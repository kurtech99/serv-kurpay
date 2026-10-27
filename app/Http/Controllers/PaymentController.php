<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Setting;

class PaymentController extends Controller
{
    public function checkout($trxId)
    {
        $trx = Transaction::with('merchant')->where('trx_id', $trxId)->firstOrFail();

        $merchant = $trx->merchant;
        if ($merchant && $merchant->isCustomGateway() && !empty($merchant->custom_qris_image)) {
            $qrisImage = asset('storage/' . $merchant->custom_qris_image);
            $merchantName = $merchant->custom_qris_name ?: $merchant->business_name;
            $nmid = $merchant->custom_nmid ?: '-';
            $isCustom = true;
        } else {
            $qrisImage = asset('images/qris-clean.png');
            $merchantName = Setting::get('merchant_qris_name', 'kurtech');
            $nmid = Setting::get('merchant_nmid', 'ID1025414745014');
            $isCustom = false;
        }

        return view('pay.checkout', compact('trx', 'merchant', 'qrisImage', 'merchantName', 'nmid', 'isCustom'));
    }

    public function checkStatus($trxId)
    {
        $trx = Transaction::where('trx_id', $trxId)->first();
        if (!$trx) {
            return response()->json(['status' => 'not_found', 'is_paid' => false], 404);
        }

        return response()->json([
            'trx_id' => $trx->trx_id,
            'status' => $trx->status,
            'is_paid' => $trx->isPaid(),
            'callback_url' => $trx->callback_url,
        ]);
    }
}
