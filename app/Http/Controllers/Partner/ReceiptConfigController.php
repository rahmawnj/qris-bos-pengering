<?php

// app/Http/Controllers/Partner/ReceiptConfigController.php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptConfigController extends Controller
{
    /**
     * Menampilkan form konfigurasi struk pembayaran.
     * Hanya owner yang bisa mengakses.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit()
    {
        $user = Auth::user();

        if ($user->role !== 'owner' || !$user->owner) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $owner = $user->owner;

        // Default konfigurasi struk pembayaran
        $defaultConfig = [
            'show_brand_name' => true,
            'show_brand_logo' => true,
            'show_outlet_address' => true,
            'show_outlet_phone' => true,
            'show_nota_id' => true,
            'show_customer_info' => true, // Nama & Email Customer
            'show_payment_method' => true, // << UBAH INI: Mengganti show_payment_type menjadi show_payment_method
            'show_cashier_name' => true, // Hanya jika type manual
            'show_datetime' => true,
            'show_notes' => true,
            'show_qr_code' => false, // << NEW: Opsi untuk QR Code
            'header_style' => 'centered', // Pilihan: 'centered', 'left_aligned'
            'font_size' => '20px', // Ukuran font default
            'logo_size' => 'normal',
            'thank_you_message' => '-- Terima Kasih --',
            'instruction_message' => 'Nota ini wajib dibawa sebagai bukti transaksi.',
        ];

        // Gabungkan konfigurasi yang ada dengan default
        $config = array_merge($defaultConfig, $owner->receipt_config ?? []);

        // Pastikan font_size valid, jika tidak, kembalikan ke default
        if (!in_array($config['font_size'], ['20px', '21px', '22px', '23px', '24px'])) {
            $config['font_size'] = '22px';
        }
        // Pastikan header_style valid, jika tidak, kembalikan ke default
        if (!in_array($config['header_style'], ['centered', 'left_aligned'])) {
            $config['header_style'] = 'centered';
        }
        if (!in_array($config['logo_size'] ?? 'normal', ['small', 'normal', 'large'])) {
            $config['logo_size'] = 'normal';
        }

        // << NEW: Pastikan show_qr_code adalah boolean
        $config['show_qr_code'] = (bool)($config['show_qr_code'] ?? false);
        $config['show_brand_logo'] = (bool)($config['show_brand_logo'] ?? true);


        return view('partner.receipt_config.edit', compact('config'));
    }

    /**
     * Menyimpan konfigurasi struk pembayaran.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'owner' || !$user->owner) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $owner = $user->owner;

        // Validasi input form
        $request->validate([
            'show_brand_name' => 'boolean',
            'show_brand_logo' => 'boolean',
            'show_outlet_address' => 'boolean',
            'show_outlet_phone' => 'boolean',
            'show_nota_id' => 'boolean',
            'show_customer_info' => 'boolean',
            'show_payment_method' => 'boolean', // << UBAH INI: Dari show_payment_type ke show_payment_method
            'show_cashier_name' => 'boolean',
            'show_datetime' => 'boolean',
            'show_notes' => 'boolean',
            'show_qr_code' => 'boolean', // << NEW: Validasi untuk show_qr_code
            'header_style' => 'required|in:centered,left_aligned',
            'font_size' => 'required|in:20px,21px,22px,23px,24px',
            'logo_size' => 'required|in:small,normal,large',
            'thank_you_message' => 'nullable|string|max:255',
            'instruction_message' => 'nullable|string|max:255',
        ]);

        // Kumpulkan data konfigurasi dari request
        $configData = [
            'show_brand_name' => $request->boolean('show_brand_name'),
            'show_brand_logo' => $request->boolean('show_brand_logo'),
            'show_outlet_address' => $request->boolean('show_outlet_address'),
            'show_outlet_phone' => $request->boolean('show_outlet_phone'),
            'show_nota_id' => $request->boolean('show_nota_id'),
            'show_customer_info' => $request->boolean('show_customer_info'),
            'show_payment_method' => $request->boolean('show_payment_method'), // << UBAH INI
            'show_cashier_name' => $request->boolean('show_cashier_name'),
            'show_datetime' => $request->boolean('show_datetime'),
            'show_notes' => $request->boolean('show_notes'),
            'show_qr_code' => $request->boolean('show_qr_code'), // << NEW
            'show_addons' => true, // Selalu true
            'show_service_type' => true, // Selalu true
            'header_style' => $request->input('header_style'),
            'font_size' => $request->input('font_size'),
            'logo_size' => $request->input('logo_size'),
            'thank_you_message' => $request->input('thank_you_message'),
            'instruction_message' => $request->input('instruction_message'),
        ];

        // Simpan konfigurasi ke kolom receipt_config
        $owner->receipt_config = $configData;
        $owner->save();

        return redirect()->back()->with('success', 'Konfigurasi struk pembayaran berhasil diperbarui!');
    }
}
