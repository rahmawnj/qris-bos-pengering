<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Outlet;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransPartnerQrisController extends Controller
{
    public function charge(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:10'],
            'order_id' => ['nullable', 'string', 'max:100'],
            'owner_id' => ['nullable', 'exists:owners,id'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'device_code' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'partner_server_key' => ['nullable', 'string'],
            'partner_id' => ['nullable', 'string', 'max:100'],
            'merchant_id' => ['nullable', 'string', 'max:100'],
            'environment' => ['nullable', 'in:sandbox,staging,production'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $outlet = $this->resolveOutlet($request);
        $owner = $this->resolveOwner($request, $outlet);

        $partnerServerKey = trim((string) ($validated['partner_server_key']
            ?? config('services.midtrans_partner.server_key')));

        $partnerId = trim((string) ($validated['partner_id']
            ?? config('services.midtrans_partner.partner_id')));

        $merchantId = trim((string) ($validated['merchant_id']
            ?? ($owner?->payment_account_type === 'owner' ? $owner?->merchant_id : null)
            ?? config('services.midtrans_partner.merchant_id')));

        if ($partnerServerKey === '' || $partnerId === '' || $merchantId === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Credential Midtrans Partner belum lengkap. Isi partner_server_key, partner_id, dan merchant_id.',
            ], 422);
        }

        $orderId = (string) ($validated['order_id'] ?? $this->makeOrderId($outlet, $validated['type'] ?? 'qris'));
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $validated['amount'],
            ],
            'item_details' => [
                [
                    'id' => (string) ($validated['type'] ?? 'qris'),
                    'price' => (int) $validated['amount'],
                    'quantity' => 1,
                    'name' => $validated['item_name'] ?? 'QRIS Payment',
                    'merchant_name' => $outlet?->outlet_name ?? $owner?->brand_name ?? 'Bos Pengering',
                ],
            ],
            'customer_details' => [
                'first_name' => $validated['customer_name'] ?? $owner?->brand_name ?? $outlet?->outlet_name ?? 'Pelanggan',
                'email' => $validated['customer_email'] ?? $owner?->user?->email ?? 'pelanggan@bospengering.com',
            ],
            'enabled_payments' => ['other_qris'],
            'custom_field1' => (string) ($validated['device_code'] ?? ''),
            'custom_field2' => $merchantId,
            'custom_field3' => $partnerId,
        ];

        $environment = $validated['environment']
            ?? config('services.midtrans_partner.environment', 'staging');

        try {
            $authorization = $this->authorizationHeader($partnerId, $partnerServerKey);

            $response = Http::acceptJson()
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'Authorization' => $authorization,
                    'X-PARTNER-ID' => $partnerId,
                    'X-MERCHANT-ID' => $merchantId,
                ])
                ->post($this->checkoutUrl($environment), $payload);

            $rawBody = $response->body();
            $body = $response->json();
            $responseBody = is_array($body) ? $body : ['raw' => $rawBody];

            return response()->json([
                'status' => $response->successful() ? 'success' : 'error',
                'message' => [
                    'order_id' => $orderId,
                    'midtrans_status' => $response->status(),
                    'environment' => $environment,
                    'request' => [
                        'url' => $this->checkoutUrl($environment),
                        'method' => 'POST',
                        'headers' => [
                            'Authorization' => $this->maskedAuthorizationHeader($partnerId, $partnerServerKey),
                            'X-PARTNER-ID' => $partnerId,
                            'X-MERCHANT-ID' => $merchantId,
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                        ],
                        'payload' => $payload,
                    ],
                    'response_headers' => $response->headers(),
                    'response_body' => $rawBody,
                    'response' => $responseBody,
                ],
            ], $response->successful() ? 200 : $response->status());
        } catch (\Throwable $e) {
            Log::error('Midtrans Partner QRIS charge failed.', [
                'order_id' => $orderId,
                'owner_id' => $owner?->id,
                'outlet_id' => $outlet?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghubungi Midtrans Partner API: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testPartnerCheck(Request $request)
    {
        $orderId = $request->query('order_id');

        if (!$orderId) {
            return response()->json(['error' => 'Parameter order_id wajib diisi.'], 422);
        }

        // Cari transaction untuk mendapatkan merchant_id jika tidak ada di query
        $transaction = \App\Models\Transaction::where('order_id', $orderId)->first();
        
        // Ambil kredensial dari config (fallback ke .env values jika config kosong)
        $partnerServerKey = config('services.midtrans_partner.server_key') ?: 'PARTNER-server-uOVkbcXGNzdCkdDGWwgYZdfp';
        $partnerId = config('services.midtrans_partner.partner_id') ?: '792';
        $environment = config('services.midtrans_partner.environment') ?: 'production';

        $defaultMerchantId = config('services.midtrans_partner.merchant_id') ?: 'G727181345';
        if ($transaction && $transaction->owner) {
            $defaultMerchantId = $transaction->owner->merchant_id ?: $defaultMerchantId;
        }

        $merchantId = $request->query('merchant_id', $defaultMerchantId);

        // Midtrans Status URL
        $baseUrl = ($environment === 'production') 
            ? 'https://api.midtrans.com/v2' 
            : 'https://api.sandbox.midtrans.com/v2';
        
        $url = "{$baseUrl}/{$orderId}/status";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($partnerId . ':' . $partnerServerKey),
                'X-PARTNER-ID' => $partnerId,
                'X-MERCHANT-ID' => $merchantId,
                'Accept' => 'application/json',
            ])->get($url);

            $responseData = $response->json();
            if (empty($responseData)) {
                $responseData = $response->body();
            }

            return response()->json([
                'test_info' => [
                    'url' => $url,
                    'order_id' => $orderId,
                    'environment' => $environment,
                    'credentials_used' => [
                        'partner_id' => $partnerId,
                        'merchant_id' => $merchantId,
                        'server_key' => $this->maskCredential($partnerServerKey)
                    ],
                    'transaction_found' => !!$transaction,
                ],
                'midtrans_response' => $responseData,
                'status_code' => $response->status()
                ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan pada sistem / server.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function debug(Request $request)
    {
        $validated = $request->validate([
            'partner_server_key' => ['required', 'string'],
            'partner_id' => ['required', 'string', 'max:100'],
            'merchant_id' => ['required', 'string', 'max:100'],
            'environment' => ['nullable', 'in:sandbox,staging,production'],
        ]);

        $environment = $validated['environment'] ?? config('services.midtrans_partner.environment', 'sandbox');

        return response()->json([
            'status' => 'success',
            'message' => [
                'url' => $this->checkoutUrl($environment),
                'method' => 'POST',
                'headers' => [
                    'Authorization' => $this->maskedAuthorizationHeader($validated['partner_id'], $validated['partner_server_key']),
                    'X-PARTNER-ID' => $validated['partner_id'],
                    'X-MERCHANT-ID' => $validated['merchant_id'],
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'auth_format' => 'Basic base64(partner_id:partner_server_key)',
            ],
        ]);
    }

    private function resolveOutlet(Request $request): ?Outlet
    {
        if ($request->filled('outlet_id')) {
            return Outlet::with('owner.user')->find($request->input('outlet_id'));
        }

        if ($request->filled('device_code')) {
            return Device::with('outlet.owner.user')
                ->where('code', $request->input('device_code'))
                ->first()
                ?->outlet;
        }

        return null;
    }

    private function resolveOwner(Request $request, ?Outlet $outlet): ?Owner
    {
        if ($outlet?->owner) {
            return $outlet->owner;
        }

        if ($request->filled('owner_id')) {
            return Owner::with('user')->find($request->input('owner_id'));
        }

        return null;
    }

    private function makeOrderId(?Outlet $outlet, string $type): string
    {
        $outletCode = $outlet?->code ?: 'PARTNER';

        return strtoupper(Str::slug($type ?: 'qris') . '-' . $outletCode . '-' . now()->getTimestampMs() . Str::random(4));
    }

    private function baseUrl(string $environment): string
    {
        return match ($environment) {
            'production' => 'https://partner-api.midtrans.com',
            'staging' => 'https://partner-api.stg.midtrans.com',
            default => 'https://sandbox.partner-api.midtrans.com',
        };
    }

    private function checkoutUrl(string $environment): string
    {
        return $this->baseUrl($environment) . '/api/v1/checkout/transactions';
    }

    private function maskCredential(string $credential): string
    {
        if (strlen($credential) <= 10) {
            return '***';
        }

        return substr($credential, 0, 10) . '...' . substr($credential, -4);
    }

    private function authorizationHeader(string $partnerId, string $partnerServerKey): string
    {
        return 'Basic ' . base64_encode($partnerId . ':' . $partnerServerKey);
    }

    private function maskedAuthorizationHeader(string $partnerId, string $partnerServerKey): string
    {
        return 'Basic ' . base64_encode($partnerId . ':' . $this->maskCredential($partnerServerKey));
    }
}
