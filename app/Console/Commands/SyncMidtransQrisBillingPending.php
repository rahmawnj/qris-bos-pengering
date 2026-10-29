<?php

namespace App\Console\Commands;

use App\Models\QrisBillingPayment;
use App\Services\MidtransQrisBillingStatusService;
use Illuminate\Console\Command;

class SyncMidtransQrisBillingPending extends Command
{
    protected $signature = 'qris-billing:sync-midtrans-pending {--limit=100 : Maximum number of pending VA payments to check}';

    protected $description = 'Synchronize pending QRIS billing Virtual Account payments with Midtrans Sandbox';

    public function handle(MidtransQrisBillingStatusService $statusService): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $payments = QrisBillingPayment::query()
            ->where('status', 'pending')
            ->where('payment_provider', 'midtrans')
            ->where('payment_method', 'virtual_account')
            ->whereNotNull('gateway_order_id')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $this->info('Memeriksa ' . $payments->count() . ' pembayaran VA pending.');

        $checked = 0;
        $updated = 0;
        $failed = 0;

        foreach ($payments as $payment) {
            $before = $payment->status;
            $result = $statusService->sync($payment);
            $after = $payment->fresh()?->status;

            $checked++;
            if (!$result['ok']) {
                $failed++;
                $this->warn('#' . $payment->id . ': ' . $result['message']);
            } elseif ($after !== $before) {
                $updated++;
                $this->line('#' . $payment->id . ': ' . $before . ' -> ' . $after);
            }
        }

        $this->info("Selesai. Diperiksa: {$checked}, berubah: {$updated}, gagal diperiksa: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
