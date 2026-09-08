<?php

namespace Tests\Unit;

use App\Services\Payments\Qris\Gateways\MidtransQrisGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransQrisGatewayTest extends TestCase
{
    public function test_it_normalizes_midtrans_charge_response(): void
    {
        config([
            'services.midtrans.environment' => 'sandbox',
            'services.midtrans.server_key' => 'midtrans-secret',
        ]);

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'transaction_id' => 'midtrans-trx-123',
                'gross_amount' => '23000.00',
                'qr_string' => '000201010212TESTMIDTRANS',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => 'https://api.midtrans.com/v2/qris/midtrans-trx-123/qr-code',
                    ],
                ],
            ], 201),
        ]);

        $payload = app(MidtransQrisGateway::class)->createCharge(
            orderId: 'ORDER-456',
            amount: 23000,
            callbackUrl: 'https://example.com/midtrans-callback',
        );

        $this->assertSame('midtrans', $payload['provider']);
        $this->assertSame('midtrans-trx-123', $payload['gateway_reference']);
        $this->assertSame('https://api.midtrans.com/v2/qris/midtrans-trx-123/qr-code', $payload['payment_url']);
        $this->assertSame('000201010212TESTMIDTRANS', $payload['qr_string']);
        $this->assertSame(23000, $payload['amount']);
    }

    public function test_it_parses_midtrans_webhook_payload(): void
    {
        config([
            'services.midtrans.server_key' => 'midtrans-secret',
        ]);

        $orderId = 'ORDER-456';
        $statusCode = '200';
        $grossAmount = '23000.00';

        $request = Request::create(
            '/api/payment-status-update/midtrans',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'transaction_status' => 'settlement',
                'transaction_id' => 'midtrans-trx-123',
                'status_code' => $statusCode,
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'fraud_status' => 'accept',
                'signature_key' => hash('sha512', $orderId . $statusCode . $grossAmount . 'midtrans-secret'),
            ], JSON_THROW_ON_ERROR),
        );

        $payload = app(MidtransQrisGateway::class)->parseWebhook($request);

        $this->assertSame('midtrans', $payload['provider']);
        $this->assertSame($orderId, $payload['order_id']);
        $this->assertSame('success', $payload['status']);
        $this->assertSame(23000, $payload['gross_amount']);
        $this->assertSame('midtrans-trx-123', $payload['gateway_reference']);
    }
}
