<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function tripay(Request $request, MikrotikService $mikrotikService)
    {
        $json = $request->getContent();
        
        $signature = hash_hmac('sha256', $json, env('TRIPAY_PRIVATE_KEY'));

        if (!hash_equals($signature, $request->header('x-callback-signature', ''))) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        $data = json_decode($json);

        if ($data->status === 'PAID') {
            $this->processPaidTransaction($data->reference, $mikrotikService);
        }

        return response()->json(['success' => true]);
    }

    public function xendit(Request $request, MikrotikService $mikrotikService)
    {
        $token = $request->header('x-callback-token');

        if ($token !== env('XENDIT_CALLBACK_TOKEN')) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }

        if ($request->status === 'PAID') {
            $this->processPaidTransaction($request->external_id, $mikrotikService);
        }

        return response()->json(['success' => true]);
    }

    public function midtrans(Request $request, MikrotikService $mikrotikService)
    {
        $serverKey = \App\Models\Setting::get('midtrans_server_key');
        
        $signatureKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($signatureKey !== $request->signature_key) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        if ($request->transaction_status === 'settlement' || $request->transaction_status === 'capture') {
            $this->processPaidTransaction($request->order_id, $mikrotikService);
        }

        return response()->json(['success' => true]);
    }

    protected function processPaidTransaction($trxId, MikrotikService $mikrotikService)
    {
        $transaction = Transaction::where('trx_id', $trxId)->with(['package', 'customer'])->first();

        if (!$transaction || $transaction->status === 'paid') {
            // Idempotent: already paid or not found
            return;
        }

        $transaction->status = 'paid';

        // Generate voucher
        $username = $mikrotikService->generateUniqueUsername();
        $password = str()->random(4); // simple password

        try {
            $mikrotikService->addHotspotUser(
                $username,
                $password,
                $transaction->package->mikrotik_profile,
                $transaction->package->uptime_limit,
                $transaction->package->bytes_limit
            );

            // Save generated credentials
            $transaction->mikrotik_username = $username;
            $transaction->mikrotik_password = $password;

            // Send WA Blast
            $client = new \GuzzleHttp\Client(['timeout' => 3.0]);
            $client->post('http://127.0.0.1:3001/send-voucher', [
                'json' => [
                    'tenant_id' => $transaction->package->user_id,
                    'phone' => $transaction->customer->phone,
                    'package_name' => $transaction->package->name,
                    'username' => $username,
                    'password' => $password
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to generate voucher or send WA for {$trxId}: " . $e->getMessage());
        }

        $transaction->save();

        // Cek sisa kuota tenant & kirim notifikasi jika hampir habis (sisa 5 atau 1)
        try {
            $tenant = $transaction->package->user;
            if ($tenant) {
                $limit = $tenant->getVoucherLimit();
                if ($limit > 0) {
                    $used = $tenant->getVouchersGeneratedThisMonth();
                    $remaining = $limit - $used;
                    
                    if ($remaining === 5 || $remaining === 1 || $remaining === 0) {
                        $client = new \GuzzleHttp\Client(['timeout' => 3.0]);
                        $client->post('http://127.0.0.1:3001/send-message', [
                            'json' => [
                                'tenant_id' => $tenant->id,
                                'phone' => $tenant->phone,
                                'message' => "⚠️ *PERINGATAN SISTEM HOTSPOT*\n\nHalo {$tenant->name}, sisa kuota voucher Anda saat ini tinggal *{$remaining} voucher*.\n\nSegera lakukan upgrade paket agar pelanggan Anda tetap bisa membeli akses WiFi secara otomatis melalui sistem.\n\nTerima kasih."
                            ]
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send limit warning WA to tenant: " . $e->getMessage());
        }
    }
}
