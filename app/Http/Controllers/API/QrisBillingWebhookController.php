<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QrisBillingPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QrisBillingWebhookController extends Controller
{
    public function midtrans(Request $request)
    {
        $payload = $request->all();
        // Callback VA billing juga divalidasi dengan key Sandbox yang sama.
        $serverKey = (string) config('services.midtrans.sandbox_server_key');
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($serverKey === '' || $orderId === '' || $signature === '') {
            return response()->json(['status' => 'error', 'message' => 'Invalid callback payload.'], 400);
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('QRIS billing Midtrans Sandbox callback signature mismatch.', ['environment' => 'sandbox', 'order_id' => $orderId]);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature.'], 403);
        }

        $payment = QrisBillingPayment::where('gateway_order_id', $orderId)
            ->where('payment_provider', 'midtrans')
            ->first();

        if (!$payment) {
            return response()->json(['status' => 'ok', 'message' => 'Unknown order ignored.']);
        }

        if ((int) round((float) $grossAmount) !== (int) $payment->amount) {
            Log::warning('QRIS billing Midtrans callback amount mismatch.', [
                'order_id' => $orderId,
                'expected' => (int) $payment->amount,
                'received' => $grossAmount,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Amount mismatch.'], 400);
        }

        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? ''));

        Log::info('QRIS billing Midtrans Sandbox callback received.', [
            'environment' => 'sandbox',
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'transaction_status' => $transactionStatus,
            'transaction_id' => $payload['transaction_id'] ?? null,
        ]);

        $isPaid = $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && ($fraudStatus === '' || $fraudStatus === 'accept'));

        DB::transaction(function () use ($orderId, $transactionStatus, $isPaid, $payload) {
            $lockedPayment = QrisBillingPayment::where('gateway_order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (!$lockedPayment || $lockedPayment->status === 'paid') {
                return;
            }

            $lockedPayment->gateway_transaction_id = $payload['transaction_id'] ?? $lockedPayment->gateway_transaction_id;

            if ($isPaid) {
                $lockedPayment->status = 'paid';
                $lockedPayment->paid_at = now();
                $lockedPayment->save();

                $outlet = $lockedPayment->outlet()->lockForUpdate()->first();
                if ($outlet && $outlet->qris_billing_enabled && $lockedPayment->period_end) {
                    $outlet->qris_billing_due_date = $lockedPayment->period_end->toDateString();
                    $outlet->save();
                }
                return;
            }

            if (in_array($transactionStatus, ['expire', 'cancel', 'deny', 'failure', 'failed'], true)) {
                $lockedPayment->status = $transactionStatus === 'expire' ? 'expired' : 'failed';
            }

            $lockedPayment->save();
        });

        return response()->json(['status' => 'ok', 'message' => 'QRIS billing callback processed.']);
    }
}
