<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use App\Models\QrisBillingPayment;
use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    /**
     * Show the API documentation or lock screen.
     */
    public function index()
    {
        $serviceTypes = ServiceType::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (ServiceType $serviceType) => [
                'label' => $serviceType->name,
                'value' => $serviceType->slug,
            ])
            ->values();

        return view('api-docs', compact('serviceTypes'));
    }

    /**
     * Show integration flow documentation or lock screen.
     */
    public function flows()
    {
        return view('api-flows');
    }

    /**
     * Authenticate the user for viewing API documentation with the password tazaka123.
     */
    public function auth(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $redirectTo = $request->input('redirect_to');
        $hasSafeRedirect = is_string($redirectTo) && str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//');

        if ($request->password === 'tazaka123') {
            session(['api_docs_authenticated' => true]);

            if ($hasSafeRedirect) {
                return redirect($redirectTo);
            }

            return redirect()->route('api-docs.index');
        }

        if ($hasSafeRedirect) {
            return redirect($redirectTo)->with('error', 'Kunci akses salah! Silakan coba lagi.');
        }

        return redirect()->route('api-docs.index')->with('error', 'Kunci akses salah! Silakan coba lagi.');
    }

    /**
     * Clear the API documentation session (lock the screen).
     */
    public function testVirtualAccount(Request $request)
    {
        abort_unless(session('api_docs_authenticated'), 403);

        $data = $request->validate([
            'bank' => 'required|in:bca,bni,bri,permata,cimb',
            'amount' => 'required|integer|min:1000|max:1000000',
        ]);

        // Playground VA selalu menggunakan kredensial Sandbox terpisah,
        // tanpa mengubah environment/key Production yang dipakai integrasi utama.
        $key = (string) config('services.midtrans.sandbox_server_key');
        if ($key === '') {
            return response()->json(['ok' => false, 'message' => 'MIDTRANS_SANDBOX_SERVER_KEY belum dikonfigurasi.'], 422);
        }

        $url = 'https://api.sandbox.midtrans.com/v2/charge';
        $payload = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => 'APIDOCS-VA-' . now()->format('ymdHis') . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6)),
                'gross_amount' => (int) $data['amount'],
            ],
            'bank_transfer' => ['bank' => $data['bank']],
            'customer_details' => ['first_name' => 'API Docs Test', 'email' => 'api-docs-test@example.com'],
        ];

        $response = \Illuminate\Support\Facades\Http::withBasicAuth($key, '')
            ->acceptJson()->asJson()->timeout(20)->post($url, $payload);
        $raw = $response->json();

        return response()->json([
            'ok' => $response->successful() && (string) data_get($raw, 'status_code', '') === '201',
            'environment' => 'sandbox',
            'request' => [
                'method' => 'POST',
                'url' => $url,
                'authentication' => 'Basic Auth; Server Key tidak diekspos ke browser',
                'body' => $payload,
            ],
            'gateway' => ['http_status' => $response->status()],
            'raw' => $raw ?? $response->body(),
        ]);
    }

    /**
     * List recent VA renewal payments for the authenticated API playground.
     */
    public function virtualAccountBillingPayments()
    {
        abort_unless(session('api_docs_authenticated'), 403);

        $payments = QrisBillingPayment::with('outlet:id,outlet_name')
            ->where('payment_method', 'virtual_account')
            ->where('payment_provider', 'midtrans')
            ->whereNotNull('gateway_order_id')
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (QrisBillingPayment $payment) => [
                'id' => $payment->id,
                'outlet_id' => $payment->outlet_id,
                'outlet_name' => $payment->outlet?->outlet_name,
                'order_id' => $payment->gateway_order_id,
                'amount' => (int) $payment->amount,
                'status' => $payment->status,
                'bank' => $payment->va_bank,
                'va_number' => $payment->va_number,
                'period_end' => $payment->period_end?->toDateString(),
            ]);

        return response()->json(['ok' => true, 'payments' => $payments]);
    }

    /**
     * Prepare a signed Midtrans Sandbox notification for the API playground.
     * The browser sends the returned payload to the real callback route.
     */
    public function testVirtualAccountCallback(Request $request)
    {
        abort_unless(session('api_docs_authenticated'), 403);

        $data = $request->validate([
            'order_id' => 'required|string|max:100',
            'transaction_status' => 'required|in:pending,capture,settlement,expire,cancel,deny,failure,failed',
            'gross_amount' => 'required|numeric|min:1000|max:1000000',
            'transaction_id' => 'nullable|string|max:100',
            'bank' => 'nullable|in:bca,bni,bri,permata,cimb',
            'va_number' => 'nullable|string|max:100',
            'fraud_status' => 'nullable|in:accept,challenge',
        ]);

        $serverKey = (string) config('services.midtrans.sandbox_server_key');
        if ($serverKey === '') {
            return response()->json([
                'ok' => false,
                'message' => 'MIDTRANS_SANDBOX_SERVER_KEY belum dikonfigurasi.',
            ], 422);
        }

        $orderId = trim($data['order_id']);
        $grossAmount = number_format((float) $data['gross_amount'], 2, '.', '');
        $transactionStatus = $data['transaction_status'];
        $statusCodes = [
            'pending' => '201',
            'capture' => '200',
            'settlement' => '200',
            'expire' => '202',
            'cancel' => '200',
            'deny' => '202',
            'failure' => '202',
            'failed' => '202',
        ];
        $statusCode = $statusCodes[$transactionStatus];

        $payload = [
            'transaction_time' => now()->format('Y-m-d H:i:s'),
            'transaction_status' => $transactionStatus,
            'transaction_id' => !empty($data['transaction_id'])
                ? $data['transaction_id']
                : ('PLAYGROUND-' . now()->format('YmdHis')),
            'status_message' => 'Midtrans Sandbox notification simulated from API playground',
            'status_code' => $statusCode,
            'signature_key' => hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey),
            'payment_type' => 'bank_transfer',
            'payment_amounts' => [],
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
            'fraud_status' => $data['fraud_status'] ?? 'accept',
            'currency' => 'IDR',
        ];

        if (!empty($data['bank']) && !empty($data['va_number'])) {
            $payload['va_numbers'] = [[
                'bank' => $data['bank'],
                'va_number' => $data['va_number'],
            ]];
        }

        $callbackPath = route('qris-billing.midtrans-callback', [], false);
        $callbackUrl = $request->getSchemeAndHttpHost() . $callbackPath;

        return response()->json([
            'ok' => true,
            'environment' => 'sandbox',
            'callback_url' => $callbackUrl,
            'delivery_mode' => 'Payload ditandatangani di backend; browser akan mengirim HTTP POST ke endpoint callback sebenarnya.',
            'request' => [
                'method' => 'POST',
                'url' => $callbackUrl,
                'authentication' => 'Signature Sandbox dibuat di backend; Server Key tidak dikirim ke browser.',
                'body' => $payload,
            ],
            'payload' => $payload,
        ]);
    }

    public function logout()
    {
        session()->forget('api_docs_authenticated');
        return redirect()->route('api-docs.index');
    }
}
