<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\ExportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OutletController;

use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Partner\TopupController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Partner\WithdrawalController;
use App\Http\Controllers\Partner\MemberPaymentController;
use App\Http\Controllers\Partner\ReceiptConfigController;
use App\Http\Controllers\Member\DashboardMemberController;
use App\Http\Controllers\Partner\CashierPaymentController;
use App\Http\Controllers\Partner\DashboardOwnerController;
use App\Http\Controllers\Partner\PartnerCashierController;
use App\Http\Controllers\Partner\ManualTransactionController;
use App\Http\Controllers\Partner\AddonController as PartnerAddonController;
use App\Http\Controllers\Partner\BrandController as PartnerBrandController;
use App\Http\Controllers\Partner\DeviceController as PartnerDeviceController;
use App\Http\Controllers\Partner\MemberController as PartnerMemberController;
use App\Http\Controllers\Partner\OutletController as PartnerOutletController;
use App\Http\Controllers\Admin\BypassLogController as AdminBypassLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\QrisBillingController as AdminQrisBillingController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\Partner\BypassLogController as PartnerBypassLogController;
use App\Http\Controllers\Partner\TransactionController as PartnerTransactionController;
use App\Http\Controllers\Partner\QrisBillingController as PartnerQrisBillingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/home/transaction/{order_id}', [LandingController::class, 'transaction'])->name('home.transaction');

// API Documentation Routes (password protected via "tazaka123")
Route::get('/api-docs', [ApiDocsController::class, 'index'])->name('api-docs.index');
Route::get('/api-flows', [ApiDocsController::class, 'flows'])->name('api-docs.flows');
Route::post('/api-docs/auth', [ApiDocsController::class, 'auth'])->name('api-docs.auth');
Route::get('/api-docs/logout', [ApiDocsController::class, 'logout'])->name('api-docs.logout');


Auth::routes();
Route::get('/storage/{filename}', function ($filename) {
    $path = storage_path('app/public/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
})->where('filename', '.*');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/home', function () {
    if (Auth::guard('admin_config')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->user()->role == 'owner' || auth()->user()->role == 'cashier') {
        return redirect()->route('partner.dashboard');
    }
    return redirect()->route('admin.dashboard');
})->name('home');


Route::get('export/admin-transactions', [ExportController::class, 'adminTransactions'])->name('export.admin-transactions');
Route::get('export/partner-transactions', [ExportController::class, 'partnerTransactions'])->name('export.partner-transactions');
Route::get('export/manual-transactions', [ExportController::class, 'manualTransactions'])->name('export.manual-transactions');
Route::get('export/qris-transactions', [ExportController::class, 'qrisTransactions'])->name('export.qris-transactions');

