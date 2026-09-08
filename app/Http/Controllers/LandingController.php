<?php

namespace App\Http\Controllers;

use App\Constants\OrderStatus;
use App\Models\Transaction; // Make sure this model exists and is correctly namespaced
use Carbon\Carbon; // Make sure Carbon is imported

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
        // 1. Fetch the transaction with necessary relationships
        $transaction = Transaction::where('order_id', $order_id)
            ->with([
                'owner',
                'outlet',
                'manualTransaction.service',
                'memberTransaction.member.user',
            ])
            ->first(); // Use first() instead of firstOrFail() to handle not found gracefully

        if (!$transaction) {
            // Handle case where transaction is not found
            return view('landing.transaction_progress', ['error' => 'Transaksi tidak ditemukan.']);
        }

        // 2. Set custom_status_text, ensuring $transaction->progress is a string
        // If $transaction->progress is null, default it to 'received'
        $transaction->custom_status_text = OrderStatus::label($transaction->progress ?? 'received');

        $customerName = 'Pelanggan Umum'; // Default
        $customerPhone = 'N/A'; // Default

        if ($transaction->type == 'manual' && $transaction->manualTransaction) {
            $customerName = $transaction->manualTransaction->customer_name ?? $customerName;
            $customerPhone = $transaction->manualTransaction->customer_phone_number ?? $customerPhone;
        } elseif ($transaction->type == 'member' && $transaction->memberTransaction && $transaction->memberTransaction->member && $transaction->memberTransaction->member->user) {
            $customerName = $transaction->memberTransaction->member->user->name ?? $customerName;
            $customerPhone = $transaction->memberTransaction->member->user->phone_number ?? $customerPhone;
        }
        $transaction->customer_display_name = $customerName;
        $transaction->customer_display_phone = $customerPhone;

        // --- 3. Mengambil Estimasi Tanggal Selesai ---
        $estimatedCompletionAt = null;
        if ($transaction->type == 'manual' && $transaction->manualTransaction && $transaction->manualTransaction->estimated_completion_at) {
            $estimatedCompletionAt = Carbon::parse($transaction->manualTransaction->estimated_completion_at);
        }
        // Tambahkan ke objek transaksi
        $transaction->estimated_completion_display_at = $estimatedCompletionAt;


        // --- 4. Menghitung Total Harga Layanan + Addons (untuk tampilan detail) ---
        $totalAddonsPrice = 0;
        $addonsData = [];
        if ($transaction->type == 'manual' && $transaction->manualTransaction && $transaction->manualTransaction->addons) {
            $addons = $transaction->manualTransaction->addons;
            if (is_string($addons)) {
                $addons = json_decode($addons, true);
            }
            if (is_array($addons)) {
                foreach ($addons as $addon) {
                    $totalAddonsPrice += (float) ($addon['price'] ?? 0);
                    $addonsData[] = $addon;
                }
            }
        }
        // Pastikan service_price diambil dari manualTransaction jika tipe manual
        $servicePrice = ($transaction->type == 'manual' && $transaction->manualTransaction)
            ? ($transaction->manualTransaction->service_price ?? 0)
            : 0;

        $transaction->total_amount_before_paid = $servicePrice + $totalAddonsPrice;
        $transaction->addons_data = $addonsData; // Sertakan addons yang sudah di-parse ke objek transaksi

        // --- 5. Mengirim Data ke View ---
        return view('landing.transaction_progress', [
            'transaction' => $transaction,
            'progressSteps' => OrderStatus::STATUSES,
        ]);
    }
}