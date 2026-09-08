<?php

namespace App\Services\Payments\Qris\Gateways;

use App\Services\Payments\Qris\Contracts\QrisGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class XenditQrisGateway implements QrisGateway
{
    private const CREATE_QR_ENDPOINT = 'https://api.xendit.co/qr_codes';
    private const CURRENCY = 'IDR';

    public function provider(): string
    {
        return 'xendit';
    }

    public function createCharge(string $orderId, int $amount, string $callbackUrl, array $metadata = []): array
    {
        $apiKey = (string) config('services.xendit.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('Xendit API key is not configured.');
        }

        $response = Http::withBasicAuth($apiKey, '')
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'api-version' => '2022-07-31',
            ])
            ->post(self::CREATE_QR_ENDPOINT, [
                'reference_id' => $orderId,
                'type' => 'DYNAMIC',
                'currency' => self::CURRENCY,
                'amount' => $amount,
                'callback_url' => $callbackUrl,
                'metadata' => $metadata,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Xendit API error: ' . $response->body());
        }

        $payload = $response->json();

        if (! isset($payload['id'], $payload['qr_string'])) {
            throw new RuntimeException('Incomplete Xendit QRIS response.');
        }

        return [
            'provider' => $this->provider(),
            'gateway_reference' => (string) $payload['id'],
            'payment_url' => (string) $payload['id'],
            'qr_string' => (string) $payload['qr_string'],
            'api_url' => self::CREATE_QR_ENDPOINT,
            'amount' => (int) round((float) ($payload['amount'] ?? $amount)),
            'expires_at' => $payload['expires_at'] ?? null,
            'raw' => $payload,
        ];
    }

    public function parseWebhook(Request $request): array
    {
        $payload = $request->json()->all();
        $configuredToken = (string) config('services.xendit.callback_token');
        $incomingToken = (string) $request->header('x-callback-token', '');

        if ($configuredToken !== '' && ! hash_equals($configuredToken, $incomingToken)) {
            throw new RuntimeException('Invalid Xendit callback token.');
        }

        $orderId = data_get($payload, 'qr_code.external_id')
            ?? data_get($payload, 'qr_code.reference_id')
            ?? data_get($payload, 'reference_id')
            ?? data_get($payload, 'data.reference_id');
        $status = data_get($payload, 'status') ?? data_get($payload, 'data.status');

        if (! $orderId || ! $status) {
            throw new RuntimeException('Invalid Xendit webhook payload.');
        }

        return [
            'provider' => $this->provider(),
            'order_id' => (string) $orderId,
            'status' => $this->normalizeStatus((string) $status),
            'gross_amount' => ($amount = data_get($payload, 'amount') ?? data_get($payload, 'data.amount')) !== null
                ? (int) round((float) $amount)
                : null,
            'gateway_reference' => (string) (data_get($payload, 'qr_code.id') ?? data_get($payload, 'id') ?? data_get($payload, 'data.id') ?? ''),
            'metadata' => [],
            'raw' => $payload,
        ];
    }

    private function normalizeStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'COMPLETED', 'SUCCEEDED', 'SUCCESS' => 'success',
            'FAILED', 'EXPIRED', 'CANCELLED', 'DENY', 'CANCEL' => 'failed',
            default => 'pending',
        };
    }
}
