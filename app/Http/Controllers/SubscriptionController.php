<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionTransaction;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SubscriptionController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'package_type' => 'required|in:standar,pro,unlimited',
            'payment_method' => 'required|in:midtrans,manual_dana,manual_bank',
        ]);

        $user = Auth::user();
        if ($request->package_type === 'unlimited') {
            $amount = 149000;
        } elseif ($request->package_type === 'pro') {
            $amount = 59000;
        } else {
            $amount = 29000;
        }
        $trxId = 'SAAS-' . time() . '-' . strtoupper(Str::random(4));

        $transaction = SubscriptionTransaction::create([
            'user_id' => $user->id,
            'trx_id' => $trxId,
            'package_type' => $request->package_type,
            'amount' => $amount,
            'status' => 'pending',
            // Kita juga bisa menyimpan payment method ke DB kalau ada fieldnya, tapi saat ini tidak perlu karena di-handle oleh parameter request untuk manual.
        ]);

        if (in_array($request->payment_method, ['manual_dana', 'manual_bank'])) {
            return redirect()->route('subscription.invoice', ['trx_id' => $trxId, 'method' => $request->payment_method]);
        }

        // MIDTRANS INTEGRATION
        $serverKey = Setting::get('saas_midtrans_server_key', '');
        $isProduction = Setting::get('saas_midtrans_is_production', '0');
        
        if (empty($serverKey)) {
            return back()->with('error', 'Kredensial Midtrans belum diatur oleh SuperAdmin.');
        }

        $apiUrl = $isProduction == '1' 
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id' => $trxId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => $request->package_type,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => 'Upgrade Paket ' . strtoupper($request->package_type)
                ]
            ]
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->post($apiUrl, $payload);

        if ($response->successful()) {
            $snapUrl = $response->json()['redirect_url'];
            return redirect($snapUrl);
        }

        return back()->with('error', 'Gagal memanggil API Midtrans: ' . $response->body());
    }

    public function webhook(Request $request)
    {
        $serverKey = Setting::get('saas_midtrans_server_key', '');
        
        $orderId = $request->order_id;
        $statusCode = $request->status_code;
        $grossAmount = $request->gross_amount;
        $serverKey = $serverKey;
        $signatureKey = hash("sha512", $orderId . $statusCode . $grossAmount . $serverKey);
        
        if ($signatureKey != $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }
        
        $transaction = SubscriptionTransaction::where('trx_id', $orderId)->first();
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        
        if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
            if ($transaction->status !== 'paid') {
                $transaction->update(['status' => 'paid']);
                
                $user = $transaction->user;
                $expiresAt = $user->package_expires_at;
                if ($expiresAt && $expiresAt->isFuture()) {
                    $newExpiresAt = $expiresAt->addDays(30);
                } else {
                    $newExpiresAt = now()->addDays(30);
                }

                $user->update([
                    'package_type' => $transaction->package_type,
                    'package_expires_at' => $newExpiresAt
                ]);
            }
        } elseif ($request->transaction_status == 'expire' || $request->transaction_status == 'cancel') {
            $transaction->update(['status' => 'failed']);
        }
        
        return response()->json(['message' => 'OK']);
    }

    public function invoice($trx_id)
    {
        $transaction = SubscriptionTransaction::where('trx_id', $trx_id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();
                        
        if ($transaction->status === 'paid') {
            return redirect()->route('dashboard')->with('success', 'Tagihan sudah lunas.');
        }

        return view('subscription.invoice', compact('transaction'));
    }

    public function uploadProof(Request $request, $trx_id)
    {
        $request->validate([
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $transaction = SubscriptionTransaction::where('trx_id', $trx_id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('proofs', 'public');
            
            $transaction->update([
                'proof' => $path,
                'status' => 'pending_verification'
            ]);

            return redirect()->route('dashboard')->with('success', 'Bukti transfer berhasil diunggah. Silakan tunggu konfirmasi Admin.');
        }

        return back()->with('error', 'Gagal mengunggah bukti.');
    }

    public function pay($trx_id)
    {
        return redirect()->route('dashboard');
    }
}
