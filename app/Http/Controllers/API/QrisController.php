<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\QrisTransactionDetail;
use App\Models\Transaction;
use App\Services\Payments\Qris\QrisGatewayManager;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QrisController extends Controller
{
    private const MINIMUM_QRIS_AMOUNT = 10;

    public function __construct(
        protected QrisGatewayManager $gatewayManager,
    ) {
    }

    public function qr_request(Request $request)
    {
        $amount = (int) $request->input('amount');
        $type = (string) $request->input('type');
        $deviceCode = (string) $request->input('device_code');

        if ($amount < self::MINIMUM_QRIS_AMOUNT || $type === '' || $deviceCode === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter tidak valid',
            ], 400);
        }

        try {
            $provider = $this->gatewayManager->normalizeProvider($request->input('provider'));
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider QRIS tidak didukung.',
            ], 400);
        }

        $device = Device::with('outlet')->where('code', $deviceCode)->first();

        if (! $device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device tidak ditemukan',
            ], 404);
        }

        if (! $device->outlet) {
            return response()->json([
                'status' => 'error',
                'message' => 'Outlet untuk device ini tidak ditemukan',
            ], 404);
        }

        if ((int) $device->outlet->status === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, outlet sedang tutup. Tidak bisa melakukan request pembayaran.',
            ], 403);
        }

        $outlet = $device->outlet;

        if ($outlet->has_overdue_billing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Masa aktif QRIS habis. Silakan perpanjang.',
            ], 200);
        }

        $orderId = strtoupper($type . '-' . $outlet->code . '-' . now()->getTimestampMs() . Str::random(4));
        $callbackUrl = route('payment-callback');

        $owner = $outlet->owner;
        $activeProvider = $provider;
        $additionalMetadata = [];

        // Logika Akun Privat: Selalu cek pilihan toggle Owner (Private vs General).
        // Jika owner memilih 'owner' (Private), maka kita gunakan jalur midtrans_partner.
        // Jika owner memilih 'general', maka tetap gunakan midtrans (General), meskipun data merchant_id ada isinya.
        if ($activeProvider === 'midtrans') {
            if ($owner->payment_account_type === 'owner') {
                $activeProvider = 'midtrans_partner';
                $additionalMetadata = [
                    'merchant_id' => $owner->merchant_id,
                    'partner_id' => config('services.midtrans_partner.partner_id'),
                    'partner_server_key' => config('services.midtrans_partner.server_key'),
                ];
            }
            // Jika 'general', biarkan $activeProvider tetap 'midtrans'
        }

        try {
            $charge = $this->gatewayManager->resolve($activeProvider)->createCharge(
                orderId: $orderId,
                amount: $amount,
                callbackUrl: $callbackUrl,
                metadata: array_merge([
                    'device_code' => $deviceCode,
                    'service_type' => $type,
                    'outlet_code' => $outlet->code,
                    'customer_name' => $outlet->owner->brand_name ?? 'Pelanggan',
                    'customer_email' => $outlet->owner->user->email ?? 'pelanggan@bospengering.com',
                ], $additionalMetadata),
            );

            
        } catch (\Throwable $e) {
            Log::error('QRIS charge creation failed.', [
                'provider' => $activeProvider,
                'order_id' => $orderId,
                'device_code' => $deviceCode,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat QRIS: ' . $e->getMessage(),
            ], 500);
        }

        $amountFormatted = 'Rp ' . number_format($charge['amount'], 0, ',', '.');
        $filePath = $this->generateQRCode(
            $charge['qr_string'],
            $orderId,
            $outlet->outlet_name ?? $outlet->code,
            $amountFormatted
        );
        $qrImageUrl = basename($filePath);

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'order_id' => $orderId,
                'outlet_id' => $outlet->id,
                'amount' => $charge['amount'],
                'total_amount' => $charge['amount'],
                'fee_amount' => 0,
                'type' => 'qris',
                'timezone' => $outlet->timezone,
                'status' => 'pending',
                'owner_id' => $outlet->owner_id,
                'date' => now(),
                'time' => now(),
            ]);

            QrisTransactionDetail::create([
                'transaction_id' => $transaction->id,
                'payment_provider' => $charge['provider'],
                'gateway_reference' => $charge['gateway_reference'],
                'payment_url' => $charge['payment_url'],
                'qr_code_image' => $qrImageUrl,
                'service_type' => $type,
                'device_code' => $deviceCode,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->deleteQRCode($orderId);

            Log::error('Failed to persist QRIS transaction.', [
                'provider' => $provider,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => [
                'order_id' => $orderId,
                'transaction_id' => $charge['gateway_reference'],
                'payment_status' => 'pending',
                'provider' => $charge['provider'],
                'api_url' => $charge['api_url'] ?? null,
                'qr_image' => url('/storage/qrcodes/' . $qrImageUrl, [], false),
                'expiresAt' => $charge['expires_at']
                    ?? now()->addMinutes((int) config('payments.qris.client_expiry_minutes', 1))->toIso8601String(),
            ],
        ], 200);
    }

    public function generateQRCode($data, $orderId, $textAbove = '0', $textBelow = 'Merchant Name')
    {
        $options = new QROptions([
            'version' => QRCode::VERSION_AUTO,
            'outputType' => QRCode::OUTPUT_IMAGE_JPG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 4,
            'imageBase64' => false,
            'bgColor' => [255, 255, 255, 127],
            'imageTransparent' => true,
        ]);

        $backgroundImageUrl = asset('assets/img/qristempl.jpg');
        $bgFilePath = public_path(parse_url($backgroundImageUrl, PHP_URL_PATH));
        $bgGdImage = imagecreatefromjpeg($bgFilePath);

        $qrCode = new QRCode($options);
        $qrImage = $qrCode->render($data);
        $qrGdImage = imagecreatefromstring($qrImage);

        $qrWidth = imagesx($qrGdImage);
        $qrHeight = imagesy($qrGdImage);
        $bgWidth = imagesx($bgGdImage);
        $bgHeight = imagesy($bgGdImage);

        $dstX = ($bgWidth - $qrWidth) / 2;
        $dstY = ($bgHeight - $qrHeight) / 2;

        imagecopy($bgGdImage, $qrGdImage, (int) $dstX, (int) ($dstY + 20), 0, 0, $qrWidth, $qrHeight);

        $fontSize = 5;
        $textAboveWidth = imagefontwidth($fontSize) * strlen($textAbove);
        $textAboveX = ($bgWidth - $textAboveWidth) / 2;
        $textAboveY = $dstY - 30;

        $textBelowWidth = imagefontwidth($fontSize) * strlen($textBelow);
        $textBelowX = ($bgWidth - $textBelowWidth) / 2;
        $textBelowY = $dstY + $qrHeight + 20;

        $textColor = imagecolorallocate($bgGdImage, 0, 0, 0);

        imagestring($bgGdImage, $fontSize, (int) $textAboveX, (int) $textAboveY + 45, $textAbove, $textColor);
        imagestring($bgGdImage, $fontSize, (int) $textBelowX, (int) $textBelowY, $textBelow, $textColor);

        $folder = storage_path('app/public/qrcodes/');

        if (! file_exists($folder)) {
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

        if (! $orderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order ID is required',
            ], 400);
        }

        try {
            $transaction = Transaction::where('order_id', $orderId)
                ->with(['deviceTransactions', 'qrisTransaction'])
                ->first();

            if (! $transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => [
                        'order_id' => $orderId,
                        'description' => 'Order not found.',
                    ],
                ]);
            }

            $status = $transaction->status;
            $qrCodeImage = $transaction->qrisTransaction?->qr_code_image;

            if ($status === 'success' || ($status === 'pending' && $transaction->qrisTransaction?->bypass_status === 'active')) {
                $deviceStatus = null;

                if ($qrCodeImage) {
                    $this->deleteQRCode($orderId);
                }

                $deviceTransaction = $transaction->deviceTransactions->sortByDesc('id')->first();

                if ($deviceTransaction) {
                    $deviceStatus = $deviceTransaction->status;

                    $deviceTransaction->update([
                        'status' => false,
                        'activated_at' => now(),
                    ]);

                    QrisTransactionDetail::where('transaction_id', $transaction->id)->update([
                        'service_type' => $deviceTransaction->service_type,
                        'device_code' => $deviceTransaction->device_code,
                    ]);

                    if ($status === 'pending' && $transaction->qrisTransaction?->bypass_status === 'active') {
                        $transaction->qrisTransaction->update([
                            'bypass_status' => 'activated',
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => [
                        'order_id' => $orderId,
                        'payment_status' => $status,
                        'device_status' => $deviceStatus,
                        'qr_code_deleted' => true,
                        'description' => 'Pembayaran Berhasil.',
                    ],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => [
                    'order_id' => $orderId,
                    'payment_status' => $status,
                    'qr_code_deleted' => false,
                    'description' => 'Pembayaran tidak berhasil.',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ]);
        }
    }

    public function checkPaymentStatus2(Request $request)
    {
        $serviceType = $request->query('service_type');
        $deviceCode = $request->query('device_code');

        if (! $serviceType) {
            return response()->json([
                'status' => 'error',
                'message' => 'service_type is required',
            ], 400);
        }

        $device = Device::where('code', $deviceCode)->first();

        if (! $device) {
            return response()->json([
                'status' => 'error',
                'message' => 'device code is not registered',
            ], 400);
        }

        try {
            $transaction = Transaction::where(function ($q) {
                $q->where('status', 'success')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'pending')
                            ->whereHas('qrisTransaction', function ($qrisQuery) {
                                $qrisQuery->where('bypass_status', 'active');
                            });
                    });
            })
                ->where('type', 'qris')
                ->whereHas('deviceTransactions', function ($query) use ($serviceType, $deviceCode) {
                    $query->where('service_type', $serviceType)
                        ->where('device_code', $deviceCode)
                        ->where('status', true);
                })
                ->where(function ($query) {
                    $query->where('created_at', '>=', Carbon::now()->subHour())
                        ->orWhereHas('qrisTransaction', function ($q) {
                            $q->where('bypass_status', 'active');
                        });
                })
                ->with(['deviceTransactions', 'qrisTransaction'])
                ->orderBy('created_at', 'asc')
                ->first();

            $orderId = $transaction?->order_id;

            if (! $transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => [
                        'order_id' => $orderId,
                        'description' => 'Order not found.',
                    ],
                ]);
            }

            $status = $transaction->status;
            $qrCodeImage = $transaction->qrisTransaction?->qr_code_image;

            if ($status === 'success' || ($status === 'pending' && $transaction->qrisTransaction?->bypass_status === 'active')) {
                $deviceTransaction = $transaction->deviceTransactions->first();
                $deviceStatus = null;

                if ($deviceTransaction) {
                    $deviceStatus = $deviceTransaction->status;

                    if ($qrCodeImage) {
                        $this->deleteQRCode($orderId);
                    }

                    $deviceTransaction->update([
                        'status' => false,
                        'activated_at' => now(),
                    ]);

                    if ($status === 'pending' && $transaction->qrisTransaction?->bypass_status === 'active') {
                        $transaction->qrisTransaction->update([
                            'bypass_status' => 'activated',
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => [
                        'order_id' => $orderId,
                        'payment_status' => $status,
                        'device_status' => $deviceStatus,
                        'amount' => $transaction->amount,
                        'description' => 'Pembayaran Berhasil.',
                    ],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => [
                    'order_id' => $orderId,
                    'payment_status' => $status,
                    'amount' => $transaction->amount,
                    'description' => 'Pembayaran tidak berhasil.',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ]);
        }
    }

    private function deleteQRCode(string $orderId): void
    {
        $filePath = storage_path('app/public/qrcodes/' . $orderId . '.jpg');

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
