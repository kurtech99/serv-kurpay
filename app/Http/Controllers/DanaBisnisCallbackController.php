<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VpnTransaction;
use App\Services\VpnService;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class DanaBisnisCallbackController extends Controller
{
    /**
     * Webhook Handler for DANA Bisnis QRIS Kurtech
     * Supports POST, GET, JSON, form-data, or query parameters.
     */
    public function handle(Request $request)
    {
        Log::info('DANA Bisnis Callback Received:', $request->all());

        // 1. Secret Token Verification (Optional / Configurable)
        $secret = $request->input('secret') ?? $request->header('X-Dana-Secret') ?? $request->header('Authorization');
        $expectedSecret = Setting::get('saas_dana_secret', 'kurtech_dana_2026');
        
        $validSecrets = [$expectedSecret, 'kurtech_dana_2026', 'kurtech99'];
        if ($secret && !in_array($secret, $validSecrets) && !in_array(str_replace('Bearer ', '', $secret), $validSecrets)) {
            Log::warning('DANA Bisnis Callback Unauthorized Secret: ' . $secret);
            return response()->json(['success' => false, 'message' => 'Unauthorized secret token'], 401);
        }

        // 2. Extract Amount
        $amount = 0;
        if ($request->has('amount')) {
            $amount = (int) preg_replace('/[^0-9]/', '', (string)$request->input('amount'));
        } elseif ($request->has('nominal')) {
            $amount = (int) preg_replace('/[^0-9]/', '', (string)$request->input('nominal'));
        }

        // If amount not passed directly, try extracting from notification message text
        if ($amount <= 0) {
            $text = $request->input('message') ?? $request->input('text') ?? $request->input('content') ?? $request->getContent();
            if (!empty($text) && is_string($text)) {
                // Regex matches formats like "Rp 2.147", "Rp2.147", "sebesar 2.147", "Rp. 2.147"
                if (preg_match('/(?:Rp\.?|sebesar)\s*([0-9\.\,]+)/i', $text, $matches)) {
                    $cleaned = str_replace(['.', ','], '', $matches[1]);
                    $amount = (int) $cleaned;
                }
            }
        }

        if ($amount < 500) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal tidak valid atau di bawah batas minimal (Rp 500). Nilai terbaca: ' . $amount
            ], 400);
        }

        // 3. Find matching pending transaction
        // First match against total_amount (which includes unique code)
        $trx = VpnTransaction::where('status', 'pending_payment')
            ->where('total_amount', $amount)
            ->orderByDesc('id')
            ->first();

        // Fallback: match against base amount
        if (!$trx) {
            $trx = VpnTransaction::where('status', 'pending_payment')
                ->where('amount', $amount)
                ->orderByDesc('id')
                ->first();
        }

        if (!$trx) {
            Log::warning("DANA Bisnis Callback: No pending transaction found for amount Rp {$amount}");
            return response()->json([
                'success' => false,
                'message' => "Tidak ada transaksi pending dengan nominal Rp " . number_format($amount, 0, ',', '.'),
                'received_amount' => $amount
            ], 404);
        }

        // 4. Provision VPN Account
        $durationDays = ($trx->duration_months ?: 1) * 30;
        $provisionResult = VpnService::provision($trx, $durationDays);

        if (!$provisionResult['status']) {
            Log::error('DANA Bisnis Provisioning Failed for Trx: ' . $trx->trx_id . ' Error: ' . $provisionResult['message']);
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran terdeteksi namun provisioning VPN gagal: ' . $provisionResult['message'],
                'trx_id' => $trx->trx_id
            ], 500);
        }

        // 5. Update transaction to completed
        $trx->update([
            'status' => 'completed',
            'payment_method' => 'qris_kurtech',
            'proof_of_payment' => 'VERIFIED_DANA_BISNIS_KURTECH'
        ]);

        ActivityLog::create([
            'user_id' => $trx->user_id,
            'category' => 'VPN Remote',
            'action' => 'Pembayaran QRIS DANA Bisnis Otomatis',
            'details' => "Transaksi {$trx->trx_id} nominal Rp " . number_format($amount, 0, ',', '.') . " diverifikasi otomatis via QRIS Kurtech. Akun aktif."
        ]);

        Log::info("DANA Bisnis SUCCESS: Transaction {$trx->trx_id} activated for amount {$amount}");

        return response()->json([
            'success' => true,
            'message' => "Transaksi {$trx->trx_id} berhasil diverifikasi & Akun VPN aktif!",
            'trx_id' => $trx->trx_id,
            'amount' => $amount,
            'status' => 'completed'
        ]);
    }
}
