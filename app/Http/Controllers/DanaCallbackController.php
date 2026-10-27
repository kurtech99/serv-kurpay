<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DanaCallbackController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Kurtech Pay - DANA Callback Received:', $request->all());

        // 1. Secret Verification
        $secret = $request->input('secret') ?? $request->header('X-Dana-Secret') ?? $request->header('Authorization');
        $expectedSecret = Setting::get('dana_secret', 'kurtech_dana_2026');

        $validSecrets = [$expectedSecret, 'kurtech_dana_2026', 'kurtech99'];
        if ($secret && !in_array($secret, $validSecrets) && !in_array(str_replace('Bearer ', '', $secret), $validSecrets)) {
            Log::warning('Kurtech Pay - Unauthorized Secret: ' . $secret);
            return response()->json(['success' => false, 'message' => 'Unauthorized secret token.'], 401);
        }

        // 2. Extract Amount
        $amount = 0;
        if ($request->has('amount')) {
            $amount = (int) preg_replace('/[^0-9]/', '', (string)$request->input('amount'));
        } elseif ($request->has('nominal')) {
            $amount = (int) preg_replace('/[^0-9]/', '', (string)$request->input('nominal'));
        }

        // Regex fallback from notification text
        if ($amount <= 0) {
            $text = $request->input('message') ?? $request->input('text') ?? $request->getContent();
            if (!empty($text) && is_string($text)) {
                if (preg_match('/(?:Rp\.?|sebesar)\s*([0-9\.\,]+)/i', $text, $matches)) {
                    $cleaned = str_replace(['.', ','], '', $matches[1]);
                    $amount = (int) $cleaned;
                }
            }
        }

        if ($amount < 500) {
            return response()->json(['success' => false, 'message' => 'Nominal di bawah batas minimal (Rp 500). Terbaca: ' . $amount], 400);
        }

        // 3. Find matching pending transaction
        $trx = Transaction::where('status', 'pending')
            ->where('total_amount', $amount)
            ->orderByDesc('id')
            ->first();

        if (!$trx) {
            $trx = Transaction::where('status', 'pending')
                ->where('amount', $amount)
                ->orderByDesc('id')
                ->first();
        }

        if (!$trx) {
            Log::warning("Kurtech Pay - No pending transaction found for Rp {$amount}");
            return response()->json([
                'success' => false,
                'message' => "Tidak ada transaksi pending dengan nominal Rp " . number_format($amount, 0, ',', '.'),
                'received_amount' => $amount
            ], 404);
        }

        // 4. Update transaction status
        $trx->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $merchant = $trx->merchant;

        // 5. Handling berdasarkan Jenis Transaksi & Gateway Mode
        if ($trx->is_deposit_topup) {
            // A. Top Up Saldo Kuota Deposit Fee Tenant
            $merchant->addFeeDeposit($trx->amount);
            Log::info("Kurtech Pay - Top Up Deposit Kuota Fee Berhasil: Merchant {$merchant->merchant_code} +Rp {$trx->amount}. Sisa Deposit: Rp {$merchant->fee_deposit_balance}");
        } elseif ($trx->gateway_mode === 'custom') {
            // B. Mode 2: Pakai QRIS Tenant Sendiri
            // Uang 100% sudah masuk langsung ke DANA tenant. Kurtech potong fee 0.3% dari Saldo Deposit Kuota Fee tenant.
            $feeToDeduct = (float) ($trx->customer_fee > 0 ? $trx->customer_fee : ceil($trx->amount * 0.003));
            $merchant->deductFeeDeposit($feeToDeduct);
            $trx->update(['deposit_fee_deducted' => $feeToDeduct]);
            Log::info("Kurtech Pay - QRIS Tenant Sendiri: Potong fee 0.3% (Rp {$feeToDeduct}) dari Saldo Deposit Kuota Fee Merchant {$merchant->merchant_code}. Sisa Deposit: Rp {$merchant->fee_deposit_balance}");
        } else {
            // C. Mode 1: Sewa Gateway Kurtech (Default)
            // Uang masuk ke DANA Kurtech, dicatat di dompet KurPay tenant 100% utuh tanpa potongan voucher.
            $merchant->addBalance($trx->amount);
            Log::info("Kurtech Pay - Gateway Kurtech: Trx {$trx->trx_id} Saldo Dompet Merchant +Rp {$trx->amount}");
        }

        // 6. Forward Webhook to Merchant's Mikhmon / Billing Callback URL (Hanya untuk transaksi voucher/billing, bukan deposit topup)
        if (!$trx->is_deposit_topup && !empty($trx->callback_url)) {
            try {
                $payload = [
                    'event' => 'payment.success',
                    'trx_id' => $trx->trx_id,
                    'order_id' => $trx->order_id,
                    'amount' => (int) $trx->amount,
                    'total_amount' => (int) $trx->total_amount,
                    'status' => 'PAID',
                    'paid_at' => $trx->paid_at->toIso8601String(),
                ];

                $response = Http::timeout(6)->post($trx->callback_url, $payload);
                $trx->update([
                    'callback_status' => $response->successful() ? 'sent' : 'failed',
                    'callback_response' => substr($response->body(), 0, 500),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Kurtech Pay - Webhook forwarding failed to {$trx->callback_url}: " . $e->getMessage());
                $trx->update([
                    'callback_status' => 'failed',
                    'callback_response' => 'Error: ' . $e->getMessage(),
                ]);
            }
        }

        // 7. Auto-Withdrawal Check (Hanya untuk Mode Gateway Kurtech)
        if (!$trx->is_deposit_topup && $trx->gateway_mode === 'kurtech' && $merchant->auto_withdraw_enabled && !empty($merchant->bank_account)) {
            $target = $merchant->auto_withdraw_target ?: 50000;
            $adminFee = (int) Setting::get('withdraw_fee', 10000);
            $isWeekend = in_array(date('N'), [6, 7]);

            if ($merchant->balance >= $target && (!$isWeekend || $merchant->auto_withdraw_weekend)) {
                // Auto create withdrawal for the eligible target or max 500k
                $withdrawAmount = min($merchant->balance, 500000);
                if ($withdrawAmount >= 50000) {
                    $netTransfer = $withdrawAmount - $adminFee;
                    $merchant->deductBalance($withdrawAmount);

                    Withdrawal::create([
                        'merchant_id' => $merchant->id,
                        'amount_requested' => $withdrawAmount,
                        'admin_fee' => $adminFee,
                        'amount_transferred' => $netTransfer,
                        'bank_target' => $merchant->bank_name,
                        'account_target' => $merchant->bank_account,
                        'holder_target' => $merchant->bank_holder,
                        'status' => 'pending',
                        'is_auto' => true,
                        'notes' => 'Penarikan otomatis sistem (Ambang Batas / Weekend)',
                    ]);
                    Log::info("Kurtech Pay - Auto withdrawal triggered for Merchant {$merchant->merchant_code}: Rp {$withdrawAmount}");
                }
            }
        }

        Log::info("Kurtech Pay - SUCCESS: Trx {$trx->trx_id} marked as PAID. Saldo Merchant +Rp {$trx->amount}");

        return response()->json([
            'success' => true,
            'message' => "Transaksi {$trx->trx_id} berhasil diverifikasi & Saldo merchant bertambah Rp " . number_format($trx->amount, 0, ',', '.'),
            'trx_id' => $trx->trx_id,
            'merchant' => $merchant->business_name,
            'amount' => $amount,
            'status' => 'paid',
        ]);
    }
}
