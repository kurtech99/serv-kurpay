<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\DanaCallbackController;

// API v1 untuk Mikhmon & Billing Kurtech
Route::prefix('v1')->group(function () {
    Route::post('/transaction/create', [ApiController::class, 'createTransaction']);
    Route::get('/transaction/detail', [ApiController::class, 'detailTransaction']);
});

// Webhook Callback dari DANA Bisnis QRIS Kurtech
Route::any('/callback/dana-qris', [DanaCallbackController::class, 'handle'])->name('dana.callback');
