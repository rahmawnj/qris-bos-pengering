<?php

namespace App\Services\Payments\Qris\Contracts;

use Illuminate\Http\Request;

interface QrisGateway
{
    public function provider(): string;

    /**
     * @return array{
     *     provider: string,
     *     gateway_reference: string,
     *     payment_url: string,
     *     qr_string: string,
     *     amount: int,
     *     expires_at: ?string,
     *     raw: array<mixed>
     * }
     */
    public function createCharge(string $orderId, int $amount, string $callbackUrl, array $metadata = []): array;

    /**
     * @return array{
     *     provider: string,
     *     order_id: string,
     *     status: string,
     *     gross_amount: ?int,
     *     gateway_reference: string,
     *     metadata: array<string, string>,
     *     raw: array<mixed>
     * }
     */
    public function parseWebhook(Request $request): array;
}
