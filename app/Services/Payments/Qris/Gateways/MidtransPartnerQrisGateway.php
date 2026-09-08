<?php

namespace App\Services\Payments\Qris\Gateways;

use App\Services\Payments\Qris\Contracts\QrisGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransPartnerQrisGateway implements QrisGateway
{
    public function provider(): string
    {
        return 'midtrans_partner';
    }

    public function createCharge(string $orderId, int $amount, string $callbackUrl, array $metadata = []): array
    {
        $serverKey = (string) ($metadata['partner_server_key'] ?? config('services.midtrans_partner.server_key'));
        $partnerId = (string) ($metadata['partner_id'] ?? config('services.midtrans_partner.partner_id'));
        $merchantId = (string) ($metadata['merchant_id'] ?? config('services.midtrans_partner.merchant_id'));

        if ($serverKey === '' || $partnerId === '' || $merchantId === '') {
            throw new RuntimeException('Midtrans Partner credentials are not fully configured.');
        }

        $url = $this->baseUrl() . '/api/v1/core/charge';

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            // Midtrans Partner supports custom fields for metadata tracking
            'custom_field1' => (string) ($metadata['device_code'] ?? ''),
            'custom_field2' => (string) ($metadata['service_type'] ?? ''),
            'custom_field3' => (string) ($metadata['outlet_code'] ?? ''),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            'X-PARTNER-ID' => $partnerId,
            'X-MERCHANT-ID' => $merchantId,
            'X-Override-Notification' => $callbackUrl,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Midtrans Partner API error: ' . $response->body());
        }

        $result = $response->json();

        if (!isset($result['transaction_id'], $result['qr_string'])) {
            throw new RuntimeException('Incomplete Midtrans Partner QRIS response.');
        }

        // Midtrans Partner API usually returns actions for QR code retrieval if not in direct qr_string
        $qrAction = collect($result['actions'] ?? [])->firstWhere('name', 'generate-qr-code');

        return [
            'provider' => $this->provider(),
            'gateway_reference' => (string) $result['transaction_id'],
            'payment_url' => (string) ($qrAction['url'] ?? $result['transaction_id']),
            'qr_string' => (string) $result['qr_string'],
            'api_url' => $url,
            'amount' => (int) round((float) ($result['gross_amount'] ?? $amount)),
            'expires_at' => isset($result['expiry_time']) ? date('c', strtotime($result['expiry_time'])) : null,
            'raw' => $result,
        ];
    }

    public function parseWebhook(Request $request): array
    {
        // Standard Midtrans parsing can be reused or adapted
        $payload = $request->json()->all();
        $orderId = (string) data_get($payload, 'order_id', '');
        $transactionStatus = (string) data_get($payload, 'transaction_status', '');

        if ($orderId === '' || $transactionStatus === '') {
            throw new RuntimeException('Invalid Midtrans Partner webhook payload.');
        }

        return [
            'provider' => $this->provider(),
            'order_id' => $orderId,
            'status' => $this->normalizeStatus($transactionStatus, (string) data_get($payload, 'fraud_status', '')),
            'gross_amount' => data_get($payload, 'gross_amount') !== null ? (int) round((float) $payload['gross_amount']) : null,
            'gateway_reference' => (string) data_get($payload, 'transaction_id', ''),
            'metadata' => [
                'device_code' => (string) data_get($payload, 'custom_field1', ''),
                'service_type' => (string) data_get($payload, 'custom_field2', ''),
                'outlet_code' => (string) data_get($payload, 'custom_field3', ''),
            ],
            'raw' => $payload,
        ];
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
        return config('services.midtrans_partner.environment') === 'production'
            ? 'https://partner-api.midtrans.com'
            : 'https://sandbox.partner-api.midtrans.com';
    }
}