// Route::middleware(['auth'])->group(function () {
    Route::get('profile', [ProfileController::class, 'form'])->name('profile.form');
    Route::patch('profile', [ProfileController::class, 'submit'])->name('profile.submit');

    // dd('lolos 1');
    Route::prefix('admin')->name('admin.')->middleware('admin_access')->group(function () {

    // dd('a');
    // Route::get('dashboard', function() {
    // // dd('b');
    //      return "Suksess! Kamu login sebagai Admin.";
// })->name('dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('bypass/logs', [AdminBypassLogController::class, 'index'])->name('bypass.logs');
        Route::get('accounts', [AccountController::class, 'allAccounts'])->name('accounts.all');
        Route::get('accounts/export-data', [AccountController::class, 'exportData'])->name('accounts.export-data');
        Route::put('owners/{owner}/update-status', [AccountController::class, 'updateOwnerStatus'])->name('owners.update.status');
        Route::post('owners/{owner}/impersonate', [AccountController::class, 'impersonateOwner'])->name('owners.impersonate');
        Route::post('impersonate/stop', [AccountController::class, 'stopImpersonate'])->name('impersonate.stop');

        Route::get('withdrawal/list', [AdminWithdrawalController::class, 'listWithdrawals'])->name('withdrawal.list');
        Route::get('withdrawal/histories', [AdminWithdrawalController::class, 'histories'])->name('withdrawal.histories');

        Route::get('withdrawal/reduce', [AdminWithdrawalController::class, 'reduceCreate'])->name('withdrawal.reduce.create');
        Route::post('withdrawal/reduce', [AdminWithdrawalController::class, 'reduceStore'])->name('withdrawal.reduce.store');

        Route::get('withdrawal/{withdrawal}', [AdminWithdrawalController::class, 'withdrawal_request'])->name('withdrawal.request');
        Route::post('withdrawal', [AdminWithdrawalController::class, 'withdrawal_store'])->name('withdrawal.store');

        Route::resource('service_types', ServiceTypeController::class);
        Route::resource('users', UserController::class);
        Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
        Route::get('qris-billing', [AdminQrisBillingController::class, 'index'])->name('qris-billing.index');
        Route::get('qris-billing/report', [AdminQrisBillingController::class, 'report'])->name('qris-billing.report');
        Route::get('qris-billing/payment/{payment}', [AdminQrisBillingController::class, 'showPayment'])->name('qris-billing.payment.show');
        Route::get('qris-billing/{outlet}', [AdminQrisBillingController::class, 'show'])->name('qris-billing.show');
        Route::patch('qris-billing/{outlet}/mark-paid', [AdminQrisBillingController::class, 'markPaid'])->name('qris-billing.mark-paid');
        Route::patch('outlets/{outlet}/qris-billing/mark-paid', [AdminQrisBillingController::class, 'markPaid'])->name('outlets.qris-billing.mark-paid');
        Route::get('outlets/export-data', [OutletController::class, 'exportData'])->name('outlets.export-data');
        Route::resource('outlets', OutletController::class);
        Route::get('cashiers/export-data', [CashierController::class, 'exportData'])->name('cashiers.export-data');
        Route::resource('cashiers', CashierController::class);
        Route::get('owners/export-data', [OwnerController::class, 'exportData'])->name('owners.export-data');
        Route::resource('owners', OwnerController::class);
        Route::resource('devices', DeviceController::class);
        Route::resource('members', MemberController::class);
        Route::resource('addons', AddonController::class);

        Route::get('setting', [SettingController::class, 'form'])->name('setting.form');
        Route::patch('setting', [SettingController::class, 'submit'])->name('setting.submit');

        Route::get('transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/qris', [AdminTransactionController::class, 'qris_transaction'])->name('transactions.qris');
        Route::get('transactions/manual', [AdminTransactionController::class, 'manual_transaction'])->name('transactions.manual');
        Route::get('transactions/verifications', [AdminTransactionController::class, 'manual_verification_list'])->name('transactions.verifications');
        Route::post('transactions/{transaction}/check-payment-gateway', [AdminTransactionController::class, 'checkPaymentGateway'])->name('transactions.check_payment_gateway');
        Route::patch('transactions/{transaction}/verify-status', [AdminTransactionController::class, 'verify_status'])->name('transactions.verify_status');
        Route::get('transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');
        Route::post('transactions/{transaction}/bypass', [AdminTransactionController::class, 'bypass'])->name('transactions.bypass');
        Route::patch('transactions/{transaction}/bypass-status', [AdminTransactionController::class, 'updateBypassStatus'])->name('transactions.update_bypass_status');

        Route::delete('transactions/{transaction}', [AdminTransactionController::class, 'destroy'])
            ->name('transactions.destroy');
    });

    Route::prefix('partner')->name('partner.')->middleware('checkrole:owner,outlet,cashier')->group(function () {
        Route::get('device/list', [DashboardOwnerController::class, 'device_list'])->name('device.list');
        Route::post('devices', [DashboardOwnerController::class, 'storeDevice'])->name('device.store');
        Route::patch('devices/{device}', [DashboardOwnerController::class, 'updateDeviceDetails'])->name('device.update');
        Route::patch('devices/{device}/status', [DashboardOwnerController::class, 'updateDeviceStatus'])->name('device.update_status');
        Route::patch('devices/{device}/service-types', [DashboardOwnerController::class, 'updateDeviceServicePrices'])->name('device.service_types.update');
        Route::delete('devices/{device}', [DashboardOwnerController::class, 'destroyDevice'])->name('device.destroy');

        Route::get('outlets/list', [PartnerOutletController::class, 'list'])->name('outlets.list');
        Route::get('outlets/{outlet}/detail', [PartnerOutletController::class, 'detail'])->name('outlets.detail');
        Route::get('outlets/{outlet}/billing', [PartnerOutletController::class, 'billing'])->name('outlets.billing');
        Route::post('outlets/{outlet}/billing/upload', [PartnerOutletController::class, 'uploadBillingProof'])->name('outlets.billing.upload');
        Route::redirect('qris-billing', 'qris-billing/report')->name('qris-billing.index');
        Route::get('qris-billing/report', [PartnerQrisBillingController::class, 'report'])->name('qris-billing.report');
        Route::get('qris-billing/payment/{payment}', [PartnerQrisBillingController::class, 'showPayment'])->name('qris-billing.payment.show');
        Route::get('qris-billing/{outlet}/upload', [PartnerQrisBillingController::class, 'upload'])->name('qris-billing.upload');
        Route::get('qris-billing/{outlet}', [PartnerQrisBillingController::class, 'show'])->name('qris-billing.show');
        Route::post('outlets', [PartnerOutletController::class, 'store'])->name('outlets.store'); // Rute BARU untuk menyimpan outlet
        Route::patch('outlets/{outlet}/service-list', [PartnerOutletController::class, 'serviceType'])->name('outlets.services.update');
        Route::put('outlets/{outlet}', [PartnerOutletController::class, 'update'])->name('outlets.update');
        Route::patch('outlets/{outlet}', [PartnerOutletController::class, 'update'])->name('outlets.patch_update'); // Often good to have both PUT and PATCH for updates
        Route::delete('outlets/{outlet}', [PartnerOutletController::class, 'destroy'])->name('outlets.destroy');
        Route::patch('outlets/{outlet}/update-status', [PartnerOutletController::class, 'updateStatus'])->name('outlets.update-status');

        Route::get('addons', [PartnerAddonController::class, 'index'])->name('addons.index');
        Route::get('addons/create', [PartnerAddonController::class, 'create'])->name('addons.create');
        Route::post('addons', [PartnerAddonController::class, 'store'])->name('addons.store');
        Route::get('addons/{addon}/edit', [PartnerAddonController::class, 'edit'])->name('addons.edit');
        Route::patch('addons/{addon}', [PartnerAddonController::class, 'update'])->name('addons.update');
        Route::delete('addons/{addon}', [PartnerAddonController::class, 'destroy'])->name('addons.destroy');

        Route::get('cashier-payment', [CashierPaymentController::class, 'create'])->name('cashier.payment.create');
        Route::post('cashier-payment', [CashierPaymentController::class, 'store'])->name('cashier.payment.store');

        Route::get('service-order', [PartnerDeviceController::class, 'serviceOrder'])->name('service-orders.list');
        Route::get('service-order/{id}', [PartnerDeviceController::class, 'serviceOrderDetail'])->name('service-orders.detail');
        Route::get('service-orders/activate-device/{deviceTransaction}', function ($deviceTransaction) {
            return redirect()->route('partner.service-orders.list')
                ->with('error', 'Sesi Anda mungkin sudah berakhir. Silakan login ulang lalu klik tombol "Mulai Layanan" kembali.');
        })->name('service-orders.activate-device.fallback');
        Route::post('service-orders/activate-device/{deviceTransaction}', [PartnerDeviceController::class, 'activateDeviceService'])->name('service-orders.activate-device');
        Route::get('service-orders/update-progress/{manualTransactionDetail}', function ($manualTransactionDetail) {
            return redirect()->route('partner.service-orders.list')
                ->with('error', 'Status progres harus diubah dari halaman service order.');
        })->name('service-orders.update-progress.fallback');
        Route::post('service-orders/update-progress/{manualTransactionDetail}', [PartnerDeviceController::class, 'updateServiceProgress'])->name('service-orders.update-progress');

        Route::get('brand/profile', [PartnerBrandController::class, 'form'])->name('brand.profile.edit');
        Route::put('brand/profile', [PartnerBrandController::class, 'submit'])->name('brand.profile.update');

        // Route::get('dashboard', [DashboardOwnerController::class, 'dashboard'])->name('dashboard');
Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('withdrawal', [WithdrawalController::class, 'withdrawal_request'])->name('withdrawal.request');
        Route::post('withdrawal', [WithdrawalController::class, 'withdrawal_store'])->name('withdrawal.store');
        Route::get('withdrawal/histories', [WithdrawalController::class, 'histories'])->name('withdrawal.histories');

        Route::get('members/verified', [PartnerMemberController::class, 'verified'])->name('members.verified');
        Route::get('members/unverified', [PartnerMemberController::class, 'unverified'])->name('members.unverified');
        Route::post('members/{member}/verify', [PartnerMemberController::class, 'verify'])->name('members.verify');
        Route::delete('members/{member}/subscription', [PartnerMemberController::class, 'destroySubscription'])->name('members.subscription.destroy');

        Route::get('topup', [TopupController::class, 'showTopupForm'])->name('topup');
        Route::get('topup/histories', [TopupController::class, 'topupHistories'])->name('topup.histories');
        Route::post('topup', [TopupController::class, 'processTopup'])->name('topup.store');

        Route::get('member-payment', [MemberPaymentController::class, 'create'])->name('member.payment.create');
        Route::post('member-payment', [MemberPaymentController::class, 'store'])->name('member.payment.store');

        Route::get('bypass/logs', [AdminBypassLogController::class, 'index'])->name('bypass.logs');

        // Route::get('transactions', [PartnerTransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');

        // Route::get('manual-transactions', [ManualTransactionController::class, 'transactions'])->name('manual.transactions');
        Route::get('transactions/qris', [AdminTransactionController::class, 'qris_transaction'])->name('transactions.qris');
        Route::get('transactions/verifications', [AdminTransactionController::class, 'manual_verification_list'])->name('transactions.verifications');
        Route::get('transactions/manual', [AdminTransactionController::class, 'manual_transaction'])->name('transactions.manual');
        Route::get('transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');
        Route::post('transactions/{transaction}/bypass', [AdminTransactionController::class, 'bypass'])->name('transactions.bypass');
        Route::patch('transactions/{transaction}/bypass-status', [AdminTransactionController::class, 'updateBypassStatus'])->name('transactions.update_bypass_status');

        // Route::get('qris-transactions', [PartnerTransactionController::class, 'qris_transaction'])->name('qris.transactions');
        // Route::get('member-transactions', [MemberPaymentController::class, 'member_transaction'])->name('member.transactions');

        Route::get('cashiers', [PartnerCashierController::class, 'index'])->name('cashiers.list');
        Route::post('cashiers', [PartnerCashierController::class, 'store'])->name('cashiers.store');
        Route::patch('cashiers/{cashier}', [PartnerCashierController::class, 'update'])->name('cashiers.update');
        Route::delete('cashiers/{cashier}', [PartnerCashierController::class, 'destroy'])->name('cashiers.destroy');

        Route::get('receipt-config', [ReceiptConfigController::class, 'edit'])->name('receipt.config.edit');
        Route::put('receipt-config', [ReceiptConfigController::class, 'update'])->name('receipt.config.update');
    });

    Route::prefix('member')->name('member.')->middleware('checkrole:member')->group(function () {
        Route::get('dashboard', [DashboardMemberController::class, 'dashboard'])->name('dashboard');
        Route::get('membership', [DashboardMemberController::class, 'membership'])->name('membership');
        Route::get('outlet-list', [DashboardMemberController::class, 'outlet_list'])->name('outlet_list');
        Route::post('subscription', [DashboardMemberController::class, 'subscription'])->name('subscription.store');
    });
// });
