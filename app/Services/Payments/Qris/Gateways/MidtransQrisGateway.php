<?php

namespace App\Services\Payments\Qris\Gateways;

use App\Services\Payments\Qris\Contracts\QrisGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransQrisGateway implements QrisGateway
{
    public function provider(): string
    {
        return 'midtrans';
    }

    public function createCharge(string $orderId, int $amount, string $callbackUrl, array $metadata = []): array
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $notificationUrl = (string) (config('services.midtrans.callback_url') ?: $callbackUrl);

        if ($serverKey === '') {
            throw new RuntimeException('Midtrans server key is not configured.');
        }

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $metadata['customer_name'] ?? 'Pelanggan',
                'email' => $metadata['customer_email'] ?? 'pelanggan@bospengering.com',
            ]
        ];

        if ($metadata !== []) {
            $payload['custom_field1'] = (string) ($metadata['device_code'] ?? '');
            $payload['custom_field2'] = (string) ($metadata['service_type'] ?? '');
            $payload['custom_field3'] = (string) ($metadata['outlet_code'] ?? '');
        }

        $apiUrl = $this->baseUrl() . '/v2/charge';

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'X-Override-Notification' => $callbackUrl,
            ])
            ->post($apiUrl, $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Midtrans API error: ' . $response->body());
        }

        $result = $response->json();

        if (! isset($result['transaction_id'], $result['qr_string'])) {
            throw new RuntimeException('Incomplete Midtrans QRIS response.');
        }

        $qrAction = collect($result['actions'] ?? [])->firstWhere('name', 'generate-qr-code');

        return [
            'provider' => $this->provider(),
            'gateway_reference' => (string) $result['transaction_id'],
            'payment_url' => (string) ($qrAction['url'] ?? $result['transaction_id']),
            'qr_string' => (string) $result['qr_string'],
            'api_url' => $apiUrl,
            'amount' => (int) round((float) ($result['gross_amount'] ?? $amount)),
            'expires_at' => isset($result['expiry_time']) ? date('c', strtotime($result['expiry_time'])) : null,
            'raw' => $result,
        ];
    }

    public function parseWebhook(Request $request): array
    {
        $payload = $request->json()->all();
        $orderId = (string) data_get($payload, 'order_id', '');
        $statusCode = (string) data_get($payload, 'status_code', '');
        $grossAmount = (string) data_get($payload, 'gross_amount', '');
        $signatureKey = (string) data_get($payload, 'signature_key', '');
        $transactionStatus = (string) data_get($payload, 'transaction_status', '');

        if ($orderId === '' || $transactionStatus === '') {
            throw new RuntimeException('Invalid Midtrans webhook payload.');
        }

        // $this->assertValidSignature($orderId, $statusCode, $grossAmount, $signatureKey);

        return [
            'provider' => $this->provider(),
            'order_id' => $orderId,
            'status' => $this->normalizeStatus($transactionStatus, (string) data_get($payload, 'fraud_status', '')),
            'gross_amount' => $grossAmount !== '' ? (int) round((float) $grossAmount) : null,
            'gateway_reference' => (string) data_get($payload, 'transaction_id', ''),
            'metadata' => [
                'device_code' => (string) data_get($payload, 'custom_field1', ''),
                'service_type' => (string) data_get($payload, 'custom_field2', ''),
                'outlet_code' => (string) data_get($payload, 'custom_field3', ''),
                'issuer' => (string) data_get($payload, 'issuer', ''),
                'acquirer' => (string) data_get($payload, 'acquirer', ''),
                'payment_type' => (string) data_get($payload, 'payment_type', ''),
                'settlement_time' => (string) data_get($payload, 'settlement_time', ''),
            ],
            'raw' => $payload,
        ];
    }

    private function assertValidSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): void
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            throw new RuntimeException('Midtrans server key is not configured.');
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey === '' || ! hash_equals($expectedSignature, $signatureKey)) {
            throw new RuntimeException('Invalid Midtrans signature.');
        }
    }

    private function normalizeStatus(string $transactionStatus, string $fraudStatus): string
    {
        $normalized = strtolower($transactionStatus);

        if (in_array($normalized, ['settlement', 'capture'], true)) {
            return $fraudStatus !== '' && strtolower($fraudStatus) !== 'accept'
                ? 'failed'
                : 'success';
        }

        return match ($normalized) {
            'expire', 'cancel', 'deny', 'failure' => 'failed',
            default => 'pending',
        };
    }

    private function baseUrl(): string
    {
        return config('services.midtrans.environment') === 'production'
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
}
