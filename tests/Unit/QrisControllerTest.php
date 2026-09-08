<?php

namespace Tests\Unit;

use App\Http\Controllers\API\QrisController;
use Illuminate\Http\Request;
use Tests\TestCase;

class QrisControllerTest extends TestCase
{
    public function test_qr_request_accepts_amount_ten_and_moves_past_initial_validation(): void
    {
        $request = Request::create('/api/qr-request', 'POST', [
            'amount' => 10,
            'type' => 'washer',
            'device_code' => 'DEV-7287D0',
            'provider' => 'unsupported-provider',
        ]);

        $response = app(QrisController::class)->qr_request($request);
        $payload = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('error', $payload['status']);
        $this->assertSame('Provider QRIS tidak didukung.', $payload['message']);
    }

    public function test_qr_request_rejects_amount_below_ten(): void
    {
        $request = Request::create('/api/qr-request', 'POST', [
            'amount' => 9,
            'type' => 'washer',
            'device_code' => 'DEV-7287D0',
        ]);

        $response = app(QrisController::class)->qr_request($request);
        $payload = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('error', $payload['status']);
        $this->assertSame('Parameter tidak valid', $payload['message']);
    }
}
