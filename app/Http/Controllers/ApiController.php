<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\Setting;

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
            'status' => 'pending',
            'callback_url' => $request->input('callback_url', $merchant->webhook_url),
        ]);

        $checkoutUrl = url('/pay/' . $trx->trx_id);
        $qrUrl = asset('images/qris-clean.png');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat.',
            'data' => [
                'trx_id' => $trx->trx_id,
                'order_id' => $trx->order_id,
                'service_name' => $trx->service_name,
                'amount' => (int) $amount,
                'customer_fee' => (int) $customerFee,
                'unique_code' => $uniqueCode,
                'total_amount' => (int) $totalAmount,
                'checkout_url' => $checkoutUrl,
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
}
