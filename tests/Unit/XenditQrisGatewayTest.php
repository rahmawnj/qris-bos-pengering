<?php

namespace Tests\Unit;

use App\Services\Payments\Qris\Gateways\XenditQrisGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XenditQrisGatewayTest extends TestCase
{
    public function test_it_normalizes_xendit_charge_response(): void
    {
        config([
            'services.xendit.api_key' => 'xendit-secret',
        ]);

        Http::fake([
            'https://api.xendit.co/qr_codes' => Http::response([
                'id' => 'qr_test_123',
                'amount' => 15000,
                'qr_string' => '000201010212TESTXENDIT',
                'status' => 'ACTIVE',
            ], 200),
        ]);

        $payload = app(XenditQrisGateway::class)->createCharge(
            orderId: 'ORDER-123',
            amount: 15000,
            callbackUrl: 'https://example.com/xendit-callback',
        );

        $this->assertSame('xendit', $payload['provider']);
        $this->assertSame('qr_test_123', $payload['gateway_reference']);
        $this->assertSame('qr_test_123', $payload['payment_url']);
        $this->assertSame('000201010212TESTXENDIT', $payload['qr_string']);
        $this->assertSame(15000, $payload['amount']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.xendit.co/qr_codes'
                && $request['reference_id'] === 'ORDER-123'
                && $request['currency'] === 'IDR'
                && $request['amount'] === 15000
                && ! isset($request['external_id']);
        });
    }

    public function test_it_parses_xendit_webhook_payload(): void
    {
        config([
            'services.xendit.callback_token' => 'callback-token',
        ]);

        $request = Request::create(
            '/api/payment-status-update/xendit',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_TOKEN' => 'callback-token',
            ],
            json_encode([
                'event' => 'qr.payment',
                'id' => 'qrpy_123',
                'amount' => 17500,
                'status' => 'COMPLETED',
                'qr_code' => [
                    'id' => 'qr_123',
                    'external_id' => 'ORDER-123',
                ],
            ], JSON_THROW_ON_ERROR),
        );

        $payload = app(XenditQrisGateway::class)->parseWebhook($request);

        $this->assertSame('xendit', $payload['provider']);
        $this->assertSame('ORDER-123', $payload['order_id']);
        $this->assertSame('success', $payload['status']);
        $this->assertSame(17500, $payload['gross_amount']);
        $this->assertSame('qr_123', $payload['gateway_reference']);
    }
}
