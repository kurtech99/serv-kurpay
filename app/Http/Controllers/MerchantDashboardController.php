<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Setting;
use Illuminate\Support\Str;

class MerchantDashboardController extends Controller
{
    private function getMerchant(): Merchant
    {
        $user = Auth::user();
        if (!$user->merchant) {
            return Merchant::create([
                'user_id' => $user->id,
                'business_name' => $user->name . ' WiFi',
                'balance' => 0,
            ]);
        }
        return $user->merchant;
    }

    public function index()
    {
        $merchant = $this->getMerchant();

        $todayRevenue = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'paid')
            ->whereDate('paid_at', today())
            ->sum('amount');

        $monthRevenue = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $totalTransactions = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'paid')
            ->count();

        $recentTransactions = Transaction::where('merchant_id', $merchant->id)
            ->latest()
            ->take(8)
            ->get();

        return view('merchant.dashboard', compact(
            'merchant', 'todayRevenue', 'monthRevenue', 'totalTransactions', 'recentTransactions'
        ));
    }

    public function withdrawal()
    {
        $merchant = $this->getMerchant();

        $withdrawals = Withdrawal::where('merchant_id', $merchant->id)
            ->latest()
            ->paginate(15);

        $minWithdraw = (int) Setting::get('withdraw_min', 50000);
        $maxWithdraw = (int) Setting::get('withdraw_max', 500000);
        $adminFee = (int) Setting::get('withdraw_fee', 10000);
        $openTime = Setting::get('withdraw_open_time', '08:00');
        $closeTime = Setting::get('withdraw_close_time', '14:00');

        // Check operational window (08:00 - 14:00 WIB)
        $nowTime = date('H:i');
        $isOpenNow = ($nowTime >= $openTime && $nowTime <= $closeTime);

        return view('merchant.withdrawal', compact(
            'merchant', 'withdrawals', 'minWithdraw', 'maxWithdraw', 'adminFee',
            'openTime', 'closeTime', 'isOpenNow'
        ));
    }

    public function submitWithdrawal(Request $request)
    {
        $merchant = $this->getMerchant();
        $minWithdraw = (int) Setting::get('withdraw_min', 50000);
        $maxWithdraw = (int) Setting::get('withdraw_max', 500000);
        $adminFee = (int) Setting::get('withdraw_fee', 10000);

        if (empty($merchant->bank_account) || empty($merchant->bank_name)) {
            return back()->with('error', 'Silakan lengkapi rekening pencairan Anda terlebih dahulu.');
        }

        $request->validate([
            'amount' => "required|numeric|min:{$minWithdraw}|max:{$maxWithdraw}",
        ]);

        $amount = (float) $request->amount;

        if ($merchant->balance < $amount) {
            return back()->with('error', "Saldo Anda tidak mencukupi. Saldo tersedia: Rp " . number_format($merchant->balance, 0, ',', '.'));
        }

        $netTransfer = $amount - $adminFee;
        if ($netTransfer <= 0) {
            return back()->with('error', 'Nominal penarikan harus lebih besar dari biaya admin.');
        }

        // Deduct merchant balance
        $merchant->deductBalance($amount);

        // Create Withdrawal Request
        $wd = Withdrawal::create([
            'merchant_id' => $merchant->id,
            'amount_requested' => $amount,
            'admin_fee' => $adminFee,
            'amount_transferred' => $netTransfer,
            'bank_target' => $merchant->bank_name,
            'account_target' => $merchant->bank_account,
            'holder_target' => $merchant->bank_holder,
            'status' => 'pending',
            'is_auto' => false,
        ]);

        return redirect()->route('merchant.withdrawal')->with('success', "Permintaan penarikan Rp " . number_format($amount, 0, ',', '.') . " berhasil diajukan! Admin akan memproses transfer sebesar Rp " . number_format($netTransfer, 0, ',', '.') . ".");
    }

    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:50',
            'bank_account' => 'required|string|max:50',
            'bank_holder' => 'required|string|max:100',
        ]);

        $merchant = $this->getMerchant();
        $merchant->update([
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'bank_holder' => $request->bank_holder,
        ]);

        return back()->with('success', 'Rekening pencairan berhasil diperbarui!');
    }

    public function updateAutoWithdraw(Request $request)
    {
        $merchant = $this->getMerchant();
        $merchant->update([
            'auto_withdraw_enabled' => $request->boolean('auto_withdraw_enabled'),
            'auto_withdraw_target' => (float) $request->input('auto_withdraw_target', 50000),
            'auto_withdraw_weekend' => $request->boolean('auto_withdraw_weekend'),
        ]);

        return back()->with('success', 'Pengaturan penarikan otomatis berhasil disimpan!');
    }

    public function apiCredentials()
    {
        $merchant = $this->getMerchant();
        return view('merchant.api_credentials', compact('merchant'));
    }

    public function regenerateKeys()
    {
        $merchant = $this->getMerchant();
        $merchant->update([
            'api_key' => 'kp_live_' . Str::random(32),
            'secret_key' => 'kp_sec_' . Str::random(32),
        ]);

        return back()->with('success', 'Kredensial API baru berhasil digenerate!');
    }

    public function updateWebhook(Request $request)
    {
        $request->validate([
            'webhook_url' => 'nullable|url|max:255',
        ]);

        $merchant = $this->getMerchant();
        $merchant->update([
            'webhook_url' => $request->webhook_url,
        ]);

        return back()->with('success', 'Webhook URL berhasil diperbarui!');
    }
}
