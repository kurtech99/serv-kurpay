<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MerchantDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PaymentController;

// Landing & Auth
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:15,1');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Public Payment Checkout Page (Pelanggan Hotspot Scan QRIS)
Route::get('/pay/{trx_id}', [PaymentController::class, 'checkout'])->name('pay.checkout');
Route::get('/pay/{trx_id}/qr', [PaymentController::class, 'qrImage'])->name('pay.qr');
Route::get('/pay/{trx_id}/status', [PaymentController::class, 'checkStatus'])->name('pay.status');

// Merchant / Tenant Portal
Route::middleware(['auth'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/', [MerchantDashboardController::class, 'index'])->name('dashboard');
    Route::get('/merchants', [MerchantDashboardController::class, 'merchants'])->name('merchants');
    Route::post('/merchants', [MerchantDashboardController::class, 'storeMerchant'])->name('merchants.store');
    Route::get('/merchants/{id}/switch', [MerchantDashboardController::class, 'switchMerchant'])->name('merchants.switch');
    Route::delete('/merchants/{id}', [MerchantDashboardController::class, 'deleteMerchant'])->name('merchants.delete');
    Route::get('/withdrawal', [MerchantDashboardController::class, 'withdrawal'])->name('withdrawal');
    Route::post('/withdrawal', [MerchantDashboardController::class, 'submitWithdrawal'])->name('withdrawal.submit');
    Route::post('/bank', [MerchantDashboardController::class, 'updateBank'])->name('bank.update');
    Route::post('/auto-withdraw', [MerchantDashboardController::class, 'updateAutoWithdraw'])->name('autowithdraw.update');
    Route::get('/api-credentials', [MerchantDashboardController::class, 'apiCredentials'])->name('api.credentials');
    Route::post('/regenerate-keys', [MerchantDashboardController::class, 'regenerateKeys'])->name('api.regenerate');
    Route::post('/webhook', [MerchantDashboardController::class, 'updateWebhook'])->name('webhook.update');
    Route::post('/gateway-mode', [MerchantDashboardController::class, 'updateGatewayMode'])->name('gateway.update');
    Route::post('/deposit/topup', [MerchantDashboardController::class, 'topupDeposit'])->name('deposit.topup');
    Route::post('/transactions/{id}/mark-paid', [MerchantDashboardController::class, 'markPaid'])->name('transactions.markPaid');
});

// SuperAdmin Portal (Khusus Admin Kurtech)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/withdrawals', [AdminDashboardController::class, 'withdrawals'])->name('withdrawals');
    Route::post('/withdrawals/{id}/approve', [AdminDashboardController::class, 'approveWithdrawal'])->name('withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [AdminDashboardController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
    Route::get('/merchants', [AdminDashboardController::class, 'merchants'])->name('merchants');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminDashboardController::class, 'saveSettings'])->name('settings.save');
});