<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
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

    public function logout()
    {
        session()->forget('api_docs_authenticated');
        return redirect()->route('api-docs.index');
    }
}
