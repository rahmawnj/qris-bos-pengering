<?php

namespace App\Services\Payments\Qris;

use App\Services\Payments\Qris\Contracts\QrisGateway;
use App\Services\Payments\Qris\Gateways\MidtransQrisGateway;
use App\Services\Payments\Qris\Gateways\MidtransPartnerQrisGateway;
use App\Services\Payments\Qris\Gateways\XenditQrisGateway;
use InvalidArgumentException;

class QrisGatewayManager
{
    public function __construct(
        protected XenditQrisGateway $xenditGateway,
        protected MidtransQrisGateway $midtransGateway,
        protected MidtransPartnerQrisGateway $midtransPartnerGateway,
    ) {
    }

    public function resolve(?string $provider = null): QrisGateway
    {
        return match ($this->normalizeProvider($provider)) {
            'xendit' => $this->xenditGateway,
            'midtrans' => $this->midtransGateway,
            'midtrans_partner' => $this->midtransPartnerGateway,
            default => throw new InvalidArgumentException('Unsupported QRIS provider.'),
        };
    }

    public function normalizeProvider(?string $provider = null): string
    {
        $resolvedProvider = strtolower(trim((string) ($provider ?: config('payments.qris.default_provider', 'xendit'))));

        if (! in_array($resolvedProvider, config('payments.qris.supported_providers', ['xendit', 'midtrans', 'midtrans_partner']), true)) {
            throw new InvalidArgumentException('Unsupported QRIS provider.');
        }

        return $resolvedProvider;
    }
}
