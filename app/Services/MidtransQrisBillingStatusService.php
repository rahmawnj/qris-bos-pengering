<?php

namespace App\Services;

use App\Models\QrisBillingPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MidtransQrisBillingStatusService
{
    /**
     * Check one VA transaction against Midtrans Sandbox and safely synchronize its local status.
     *
     * @return array{ok: bool, message: string, status?: string}
     */
    public function sync(QrisBillingPayment $payment): array
    {
        if ($payment->payment_provider !== 'midtrans'
            || $payment->payment_method !== 'virtual_account'
            || !$payment->gateway_order_id) {
            return ['ok' => false, 'message' => 'Pembayaran ini bukan transaksi Virtual Account Midtrans.'];
        }

        if ($payment->status !== 'pending') {
            return ['ok' => true, 'message' => 'Status lokal sudah final: ' . $payment->status, 'status' => $payment->status];
        }

        $serverKey = (string) config('services.midtrans.sandbox_server_key');
        if ($serverKey === '') {
            Log::error('QRIS billing status sync skipped: MIDTRANS_SANDBOX_SERVER_KEY is missing.', [
                'payment_id' => $payment->id,
                'order_id' => $payment->gateway_order_id,
            ]);
            return ['ok' => false, 'message' => 'MIDTRANS_SANDBOX_SERVER_KEY belum dikonfigurasi.'];
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(15)
                ->get('https://api.sandbox.midtrans.com/v2/' . rawurlencode($payment->gateway_order_id) . '/status');

            if (!$response->successful()) {
                Log::warning('QRIS billing Midtrans status request failed.', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->gateway_order_id,
                    'http_status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return ['ok' => false, 'message' => 'Midtrans belum dapat memeriksa status (HTTP ' . $response->status() . ').'];
            }

            $result = $response->json();
            if (!is_array($result)
                || (string) ($result['order_id'] ?? '') !== (string) $payment->gateway_order_id) {
                Log::warning('QRIS billing Midtrans status response order mismatch.', [
                    'payment_id' => $payment->id,
                    'expected_order_id' => $payment->gateway_order_id,
                    'received_order_id' => data_get($result, 'order_id'),
                ]);
                return ['ok' => false, 'message' => 'Respons status Midtrans tidak cocok dengan nomor pesanan.'];
            }

            if (!isset($result['gross_amount'])
                || (int) round((float) $result['gross_amount']) !== (int) $payment->amount) {
                Log::warning('QRIS billing Midtrans status amount mismatch.', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->gateway_order_id,
                    'expected_amount' => (int) $payment->amount,
                    'received_amount' => $result['gross_amount'] ?? null,
                ]);
                return ['ok' => false, 'message' => 'Nominal transaksi dari Midtrans tidak cocok. Status lokal tidak diubah.'];
            }

            $transactionStatus = strtolower((string) ($result['transaction_status'] ?? ''));
            $fraudStatus = strtolower((string) ($result['fraud_status'] ?? ''));
            $newStatus = null;

            if ($transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && in_array($fraudStatus, ['', 'accept'], true))) {
                $newStatus = 'paid';
            } elseif ($transactionStatus === 'expire') {
                $newStatus = 'expired';
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure', 'failed'], true)) {
                $newStatus = 'failed';
            } elseif ($transactionStatus === 'pending'
                || ($transactionStatus === 'capture' && $fraudStatus === 'challenge')) {
                $newStatus = 'pending';
            }

            if ($newStatus === null) {
                Log::warning('QRIS billing Midtrans returned an unrecognized transaction status.', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->gateway_order_id,
                    'transaction_status' => $transactionStatus,
                    'fraud_status' => $fraudStatus,
                ]);
                return ['ok' => false, 'message' => 'Status Midtrans belum dikenali; status lokal tidak diubah.'];
            }

            $finalStatus = DB::transaction(function () use ($payment, $result, $newStatus) {
                $lockedPayment = QrisBillingPayment::whereKey($payment->id)->lockForUpdate()->first();
                if (!$lockedPayment) {
                    return 'not_found';
                }

                // Never overwrite a callback/admin update that arrived while the request was in flight.
                if ($lockedPayment->status !== 'pending') {
                    return $lockedPayment->status;
                }

                $lockedPayment->gateway_transaction_id = $result['transaction_id'] ?? $lockedPayment->gateway_transaction_id;
                $lockedPayment->status = $newStatus;
                if ($newStatus === 'paid') {
                    $lockedPayment->paid_at = now();
                }
                $lockedPayment->save();

                if ($newStatus === 'paid') {
                    $outlet = $lockedPayment->outlet()->lockForUpdate()->first();
                    if ($outlet && $outlet->qris_billing_enabled && $lockedPayment->period_end) {
                        $outlet->qris_billing_due_date = $lockedPayment->period_end->toDateString();
                        $outlet->save();
                    }
                }

                return $newStatus;
            });

            Log::info('QRIS billing Midtrans status synchronized.', [
                'payment_id' => $payment->id,
                'order_id' => $payment->gateway_order_id,
                'midtrans_status' => $transactionStatus,
                'local_status' => $finalStatus,
            ]);

            $message = $finalStatus === 'paid'
                ? 'Pembayaran terkonfirmasi. Masa aktif QRIS telah diperbarui.'
                : ($finalStatus === 'pending'
                    ? 'Midtrans masih mencatat pembayaran sebagai pending.'
                    : 'Status pembayaran diperbarui menjadi ' . $finalStatus . '.');

            return ['ok' => $finalStatus !== 'not_found', 'message' => $message, 'status' => $finalStatus];
        } catch (Throwable $e) {
            Log::error('QRIS billing Midtrans status sync exception.', [
                'payment_id' => $payment->id,
                'order_id' => $payment->gateway_order_id,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => 'Gagal menghubungi Midtrans. Coba periksa lagi nanti.'];
        }
    }
}
