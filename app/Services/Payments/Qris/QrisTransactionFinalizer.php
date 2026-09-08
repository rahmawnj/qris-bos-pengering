<?php

namespace App\Services\Payments\Qris;

use App\Models\DeviceTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class QrisTransactionFinalizer
{
    public function finalizeByOrderId(
        string $orderId,
        string $status,
        ?int $grossAmount = null,
        ?string $gatewayReference = null,
        array $metadata = []
    ): ?Transaction
    {
        return DB::transaction(function () use ($orderId, $status, $grossAmount, $gatewayReference, $metadata) {
            $transaction = Transaction::with('qrisTransaction')
                ->where('order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                return null;
            }

            $this->syncGatewayReference($transaction, $gatewayReference);
            $this->syncQrisMetadata($transaction, $metadata);

            if ($transaction->status !== 'pending') {
                return $transaction;
            }

            if ($status === 'success') {
                $this->markAsSuccess($transaction, $grossAmount);

                return $transaction->fresh(['qrisTransaction', 'deviceTransactions']);
            }

            if ($status === 'failed') {
                $transaction->update([
                    'status' => 'failed',
                ]);

                return $transaction->fresh(['qrisTransaction', 'deviceTransactions']);
            }

            if ($status !== 'pending') {
                $transaction->update([
                    'status' => $status,
                ]);
            }

            return $transaction->fresh(['qrisTransaction', 'deviceTransactions']);
        });
    }

    private function markAsSuccess(Transaction $transaction, ?int $grossAmount = null): void
    {
        $provider = $transaction->qrisTransaction?->payment_provider;
        $isPartner = $provider === 'midtrans_partner';

        $grossAmount ??= (int) ($transaction->total_amount ?? $transaction->amount);
        $feeRate = $isPartner ? 0 : $this->resolveFeeRate($provider);
        $feeAmount = (int) round($grossAmount * $feeRate);
        $netAmount = max($grossAmount - $feeAmount, 0);

        $transaction->update([
            'status' => 'success',
            'amount' => $netAmount,
            'fee_amount' => $feeAmount,
            'total_amount' => $grossAmount,
        ]);

        // Only increment balance for non-partner (general) transactions
        if (! $isPartner) {
            DB::table('owners')
                ->where('id', $transaction->owner_id)
                ->increment('balance', $netAmount);
        }

        $this->createDeviceTransactionIfNeeded($transaction);
    }

    private function createDeviceTransactionIfNeeded(Transaction $transaction): void
    {
        $qrisDetail = $transaction->qrisTransaction;

        if (! $qrisDetail || ! $qrisDetail->device_code) {
            return;
        }

        $alreadyExists = DeviceTransaction::where('transaction_id', $transaction->id)
            ->where('device_code', $qrisDetail->device_code)
            ->where('service_type', $qrisDetail->service_type)
            ->exists();

        if ($alreadyExists) {
            return;
        }

        DeviceTransaction::create([
            'transaction_id' => $transaction->id,
            'device_code' => $qrisDetail->device_code,
            'service_type' => $qrisDetail->service_type,
            'activated_at' => null,
        ]);
    }

    private function syncGatewayReference(Transaction $transaction, ?string $gatewayReference): void
    {
        if (! $gatewayReference || ! $transaction->qrisTransaction) {
            return;
        }

        if ($transaction->qrisTransaction->gateway_reference === $gatewayReference) {
            return;
        }

        $transaction->qrisTransaction->update([
            'gateway_reference' => $gatewayReference,
        ]);
    }

    private function syncQrisMetadata(Transaction $transaction, array $metadata): void
    {
        if (! $transaction->qrisTransaction) {
            return;
        }

        $updates = [];

        foreach (['device_code', 'service_type'] as $field) {
            $value = trim((string) ($metadata[$field] ?? ''));

            if ($value !== '' && empty($transaction->qrisTransaction->{$field})) {
                $updates[$field] = $value;
            }
        }

        if ($updates === []) {
            return;
        }

        $transaction->qrisTransaction->update($updates);
        $transaction->setRelation('qrisTransaction', $transaction->qrisTransaction->fresh());
    }

    private function resolveFeeRate(?string $provider): float
    {
        return match ($provider) {
            'midtrans' => (float) config('services.midtrans.fee_rate', 0.007),
            default => (float) config('services.xendit.fee_rate', 0.007),
        };
    }
}
