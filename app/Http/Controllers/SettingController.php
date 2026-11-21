<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function form()
    {
        $appName = env('APP_NAME');
        $appIcon = env('APP_ICON');
        $qrisDefaultProvider = config('payments.qris.default_provider', 'xendit');
        $xenditQrisFeeRate = (float) config('services.xendit.fee_rate', 0.007) * 100;
        $midtransQrisFeeRate = (float) config('services.midtrans.fee_rate', 0.007) * 100;
        $midtransPartnerQrisFeeRate = (float) config('services.midtrans_partner.fee_rate', 0) * 100;
        $supportedProviders = config('payments.qris.supported_providers', ['xendit', 'midtrans']);
        $qrisBillingPricePerDevice = Setting::getInt('qris_billing_price_per_device', 100000);
        $qrisBillingMaxFeeMode = Setting::getValue('qris_billing_max_fee_mode', 'capped');
        $qrisBillingMaxFee = Setting::getInt('qris_billing_max_fee', 500000);
        $qrisBillingAdvanceDays = Setting::getInt('qris_billing_advance_days', 5);
        $qrisBillingPaymentBankName = Setting::getValue('qris_billing_payment_bank_name', 'Bank BCA');
        $qrisBillingPaymentAccountNumber = Setting::getValue('qris_billing_payment_account_number', '8290-xxxx-xxxx');
        $qrisBillingPaymentAccountHolder = Setting::getValue('qris_billing_payment_account_holder', 'Bos Pengering');
        $qrisBillingPaymentMethod = Setting::getValue('qris_billing_payment_method', 'both');

        return view('dashboard.setting', compact(
            'appName',
            'appIcon',
            'qrisDefaultProvider',
            'xenditQrisFeeRate',
            'midtransQrisFeeRate',
            'midtransPartnerQrisFeeRate',
            'supportedProviders',
            'qrisBillingPricePerDevice',
            'qrisBillingMaxFeeMode',
            'qrisBillingMaxFee',
            'qrisBillingAdvanceDays',
            'qrisBillingPaymentBankName',
            'qrisBillingPaymentAccountNumber',
            'qrisBillingPaymentAccountHolder',
            'qrisBillingPaymentMethod'
        ));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string',
            'qris_default_provider' => ['required', 'string', Rule::in(config('payments.qris.supported_providers', ['xendit', 'midtrans']))],
            'xendit_qris_fee_rate' => 'required|numeric|min:0|max:100',
            'midtrans_qris_fee_rate' => 'required|numeric|min:0|max:100',
            'midtrans_partner_qris_fee_rate' => 'required|numeric|min:0|max:100',
            'qris_billing_price_per_device' => 'required|integer|min:0',
            'qris_billing_max_fee_mode' => ['required', 'string', Rule::in(['capped', 'unlimited'])],
            'qris_billing_max_fee' => 'required_if:qris_billing_max_fee_mode,capped|nullable|integer|min:0',
            'qris_billing_advance_days' => 'required|integer|min:0|max:31',
            'qris_billing_payment_bank_name' => ['required_if:qris_billing_payment_method,manual,both', 'nullable', 'string', 'max:100'],
            'qris_billing_payment_account_number' => ['required_if:qris_billing_payment_method,manual,both', 'nullable', 'string', 'max:100'],
            'qris_billing_payment_account_holder' => ['required_if:qris_billing_payment_method,manual,both', 'nullable', 'string', 'max:100'],
            'qris_billing_payment_method' => ['required', 'string', Rule::in(['manual', 'virtual_account', 'both'])],
        ]);

        $envData = [
            'APP_NAME' => $request->input('app_name'),
            'QRIS_DEFAULT_PROVIDER' => strtolower($request->input('qris_default_provider')),
            'XENDIT_QRIS_FEE_RATE' => (string) ((float) $request->input('xendit_qris_fee_rate') / 100),
            'MIDTRANS_QRIS_FEE_RATE' => (string) ((float) $request->input('midtrans_qris_fee_rate') / 100),
            'MIDTRANS_PARTNER_QRIS_FEE_RATE' => (string) ((float) $request->input('midtrans_partner_qris_fee_rate') / 100),
        ];

        $this->setEnvironmentValue($envData);
        Setting::setValue('qris_billing_price_per_device', $request->input('qris_billing_price_per_device'));
        Setting::setValue('qris_billing_max_fee_mode', $request->input('qris_billing_max_fee_mode'));
        Setting::setValue('qris_billing_max_fee', $request->input('qris_billing_max_fee', 0));
        Setting::setValue('qris_billing_advance_days', $request->input('qris_billing_advance_days'));
        Setting::setValue('qris_billing_payment_bank_name', $request->input('qris_billing_payment_bank_name'));
        Setting::setValue('qris_billing_payment_account_number', $request->input('qris_billing_payment_account_number'));
        Setting::setValue('qris_billing_payment_account_holder', $request->input('qris_billing_payment_account_holder'));
        Setting::setValue('qris_billing_payment_method', $request->input('qris_billing_payment_method'));

        return redirect()->route('admin.setting.form')->with('success', 'Settings updated successfully!');
    }

    private function setEnvironmentValue($data = array())
    {
        $envFile = app()->environmentFilePath();
        $str = file_exists($envFile) ? file_get_contents($envFile) : '';

        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $key = $this->envKey(strtoupper($key));
                $line = $key . '=' . $this->envValue($value);
                $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

                if (preg_match($pattern, $str)) {
                    $str = preg_replace($pattern, $line, $str);
                } else {
                    $str = rtrim($str) . "\n" . $line . "\n";
                }
            }
        }

        file_put_contents($envFile, $str);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    private function envValue($value)
    {
        return '"' . $value . '"';
    }

    private function envKey($key)
    {
        return strtoupper($key);
    }
}
