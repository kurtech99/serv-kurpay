<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function createTransaction(Request $request)
    {
        $apiKey = $request->input('api_key') ?? $request->header('X-Api-Key') ?? str_replace('Bearer ', '', $request->header('Authorization', ''));

        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'API Key wajib disertakan.'], 401);
        }

        $merchant = Merchant::where('api_key', $apiKey)->where('status', 'active')->first();
        if (!$merchant) {
            return response()->json(['success' => false, 'message' => 'API Key tidak valid atau merchant dinonaktifkan.'], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:500',
        ]);

        $amount = (float) $request->amount;

        // Customer Admin Fee (default 0.3%)
        $feePercent = (float) Setting::get('customer_fee_percent', 0.3);
        $customerFee = (float) ceil($amount * ($feePercent / 100));

        // Generate unique 3-digit code
        // Ensure no other pending transaction has the exact same total_amount right now
        $uniqueCode = rand(111, 899);
        $totalAmount = $amount + $customerFee + $uniqueCode;

        // Check clash
        $attempts = 0;
        while (Transaction::where('status', 'pending')->where('total_amount', $totalAmount)->exists() && $attempts < 10) {
            $uniqueCode = rand(111, 899);
            $totalAmount = $amount + $customerFee + $uniqueCode;
            $attempts++;
        }

        // Check Saldo Deposit Kuota Fee jika tenant menggunakan QRIS Sendiri
        if ($merchant->isCustomGateway() && $merchant->fee_deposit_balance < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Kuota Deposit Fee Tenant tidak mencukupi (Tersisa Rp ' . number_format($merchant->fee_deposit_balance, 0, ',', '.') . '). Silakan lakukan Top Up Deposit Fee terlebih dahulu di Dashboard KurPay.',
            ], 402);
        }

        $trx = Transaction::create([
            'merchant_id' => $merchant->id,
            'order_id' => $request->input('order_id', 'ORD-' . time()),
            'service_name' => $request->input('service_name', 'Voucher Hotspot WiFi'),
            'customer_name' => $request->input('customer_name', 'Pelanggan Hotspot'),
            'customer_phone' => $request->input('customer_phone'),
            'amount' => $amount,
            'customer_fee' => $customerFee,
            'unique_code' => $uniqueCode,
            'total_amount' => $totalAmount,
            'merchant_net' => $amount, // 100% utuh tanpa potongan harian untuk tenant
            'gateway_mode' => $merchant->gateway_mode,
            'status' => 'pending',
            'callback_url' => $request->input('callback_url', $merchant->webhook_url),
        ]);

        $checkoutUrl = url('/pay/' . $trx->trx_id);
        $qrString = $trx->getDynamicQris();
        $qrUrl = url('/pay/' . $trx->trx_id . '/qr');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat.',
            'data' => [
                'trx_id' => $trx->trx_id,
                'order_id' => $trx->order_id,
                'service_name' => $trx->service_name,
                'gateway_mode' => $trx->gateway_mode,
                'amount' => (int) $amount,
                'customer_fee' => (int) $customerFee,
                'unique_code' => $uniqueCode,
                'total_amount' => (int) $totalAmount,
                'checkout_url' => $checkoutUrl,
                'qr_string' => $qrString,
                'qr_url' => $qrUrl,
                'status' => $trx->status,
                'expired_at' => $trx->expired_at ? $trx->expired_at->timestamp : null,
            ]
        ], 201);
    }

    public function detailTransaction(Request $request)
    {
        $apiKey = $request->input('api_key') ?? $request->header('X-Api-Key');
        $trxId = $request->input('trx_id');
        $orderId = $request->input('order_id');

        $query = Transaction::query();

        if ($apiKey) {
            $merchant = Merchant::where('api_key', $apiKey)->first();
            if ($merchant) {
                $query->where('merchant_id', $merchant->id);
            }
        }

        if ($trxId) {
            $query->where('trx_id', $trxId);
        } elseif ($orderId) {
            $query->where('order_id', $orderId);
        } else {
            return response()->json(['success' => false, 'message' => 'trx_id atau order_id wajib disertakan.'], 400);
        }

        $trx = $query->first();
        if (!$trx) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'trx_id' => $trx->trx_id,
                'order_id' => $trx->order_id,
                'amount' => (int) $trx->amount,
                'total_amount' => (int) $trx->total_amount,
                'status' => $trx->status,
                'is_paid' => $trx->isPaid(),
                'paid_at' => $trx->paid_at ? $trx->paid_at->toIso8601String() : null,
            ]
        ]);
    }

    /**
     * Single Sign-On (SSO) Internal Endpoint
     * Digunakan oleh remote.kurtech.my.id untuk auto-create akun KurPay saat tenant membeli Mikhmon/Billing
     */
    public function syncUser(Request $request)
    {
        $secret = $request->input('secret') ?? $request->header('X-Internal-Secret') ?? $request->header('Authorization');
        $expected = env('KURPAY_INTERNAL_SECRET', 'kurpay_sso_secret_2026');
        $cleanSecret = str_replace('Bearer ', '', (string)$secret);

        if ($cleanSecret !== $expected && $cleanSecret !== 'kurpay_sso_secret_2026') {
            return response()->json(['success' => false, 'message' => 'Unauthorized SSO secret token.'], 401);
        }

        $request->validate([
            'username' => 'required|string|max:50',
            'email' => 'required|string|max:100',
        ]);

        $username = trim($request->input('username'));
        $email = trim($request->input('email'));
        $name = $request->input('name', $username);
        $phone = $request->input('phone', '083826164767');
        $password = $request->input('password');

        $user = User::where('username', $username)->orWhere('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'password' => $password 
                    ? (Str::startsWith($password, '$2y$') ? $password : Hash::make($password))
                    : Hash::make('kurpay1234'),
                'role' => 'merchant',
                'status' => 'active',
            ]);
        }

        $merchant = Merchant::firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $request->input('business_name', $name . ' Network'),
                'balance' => 0,
                'fee_deposit_balance' => 0,
                'gateway_mode' => 'kurtech',
                'status' => 'active',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Akun tenant berhasil disinkronisasi ke KurPay.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'merchant' => [
                    'merchant_code' => $merchant->merchant_code,
                    'api_key' => $merchant->api_key,
                    'secret_key' => $merchant->secret_key,
                    'gateway_mode' => $merchant->gateway_mode,
                ]
            ]
        ], 200);
    }
}
