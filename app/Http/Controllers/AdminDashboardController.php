<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Setting;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalMerchants = Merchant::count();
        $totalVolume = Transaction::where('status', 'paid')->sum('amount');
        $totalMerchantBalance = Merchant::sum('balance');
        $totalKurtechProfit = Withdrawal::where('status', 'completed')->sum('admin_fee') + Transaction::where('status', 'paid')->sum('customer_fee');

        $pendingWithdrawals = Withdrawal::where('status', 'pending')
            ->with('merchant.user')
            ->latest()
            ->take(10)
            ->get();

        $recentTransactions = Transaction::with('merchant')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalMerchants', 'totalVolume', 'totalMerchantBalance', 'totalKurtechProfit',
            'pendingWithdrawals', 'recentTransactions'
        ));
    }

    public function withdrawals(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = Withdrawal::with('merchant.user')->latest();
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $withdrawals = $query->paginate(20);
        return view('admin.withdrawals', compact('withdrawals', 'status'));
    }

    public function approveWithdrawal(Request $request, $id)
    {
        $wd = Withdrawal::findOrFail($id);

        if ($wd->status === 'completed') {
            return back()->with('info', 'Penarikan ini sudah selesai diproses sebelumnya.');
        }

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $filename = 'proof_' . $wd->withdrawal_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/receipts'), $filename);
            $proofPath = 'uploads/receipts/' . $filename;
        }

        $wd->update([
            'status' => 'completed',
            'proof_receipt' => $proofPath ?? $wd->proof_receipt,
            'notes' => $request->input('notes', 'Transfer selesai diproses oleh Admin.'),
            'processed_at' => now(),
        ]);

        return back()->with('success', "Penarikan #{$wd->withdrawal_id} berhasil ditandai LUNAS / SELESAI!");
    }

    public function rejectWithdrawal(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $wd = Withdrawal::findOrFail($id);

        if ($wd->status !== 'pending') {
            return back()->with('error', 'Hanya penarikan pending yang dapat ditolak.');
        }

        // Kembalikan saldo utuh ke merchant
        $wd->merchant->addBalance($wd->amount_requested);

        $wd->update([
            'status' => 'rejected',
            'notes' => 'Ditolak: ' . $request->reason,
            'processed_at' => now(),
        ]);

        return back()->with('success', "Penarikan #{$wd->withdrawal_id} ditolak dan saldo Rp " . number_format($wd->amount_requested, 0, ',', '.') . " telah dikembalikan ke merchant.");
    }

    public function merchants()
    {
        $merchants = Merchant::with('user')->withCount('transactions')->latest()->paginate(20);
        return view('admin.merchants', compact('merchants'));
    }

    public function settings()
    {
        $settings = [
            'customer_fee_percent' => Setting::get('customer_fee_percent', '0.3'),
            'withdraw_min' => Setting::get('withdraw_min', '50000'),
            'withdraw_max' => Setting::get('withdraw_max', '500000'),
            'withdraw_fee' => Setting::get('withdraw_fee', '10000'),
            'withdraw_open_time' => Setting::get('withdraw_open_time', '08:00'),
            'withdraw_close_time' => Setting::get('withdraw_close_time', '14:00'),
            'dana_secret' => Setting::get('dana_secret', 'kurtech_dana_2026'),
            'admin_whatsapp' => Setting::get('admin_whatsapp', '6283826164767'),
            'merchant_qris_name' => Setting::get('merchant_qris_name', 'kurtech'),
            'merchant_nmid' => Setting::get('merchant_nmid', 'ID1025414745014'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $keys = [
            'customer_fee_percent',
            'withdraw_min', 'withdraw_max', 'withdraw_fee',
            'withdraw_open_time', 'withdraw_close_time',
            'dana_secret', 'admin_whatsapp',
            'merchant_qris_name', 'merchant_nmid'
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        return back()->with('success', 'Pengaturan gateway berhasil disimpan!');
    }
}
