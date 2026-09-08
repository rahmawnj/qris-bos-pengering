<?php

use App\Models\User;
use App\Models\Owner;
// Hapus baris ini: use InvalidArgumentException; // Tidak diperlukan karena sudah ada di global namespace
use App\Models\Device;
use App\Models\Outlet;
use App\Models\Cashier;
use App\Models\Transaction;
use App\Helpers\DataFetcher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

// --- Helper untuk mendapatkan Root Entity ---
if (! function_exists('getUserDataRootEntity')) {
    /**
     * Mengembalikan entity utama yang bertanggung jawab atas data berdasarkan user yang login.
     * Ini bisa Owner untuk role 'owner', atau Outlet untuk role 'cashier'.
     *
     * @param \App\Models\User $user Instance user yang sedang login.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    function getUserDataRootEntity()
    {
        $user = Auth::user();
        // Eager load relasi yang mungkin dibutuhkan untuk menghindari N+1 problem
        if ($user->role === 'owner') {
            // Load relasi 'owner' jika belum dimuat
            $user->loadMissing('owner');
            return $user->owner; // Mengembalikan instance Owner dari relasi
        } elseif ($user->role === 'cashier') {
            // Load relasi 'cashier' dan chain ke 'outlet' dan 'owner' jika belum dimuat
            $user->loadMissing('cashier.outlet.owner'); // eager load nested relations
            $cashier = $user->cashier;
            return $cashier ? $cashier->outlet : null; // Mengembalikan instance Outlet dari relasi Cashier
        }
        return null; // Untuk admin atau member, tidak ada root entity spesifik untuk data terfilter
    }
}


if (! function_exists('getData')) {
    /**
     * Mengembalikan instance DataFetcher untuk user yang sedang login,
     * memungkinkan chaining properti untuk mengakses data yang difilter.
     * Contoh: `getData()->devices` atau `getData()->transactions`.
     *
     * @return \App\Helpers\DataFetcher
     */
    function getData(): DataFetcher
    {
        return DataFetcher::forCurrentUser();
    }
}


if (! function_exists('generateOrderId')) {
    /**
     * Menghasilkan order ID yang unik.
     */
    function generateOrderId($outletCode, $paymentType, $time, $serviceType = null)
    {
        // Hapus prefix OUT-
        $outletCode = preg_replace('/^(OUT-)/i', '', $outletCode);

        // Map tipe pembayaran
        $paymentMap = [
            'manual' => 'ML',
            'member' => 'MB',
            'qris'   => 'QR',
        ];

        // Ambil singkatan pembayaran
        $paymentCode = $paymentMap[strtolower($paymentType)] ?? strtoupper($paymentType);

        // Ambil huruf depan serviceType (default: N untuk none)
        $serviceInitial = strtoupper(substr($serviceType ?? 'none', 0, 1));

        // Format waktu + microseconds + random
        $timeStr = $time->format('YmdHis') . substr($time->format('u'), 0, 4) . strtoupper(substr(md5(uniqid()), 0, 4));

        // Gabungkan jadi Order ID
        return strtoupper("{$outletCode}-{$paymentCode}-{$serviceInitial}{$timeStr}");
    }

    if (! function_exists('getBrand')) {
        /**
         * Mengembalikan instance Owner (brand) berdasarkan user yang sedang login.
         *
         * - Jika login sebagai outlet, brand diambil dari data outlet yang tersimpan di session.
         * - Jika login sebagai owner, brand diambil dari properti owner pada user.
         *
         * @param mixed $user (opsional) Instance user yang sedang login.
         * @return \App\Models\Owner
         */
        if (! function_exists('getBrand')) {
            /**
             * Mengembalikan instance Owner berdasarkan user yang sedang login.
             *
             * @return \App\Models\Owner|null
             */
            function getBrand()
            {
                $user = Auth::user();

                if (!$user) {
                    return null;
                }

                if ($user->role === 'owner') {
                    return $user->owner;
                } elseif ($user->role === 'cashier') {
                    $cashier = $user->cashier;
                    if ($cashier && $cashier->outlet) {
                        return $cashier->outlet->owner;
                    }
                }
                return null;
            }
        }
    }
}