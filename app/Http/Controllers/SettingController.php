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
        $supportedProviders = config('payments.qris.supported_providers', ['xendit', 'midtrans']);
        $qrisBillingPricePerDevice = Setting::getInt('qris_billing_price_per_device', 100000);
        $qrisBillingMaxFeeMode = Setting::getValue('qris_billing_max_fee_mode', 'capped');
        $qrisBillingMaxFee = Setting::getInt('qris_billing_max_fee', 500000);
        $qrisBillingPaymentBankName = Setting::getValue('qris_billing_payment_bank_name', 'Bank BCA');
        $qrisBillingPaymentAccountNumber = Setting::getValue('qris_billing_payment_account_number', '8290-xxxx-xxxx');
        $qrisBillingPaymentAccountHolder = Setting::getValue('qris_billing_payment_account_holder', 'Bos Pengering');

        return view('dashboard.setting', compact(
            'appName',
            'appIcon',
            'qrisDefaultProvider',
            'supportedProviders',
            'qrisBillingPricePerDevice',
            'qrisBillingMaxFeeMode',
            'qrisBillingMaxFee',
            'qrisBillingPaymentBankName',
            'qrisBillingPaymentAccountNumber',
            'qrisBillingPaymentAccountHolder'
        ));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string',
            'qris_default_provider' => ['required', 'string', Rule::in(config('payments.qris.supported_providers', ['xendit', 'midtrans']))],
            'qris_billing_price_per_device' => 'required|integer|min:0',
            'qris_billing_max_fee_mode' => ['required', 'string', Rule::in(['capped', 'unlimited'])],
            'qris_billing_max_fee' => 'required_if:qris_billing_max_fee_mode,capped|nullable|integer|min:0',
            'qris_billing_payment_bank_name' => 'required|string|max:100',
            'qris_billing_payment_account_number' => 'required|string|max:100',
            'qris_billing_payment_account_holder' => 'required|string|max:100',
        ]);

        $envData = [
            'APP_NAME' => $request->input('app_name'),
            'QRIS_DEFAULT_PROVIDER' => strtolower($request->input('qris_default_provider')),
        ];

        $this->setEnvironmentValue($envData);
        Setting::setValue('qris_billing_price_per_device', $request->input('qris_billing_price_per_device'));
        Setting::setValue('qris_billing_max_fee_mode', $request->input('qris_billing_max_fee_mode'));
        Setting::setValue('qris_billing_max_fee', $request->input('qris_billing_max_fee', 0));
        Setting::setValue('qris_billing_payment_bank_name', $request->input('qris_billing_payment_bank_name'));
        Setting::setValue('qris_billing_payment_account_number', $request->input('qris_billing_payment_account_number'));
        Setting::setValue('qris_billing_payment_account_holder', $request->input('qris_billing_payment_account_holder'));

        return redirect()->route('admin.setting.form')->with('success', 'Settings updated successfully!');
    }

    private function setEnvironmentValue($data = array())
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $str .= "\n";
                $key = strtoupper($key);
                $value = $this->envValue($value);
                $key = $this->envKey($key);
                $key .= "=";

                if (strpos($str, $key) !== false) {
                    $str = preg_replace("/^$key.*$/m", "$key$value", $str);
                } else {
                    $str .= "\n$key$value";
                }
            }
        }

        file_put_contents($envFile, $str);
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
