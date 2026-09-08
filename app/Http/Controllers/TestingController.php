<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class TestingController extends Controller
{
    public function generateQRIS(Request $request)
    {
        $baseUrl = 'https://api.midtrans.com/v1.0/qr/qr-mpm-generate'; // sesuaikan jika sandbox
        $clientId = env('MIDTRANS_TEST_CLIENT_ID', 'BMRI');
        $channelId = env('MIDTRANS_TEST_CHANNEL_ID', '12345');
        $externalId = env('MIDTRANS_TEST_EXTERNAL_ID', '12345678901234567890'); // harus unik
        $accessToken = env('MIDTRANS_TEST_BEARER_TOKEN');
        $timestamp = now()->timezone('Asia/Jakarta')->format('c'); // Contoh: 2024-03-19T14:30:00+07:00

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'error' => 'MIDTRANS_TEST_BEARER_TOKEN belum dikonfigurasi.',
            ], 500);
        }

        $body = [
            "qr_type" => "DYNAMIC",
            "partner_id" => $clientId,
            "external_id" => $externalId,
            "amount" => 10000,
            "currency" => "IDR",
            "merchant_name" => "Rahma Laundry",
            "merchant_city" => "Jakarta"
        ];

        $bodyJson = json_encode($body);

        // Signature = HMAC-SHA512 dari body JSON pakai secret key (tergantung partner)
        $secretKey = env('MIDTRANS_TEST_SECRET_KEY'); // biasanya beda dari server key!

        if (!$secretKey) {
            return response()->json([
                'success' => false,
                'error' => 'MIDTRANS_TEST_SECRET_KEY belum dikonfigurasi.',
            ], 500);
        }
        $signature = hash_hmac('sha512', $bodyJson, $secretKey);

        $response = Http::withHeaders([
            'Content-Type'     => 'application/json',
            'Authorization'    => 'Bearer ' . $accessToken,
            'X-TIMESTAMP'      => $timestamp,
            'X-SIGNATURE'      => $signature,
            'X-PARTNER-ID'     => $clientId,
            'X-EXTERNAL-ID'    => $externalId,
            'CHANNEL-ID'       => $channelId,
        ])->post($baseUrl, $body);

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'data' => $response->json(),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);
        }
    }
}
