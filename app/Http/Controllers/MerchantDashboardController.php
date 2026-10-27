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
    private function getMerchant($merchantId = null): Merchant
    {
        $user = Auth::user();
        if ($merchantId) {
            $m = Merchant::where('user_id', $user->id)->where('id', $merchantId)->first();
            if ($m) {
                session(['active_merchant_id' => $m->id]);
                return $m;
            }
        }

        $activeId = session('active_merchant_id');
        if ($activeId) {
            $m = Merchant::where('user_id', $user->id)->where('id', $activeId)->first();
            if ($m) return $m;
        }

        $m = Merchant::where('user_id', $user->id)->first();
        if (!$m) {
            $m = Merchant::create([
                'user_id' => $user->id,
                'business_name' => $user->name . ' WiFi',
                'balance' => 0,
                'gateway_mode' => 'kurtech',
                'fee_deposit_balance' => 0,
                'status' => 'active',
            ]);
        }
        session(['active_merchant_id' => $m->id]);
        return $m;
    }

    public function merchants()
    {
        $user = Auth::user();
        $merchants = Merchant::where('user_id', $user->id)
            ->withCount(['transactions as total_paid_tx' => function ($q) {
                $q->where('status', 'paid');
            }])
            ->withSum(['transactions as total_revenue' => function ($q) {
                $q->where('status', 'paid');
            }], 'amount')
            ->latest()
            ->get();

        $activeMerchant = $this->getMerchant();

        return view('merchant.merchants', compact('merchants', 'activeMerchant'));
    }

    public function storeMerchant(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'business_name' => 'required|string|max:100',
            'gateway_mode'  => 'required|in:kurtech,custom',
            'webhook_url'   => 'nullable|url|max:255',
        ]);

        $merchant = Merchant::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'gateway_mode' => $request->gateway_mode,
            'webhook_url' => $request->webhook_url,
            'balance' => 0,
            'fee_deposit_balance' => 0,
            'status' => 'active',
        ]);

        session(['active_merchant_id' => $merchant->id]);

        return redirect()->route('merchant.merchants')->with('success', "Merchant/Router '{$merchant->business_name}' berhasil ditambahkan! Kode: {$merchant->merchant_code}");
    }

    public function switchMerchant($id)
    {
        $user = Auth::user();
        $merchant = Merchant::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        session(['active_merchant_id' => $merchant->id]);

        return redirect()->route('merchant.dashboard')->with('success', "Berhasil beralih ke Merchant: {$merchant->business_name}");
    }

    public function deleteMerchant($id)
    {
        $user = Auth::user();
        if ($user->merchants()->count() <= 1) {
            return back()->with('error', 'Anda harus memiliki minimal 1 merchant aktif.');
        }

        $merchant = Merchant::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        $name = $merchant->business_name;
        $merchant->delete();

        session()->forget('active_merchant_id');

        return redirect()->route('merchant.merchants')->with('success', "Merchant {$name} berhasil dihapus.");
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

    public function apiCredentials(Request $request)
    {
        $merchantId = $request->input('id');
        $merchant = $this->getMerchant($merchantId);
        $userMerchants = Auth::user()->merchants()->latest()->get();

        return view('merchant.api_credentials', compact('merchant', 'userMerchants'));
    }

    public function regenerateKeys(Request $request)
    {
        $merchantId = $request->input('merchant_id') ?: $request->input('id');
        $merchant = $this->getMerchant($merchantId);
        $merchant->update([
            'api_key' => 'kp_live_' . Str::random(32),
            'secret_key' => 'kp_sec_' . Str::random(32),
        ]);

        return back()->with('success', "Kredensial API baru untuk '{$merchant->business_name}' berhasil digenerate!");
    }

    public function updateWebhook(Request $request)
    {
        $request->validate([
            'webhook_url' => 'nullable|url|max:255',
        ]);

        $merchantId = $request->input('merchant_id') ?: $request->input('id');
        $merchant = $this->getMerchant($merchantId);
        $merchant->update([
            'webhook_url' => $request->webhook_url,
        ]);

        return back()->with('success', "Webhook URL untuk '{$merchant->business_name}' berhasil diperbarui!");
    }

    public function updateGatewayMode(Request $request)
    {
        $merchant = $this->getMerchant();

        $request->validate([
            'gateway_mode' => 'required|in:kurtech,custom',
            'custom_nmid' => 'nullable|string|max:50',
            'custom_qris_name' => 'nullable|string|max:100',
            'custom_qris_payload' => 'nullable|string',
            'custom_qris_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $data = [
            'gateway_mode' => $request->gateway_mode,
            'custom_nmid' => $request->custom_nmid,
            'custom_qris_name' => $request->custom_qris_name,
            'custom_qris_payload' => $request->custom_qris_payload,
        ];

        if ($request->hasFile('custom_qris_image')) {
            $path = $request->file('custom_qris_image')->store('qris_merchants', 'public');
            $data['custom_qris_image'] = $path;
        }

        $merchant->update($data);

        $msg = $request->gateway_mode === 'custom' 
            ? 'Metode gateway berhasil diubah ke: Pakai QRIS Sendiri. Pastikan saldo kuota deposit fee Anda mencukupi!' 
            : 'Metode gateway berhasil diubah ke: Sewa Gateway Kurtech (Default).';

        return back()->with('success', $msg);
    }

    public function topupDeposit(Request $request)
    {
        $merchant = $this->getMerchant();

        $request->validate([
            'amount' => 'required|numeric|min:5000|max:1000000',
        ]);

        $amount = (float) $request->amount;
        $uniqueCode = rand(111, 899);
        $totalAmount = $amount + $uniqueCode;

        // Check clash
        $attempts = 0;
        while (Transaction::where('status', 'pending')->where('total_amount', $totalAmount)->exists() && $attempts < 10) {
            $uniqueCode = rand(111, 899);
            $totalAmount = $amount + $uniqueCode;
            $attempts++;
        }

        $trx = Transaction::create([
            'merchant_id' => $merchant->id,
            'order_id' => 'DEP-' . time(),
            'service_name' => 'Top Up Saldo Kuota Deposit Fee KurPay',
            'customer_name' => $merchant->user->name ?? 'Mitra KurPay',
            'customer_phone' => $merchant->user->phone ?? '083826164767',
            'amount' => $amount,
            'customer_fee' => 0,
            'unique_code' => $uniqueCode,
            'total_amount' => $totalAmount,
            'merchant_net' => $amount,
            'gateway_mode' => 'kurtech', // Top up selalu bayar ke QRIS resmi Kurtech
            'is_deposit_topup' => true,
            'status' => 'pending',
            'callback_url' => route('merchant.dashboard'),
        ]);

        return redirect()->route('pay.checkout', $trx->trx_id)->with('info', 'Silakan scan QRIS untuk menyelesaikan Top Up Saldo Deposit Fee sebesar Rp ' . number_format($totalAmount, 0, ',', '.') . '.');
    }
}
