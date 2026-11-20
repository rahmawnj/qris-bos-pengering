<?php

namespace App\Http\Controllers;

use App\Constants\OrderStatus;
use App\Models\Transaction;
use Carbon\Carbon;

class LandingController extends Controller
{
    /**
     * Display the transaction progress.
     *
     * @param string $order_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function transaction($order_id)
    {
        $transaction = Transaction::where('order_id', $order_id)
            ->with([
                'owner',
                'outlet',
                'manualTransaction.service',
                'memberTransaction.member.user',
            ])
            ->first();

        if (!$transaction) {
            return view('landing.transaction_progress', ['error' => 'Transaksi tidak ditemukan.']);
        }

        $transaction->custom_status_text = OrderStatus::label($transaction->progress ?? 'received');

        $customerName = 'Pelanggan Umum';
        $customerPhone = 'N/A';

        if ($transaction->type == 'manual' && $transaction->manualTransaction) {
            $customerName = $transaction->manualTransaction->customer_name ?? $customerName;
            $customerPhone = $transaction->manualTransaction->customer_phone_number ?? $customerPhone;
        } elseif (
            $transaction->type == 'member'
            && $transaction->memberTransaction
            && $transaction->memberTransaction->member
            && $transaction->memberTransaction->member->user
        ) {
            $customerName = $transaction->memberTransaction->member->user->name ?? $customerName;
            $customerPhone = $transaction->memberTransaction->member->user->phone_number ?? $customerPhone;
        }

        $transaction->customer_display_name = $customerName;
        $transaction->customer_display_phone = $customerPhone;

        $estimatedCompletionAt = null;

        if (
            $transaction->type == 'manual'
            && $transaction->manualTransaction
            && $transaction->manualTransaction->estimated_completion_at
        ) {
            $estimatedCompletionAt = Carbon::parse($transaction->manualTransaction->estimated_completion_at);
        }

        $transaction->estimated_completion_display_at = $estimatedCompletionAt;

        // Hitung total layanan berdasarkan kuantitas, lalu tambahkan total add-on.
        $totalServicePrice = 0;
        $totalAddonsPrice = 0;
        $addonsData = [];

        if ($transaction->type == 'manual' && $transaction->manualTransaction) {
            $manualTransaction = $transaction->manualTransaction;

            $quantity = (float) ($manualTransaction->quantity ?? 1);
            if ($quantity <= 0) {
                $quantity = 1;
            }

            $servicePrice = (float) ($manualTransaction->service_price ?? 0);
            $totalServicePrice = $servicePrice * $quantity;

            $addons = $manualTransaction->addons;

            if (is_string($addons)) {
                $addons = json_decode($addons, true);
            }

            if (is_array($addons)) {
                foreach ($addons as $addon) {
                    $addonQuantity = max(1, (int) ($addon['qty'] ?? 1));
                    $addonPrice = (float) ($addon['price'] ?? 0);

                    $totalAddonsPrice += $addonPrice * $addonQuantity;
                    $addonsData[] = $addon;
                }
            }
        }

        $transaction->total_amount_before_paid = $totalServicePrice + $totalAddonsPrice;
        $transaction->addons_data = $addonsData;

        return view('landing.transaction_progress', [
            'transaction' => $transaction,
            'progressSteps' => OrderStatus::STATUSES,
        ]);
    }
}
