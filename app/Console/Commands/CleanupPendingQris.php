<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupPendingQris extends Command
{
    protected $signature = 'qris:cleanup-pending {--brand=The Laundry Spot} {--minutes=15}';

    protected $description = 'Hapus transaksi QRIS pending (hasil qr_request) berdasarkan brand dan usia data.';

    public function handle(): int
    {
        $brand = $this->option('brand');
        $minutes = (int) $this->option('minutes');
        $minutes = max(1, $minutes);
        $cutoff = Carbon::now()->subMinutes($minutes);

        $transactions = Transaction::with(['qrisTransaction', 'deviceTransactions', 'owner'])
            ->where('type', 'qris')
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->whereHas('owner', function ($q) use ($brand) {
                $q->where('brand_name', $brand);
            })
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No pending QRIS transactions to cleanup.');
            return Command::SUCCESS;
        }

        $deleted = 0;

        foreach ($transactions as $transaction) {
            DB::transaction(function () use ($transaction, &$deleted) {
                // Hapus file QR code jika ada
                $qrImage = optional($transaction->qrisTransaction)->qr_code_image;
                if ($qrImage) {
                    $filePath = storage_path('app/public/qrcodes/' . $qrImage);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }

                // Hapus relasi terkait
                $transaction->deviceTransactions()->delete();
                $transaction->qrisTransaction()->delete();

                // Hapus transaksi utama
                $transaction->delete();
                $deleted++;
            });
        }

        $msg = "Deleted {$deleted} pending QRIS transactions for brand '{$brand}' older than {$minutes} minutes.";
        $this->info($msg);
        Log::info($msg);

        return Command::SUCCESS;
    }
}
