<?php

use App\Models\Outlet;
use Illuminate\Support\Facades\Auth;

if (! function_exists('getOutlet')) {
    /**
     * Mengembalikan instance Outlet berdasarkan query parameter 'out' atau session.
     *
     * - Jika user berperan sebagai outlet (login melalui outlet), maka:
     *     • Jika terdapat query parameter 'out', cek kesesuaian kode outlet dengan yang ada di session.
     *     • Jika tidak ada query parameter, gunakan outlet dari session.
     *
     * - Jika user berperan sebagai owner, query parameter 'out' wajib ada dan outlet tersebut
     *   harus termasuk dalam daftar outlet milik owner.
     *
     * @param mixed $user (opsional) Instance user yang sedang login.
     * @return \App\Models\Outlet
     */
    function getOutlet($user = null)
    {
        $user = $user ?: auth()->user();

        // Jika login sebagai outlet
        if (Auth::guard('outlet')->check()) {
            return Auth::guard('outlet')->user();
        }

        // Jika login sebagai web (admin/owner)
        if (Auth::guard('web')->check()) {
            $owner = $user->owner ?? null;

            if (!$owner) {
                abort(403, 'Owner not found.');
            }

            // Jika query ?out= disediakan
            if (request()->has('out')) {
                $code = request()->get('out');

                $outlet = \App\Models\Outlet::where('code', $code)->first();
                if (!$outlet) {
                    abort(403, 'Outlet not found.');
                }

                if (!$owner->outlets()->where('id', $outlet->id)->exists()) {
                    abort(403, 'You are not allowed to access this outlet.');
                }

                return $outlet;
            }

            // Auto fallback ke outlet pertama milik owner
            $outlet = $owner->outlets()->first();
            if (!$outlet) {
                abort(403, 'No outlet found for this owner.');
            }

            return $outlet;
        }

        abort(403, 'Unauthorized access.');
    }
}



if (! function_exists('generateOrderId')) {
    /**
     * Menghasilkan order ID yang unik.
     *
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

        // Format waktu + microseconds
        $timeStr = $time->format('YmdHis') . substr($time->format('u'), 0, 4);

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
        function getBrand($user = null)
        {
            // Pakai user dari parameter atau user yang login (web guard)
            $user = $user ?: Auth::user();
            // Jika login sebagai outlet via guard outlet
            if ($user->role == 'cashier') {
                $outlet = $user->outlet; // Ambil outlet dari user yang login
                if (!$outlet) {
                    abort(403, 'Outlet not found.');
                }
                // Return owner dari outlet (brand)
                return $outlet->owner;
            } else 

            // Jika login sebagai owner via web guard
                return $user->owner;

            abort(403, 'Unauthorized.');
        }
    }
}