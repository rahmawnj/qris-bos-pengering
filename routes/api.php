<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\API\CashierController;
use App\Http\Controllers\API\DeviceController as APIDeviceController;
use App\Http\Controllers\API\MidtransPartnerQrisController;
use App\Http\Controllers\API\QrisController;
use App\Http\Controllers\API\QrisWebhookController;
use App\Http\Controllers\Partner\TopupController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('topup/member', [TopupController::class, 'fetchMemberByRFID'])->name('topup.member');

// bypass
Route::post('devices/{device}/update-status', [APIDeviceController::class, 'toggleStatus'])->name('api.devices.update-device');
Route::get('check-device', [APIDeviceController::class, 'checkDeviceStatus']);

// qris
Route::post('qr-request', [QrisController::class, 'qr_request']);
Route::get('midtrans-partner/qris/charge', [MidtransPartnerQrisController::class, 'charge']);
Route::get('midtrans-partner/qris/test', [MidtransPartnerQrisController::class, 'testPartnerCheck']);
Route::get('midtrans-partner/qris/debug', [MidtransPartnerQrisController::class, 'debug']);
Route::get('payment-check', [QrisController::class, 'checkPaymentStatus']);
Route::get('payment-check-2', [QrisController::class, 'checkPaymentStatus2']);
Route::post('payment-status-update', [QrisWebhookController::class, 'auto'])->name('payment-callback');
Route::post('payment-status-update/xendit', [QrisWebhookController::class, 'xendit'])->name('xendit.payment-callback');
Route::post('payment-status-update/midtrans', [QrisWebhookController::class, 'midtrans'])->name('midtrans.payment-callback');

Route::get('/device-price/{device}/{serviceType}', [CashierController::class, 'getPrice'])->name('api.device.price');
