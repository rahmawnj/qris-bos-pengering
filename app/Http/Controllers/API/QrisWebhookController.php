<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payments\Qris\QrisGatewayManager;
use App\Services\Payments\Qris\QrisTransactionFinalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrisWebhookController extends Controller
{
    public function __construct(
        protected QrisGatewayManager $gatewayManager,
        protected QrisTransactionFinalizer $transactionFinalizer,
    ) {
    }

    public function xendit(Request $request)
    {
        return $this->handle($request, 'xendit');
    }

    public function midtrans(Request $request)
    {
        return $this->handle($request, 'midtrans');
    }

    public function auto(Request $request)
    {
    
        $provider = $this->resolveProviderFromPayload($request);

        if (! $provider) {
            Log::warning('QRIS webhook provider could not be resolved.', [
                'payload' => $request->json()->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Payment provider could not be resolved.',
            ], 400);
        }

        return $this->handle($request, $provider);
    }

    private function handle(Request $request, string $provider)
    {
        try {
            $payload = $this->gatewayManager->resolve($provider)->parseWebhook($request);

            $transaction = $this->transactionFinalizer->finalizeByOrderId(
                orderId: $payload['order_id'],
                status: $payload['status'],
                grossAmount: $payload['gross_amount'],
                gatewayReference: $payload['gateway_reference'],
                metadata: $payload['metadata'] ?? [],
            );

            Log::info('QRIS webhook processed.', [
                'provider' => $provider,
                'order_id' => $payload['order_id'],
                'status' => $payload['status'],
                'transaction_found' => $transaction !== null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $transaction
                    ? 'Transaction updated successfully'
                    : 'Transaction not found, callback ignored',
            ]);
        } catch (\RuntimeException $e) {
            $httpStatus = str_contains(strtolower($e->getMessage()), 'invalid') ? 403 : 400;

            Log::warning('QRIS webhook rejected.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $httpStatus);
        } catch (\Throwable $e) {
            Log::error('QRIS webhook failed.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'System error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function resolveProviderFromPayload(Request $request): ?string
    {
        $payload = $request->json()->all();
        $orderId = $this->extractOrderId($payload);

        if ($orderId) {
            $transaction = Transaction::with('qrisTransaction')
                ->where('order_id', $orderId)
                ->first();

            $provider = $transaction?->qrisTransaction?->payment_provider;

            if (in_array($provider, ['xendit', 'midtrans', 'midtrans_partner'], true)) {
                return $provider;
            }
        }

        return $this->inferProviderFromPayloadShape($payload);
    }

    private function extractOrderId(array $payload): ?string
    {
        $orderId = data_get($payload, 'order_id')
            ?? data_get($payload, 'qr_code.external_id')
            ?? data_get($payload, 'qr_code.reference_id')
            ?? data_get($payload, 'reference_id')
            ?? data_get($payload, 'data.reference_id');

        $orderId = trim((string) $orderId);

        return $orderId !== '' ? $orderId : null;
    }

    private function inferProviderFromPayloadShape(array $payload): ?string
    {
        if (data_get($payload, 'transaction_status') !== null || data_get($payload, 'signature_key') !== null) {
            // Midtrans and Midtrans Partner share similar webhook shape.
            // If we can't find the transaction, we default to 'midtrans'.
            return 'midtrans';
        }

        if (
            data_get($payload, 'qr_code') !== null
            || data_get($payload, 'data.reference_id') !== null
            || data_get($payload, 'reference_id') !== null
        ) {
            return 'xendit';
        }

        return null;
    }
}
