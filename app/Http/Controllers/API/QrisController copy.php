<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\Outlet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\QrisTransactionDetail;

class QrisController extends Controller
{
    public function qr_request(Request $request)
    {
        $amount     = $request->input('amount');
        $type       = $request->input('type');
        $deviceCode = $request->input('device_code');

        if (!$amount || !$type || !$deviceCode) {
            return response()->json([
                "status"  => "error",
                "message" => "Parameter tidak valid"
            ], 400);
        }

        $device = Device::where('code',  $deviceCode)->first();
        $outlet = $device->outlet;

        if (!$device) {
            return response()->json([
                "status"  => "error",
                "message" => "Outlet tidak ditemukan"
            ], 404);
        }
        $outletId   = $outlet->id;
        $outletName = $outlet->name;

        $orderId = $type . '-' . $outlet->code . '-' . time();

        $apiKey = (string) config('services.xendit.api_key');
        $callbackUrl = route('xendit.payment-callback');
        $data = [
            "reference_id" => $orderId,
            "type"         => "DYNAMIC",
            "currency"     => "IDR",
            "amount"       => $amount,
            "callback_url" => $callbackUrl,
        ];

        $response = Http::withBasicAuth($apiKey, '')
            ->withHeaders([
                'api-version' => '2022-07-31'
            ])
            ->post('https://api.xendit.co/qr_codes', $data);

        if (!$response->successful()) {
            return response()->json([
                "status"  => "error",
                "message" => "Xendit API error: " . $response->body()
            ], 500);
        }

        $qrisResult = $response->json();
        if (!isset($qrisResult['qr_string'])) {
            return response()->json([
                "status"  => "error",
                "message" => "QR string tidak ditemukan dalam respons"
            ], 500);
        }
        $qrString        = $qrisResult['qr_string'];
        $amountFormatted = 'Rp ' . number_format($qrisResult['amount'], 0, ',', '.');

        $filePath   = $this->generateQRCode($qrString, $orderId, $outletName, $amountFormatted);
        $qrImageUrl =  basename($filePath);

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'order_id'    => $orderId,
                'outlet_id'   => $outletId,
                'amount'      => $qrisResult['amount'],
                'type'        => 'qris',
                'timezone'    => $outlet->timezone,
                'status'      => 'pending',
                'service_type' => $type,
                'device_code' => $deviceCode,
                'owner_id'   => $outlet->owner_id,
                'date' => now(),
                'time' => now(),
            ]);

            QrisTransactionDetail::create([
                'transaction_id' => $transaction->id,
                'payment_url'    => $qrisResult['id'],
                'qr_code_image'  => $qrImageUrl,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status"  => "error",
                "message" => "Gagal menyimpan transaksi: " . $e->getMessage()
            ], 500);
        }

        return response()->json([
            "status"  => "success",
            "message" => [
                "order_id"       => $orderId,
                "transaction_id" => $qrisResult['id'],
                "payment_status" => "pending",
                "qr_image"       => url('/storage/qrcodes/' . $qrImageUrl)
            ]
        ], 200);
    }

    function generateQRCode($data, $orderId, $textAbove = '0', $textBelow = 'Merchant Name')
    {
        // Konfigurasi opsi QR Code
        $options = new QROptions([
            'version'          => QRCode::VERSION_AUTO,  // Versi otomatis menyesuaikan ukuran QR Code
            'outputType'       => QRCode::OUTPUT_IMAGE_JPG,
            'eccLevel'         => QRCode::ECC_L,
            'scale'            => 4,
            'imageBase64'      => false,
            'bgColor'          => [255, 255, 255, 127],
            'imageTransparent' => true,
        ]);

        // Dapatkan URL background dan konversikan menjadi path file lokal
        $backgroundImageUrl = asset('assets/img/qristempl.jpg');
        $bgFilePath = public_path(parse_url($backgroundImageUrl, PHP_URL_PATH));
        $bgGdImage = imagecreatefromjpeg($bgFilePath);

        // Buat QR Code dan render ke dalam image binary
        $qrCode = new QRCode($options);
        $qrImage = $qrCode->render($data);
        $qrGdImage = imagecreatefromstring($qrImage);

        // Mendapatkan dimensi gambar QR dan background
        $qrWidth  = imagesx($qrGdImage);
        $qrHeight = imagesy($qrGdImage);
        $bgWidth  = imagesx($bgGdImage);
        $bgHeight = imagesy($bgGdImage);

        // Menghitung posisi agar QR Code berada di tengah background
        $dstX = ($bgWidth - $qrWidth) / 2;
        $dstY = ($bgHeight - $qrHeight) / 2;

        // Menyalin QR Code ke background, sedikit diturunkan secara vertikal (offset +20)
        imagecopy($bgGdImage, $qrGdImage, (int)$dstX, (int)($dstY + 20), 0, 0, $qrWidth, $qrHeight);

        // Mengatur ukuran font untuk teks (nilai 1 sampai 5)
        $fontSize = 5;

        // Menghitung posisi untuk teks di atas (textAbove)
        $textAboveWidth = imagefontwidth($fontSize) * strlen($textAbove);
        $textAboveX = ($bgWidth - $textAboveWidth) / 2;
        // Posisi teks atas: sesuaikan offset Y jika perlu
        $textAboveY = $dstY - 30;

        // Menghitung posisi untuk teks di bawah (textBelow)
        $textBelowWidth = imagefontwidth($fontSize) * strlen($textBelow);
        $textBelowX = ($bgWidth - $textBelowWidth) / 2;
        // Ubah offset Y untuk teks bawah agar tidak tertutup, misal tambahkan nilai offset yang lebih besar
        $textBelowY = $dstY + $qrHeight + 20;

        // Pilih warna teks, misalnya hitam
        $textColor = imagecolorallocate($bgGdImage, 0, 0, 0);

        // Menuliskan teks pada gambar
        imagestring($bgGdImage, $fontSize, (int)$textAboveX, (int)$textAboveY + 45, $textAbove, $textColor);
        imagestring($bgGdImage, $fontSize, (int)$textBelowX, (int)$textBelowY, $textBelow, $textColor);

        // Simpan gambar akhir ke folder storage
        $folder = storage_path('app/public/qrcodes/');
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        $filePath = $folder . $orderId . '.jpg';
        imagejpeg($bgGdImage, $filePath);

        imagedestroy($qrGdImage);
        imagedestroy($bgGdImage);

        return $filePath;
    }

    public function checkPaymentStatus(Request $request)
    {
        $orderId = $request->query('order_id');

        if (!$orderId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order ID is required'
            ], 400);
        }

        try {
            $transaction = Transaction::where('order_id', $orderId)->first();

            if ($transaction) {
                $status      = $transaction->status;
                $qrCodeImage = $transaction->qr_code_image;

                if ($status === 'success') {
                    $deviceStatus = null;

                    if ($qrCodeImage) {
                        $this->deleteQRCode($orderId);
                    }

                    return response()->json([
                        'status'  => 'success',
                        'message' => [
                            'order_id'       => $orderId,
                            'payment_status' => $status,
                            'device_status'  => $deviceStatus,
                            'qr_code_deleted' => true,
                            'description'    => 'Pembayaran Berhasil.'
                        ]
                    ]);
                } else {
                    return response()->json([
                        'status'  => 'success',
                        'message' => [
                            'order_id'       => $orderId,
                            'payment_status' => $status,
                            'qr_code_deleted' => false,
                            'description'    => 'Pembayaran tidak berhasil.'
                        ]
                    ]);
                }
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => [
                        'order_id'    => $orderId,
                        'description' => 'Order not found.'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Hapus file QR Code berdasarkan orderId.
     */
    private function deleteQRCode($orderId)
    {
        // Misalnya QR Code disimpan di storage/app/public/qrcodes dengan ekstensi .jpg
        $filePath = storage_path('app/public/qrcodes/' . $orderId . '.jpg');

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function updateTransactionStatus(Request $request)
    {
        // Ambil data notifikasi dari request JSON
        $notification = $request->json()->all();

        if (isset($notification['data']['reference_id'], $notification['data']['status'])) {
            $referenceId = $notification['data']['reference_id'];
            $transactionStatus = $notification['data']['status'];

            // Ubah 'SUCCEEDED' menjadi 'success'
            if (strtoupper($transactionStatus) === 'SUCCEEDED') {
                $transactionStatus = 'success';
            }

            try {
                Transaction::where('order_id', $referenceId)
                    ->update(['status' => $transactionStatus]);

                // $transaction = Transaction::where('order_id', $referenceId)->first();


                return response()->json([
                    'status'  => 'success',
                    'message' => 'Transaction updated successfully'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to update transaction status: ' . $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid notification data'
            ], 400);
        }
    }

    public function checkPaymentStatus2(Request $request)
    {
        $service_type = $request->query('service_type');
        $code_device = $request->query('device_code');

        if (!$service_type) {
            return response()->json([
                'status'  => 'error',
                'message' => 'service_type is required'
            ], 400);
        }
        $device = Device::where('code', $code_device)->first();

        if (!$device) {
            return response()->json([
                'status'  => 'error',
                'message' => 'device code is not registered'
            ], 400);
        }

        try {
            $transaction = Transaction::where('status', 'success')
                ->where('service_type', $service_type)
                ->where('type', 'qris')
                ->where('device_code', $code_device)
                ->whereHas('qrisTransaction', function ($query) {
                    $query->where('status_device', true);
                })
                ->where('created_at', '>=', Carbon::now()->subHour())
                ->orderBy('created_at', 'asc')
                ->first();

            $orderId = $transaction ? $transaction->order_id : null;

            if ($transaction) {
                $status      = $transaction->status;
                $qrCodeImage = $transaction->qr_code_image;

                if ($status === 'success') {
                    $deviceStatus =  QrisTransactionDetail::where('transaction_id', $transaction->id)
                        ->value('status_device');

                    if ($qrCodeImage) {
                        $this->deleteQRCode($orderId);
                    }

                    QrisTransactionDetail::where('transaction_id', $transaction->id)
                        ->update(['status_device' => false]);

                    return response()->json([
                        'status'  => 'success',
                        'message' => [
                            'order_id'       => $orderId,
                            'payment_status' => $status,
                            'device_status'  => $deviceStatus,
                            'amount' => $transaction->amount,
                            'description'    => 'Pembayaran Berhasil.'
                        ]
                    ]);
                } else {
                    return response()->json([
                        'status'  => 'success',
                        'message' => [
                            'order_id'       => $orderId,
                            'payment_status' => $status,
                            'amount' => $transaction->amount,
                            'description'    => 'Pembayaran tidak berhasil.'
                        ]
                    ]);
                }
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => [
                        'order_id'    => $orderId,
                        'description' => 'Order not found.'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}
