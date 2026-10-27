<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ticket;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalTenants = User::where('role', 'tenant')->count();
        $totalPendapatan = \App\Models\VpnTransaction::where('status', 'completed')->sum('amount');
        
        $tenants = User::where('role', 'tenant')->orderBy('created_at', 'desc')->take(5)->get();

        // Chart Data: Pendaftaran 7 hari terakhir & Pendapatan 7 hari terakhir
        $chartData = \Illuminate\Support\Facades\Cache::remember('superadmin_vpn_chart_data', 300, function() {
            $registrationData = [];
            $revenueVPNData = [];
            $registrationLabels = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');
                
                $regCount = User::where('role', 'tenant')->whereDate('created_at', $date)->count();
                $revVPN = \App\Models\VpnTransaction::where('status', 'completed')->whereDate('created_at', $date)->sum('amount');
                
                $registrationLabels[] = \Carbon\Carbon::now()->subDays($i)->format('d M');
                $registrationData[] = $regCount;
                $revenueVPNData[] = $revVPN;
            }

            return compact('registrationData', 'revenueVPNData', 'registrationLabels');
        });

        $registrationData = $chartData['registrationData'];
        $revenueVPNData = $chartData['revenueVPNData'];
        $registrationLabels = $chartData['registrationLabels'];

        return view('superadmin.dashboard', compact(
            'totalTenants', 
            'totalPendapatan',
            'tenants', 
            'registrationData',
            'revenueVPNData',
            'registrationLabels'
        ));
    }

    public function serverStats()
    {
        $servers = \App\Models\VpnServer::where('is_active', 1)->get();
        $stats = [];
        
        foreach ($servers as $server) {
            $serverStat = \App\Services\MikrotikService::getServerStats($server);
            $stats[] = [
                'id' => $server->id,
                'name' => $server->name,
                'host' => $server->host,
                'status' => $serverStat['status'],
                'ping' => $serverStat['ping'] ?? '-',
                'cpu' => $serverStat['cpu'] ?? '-',
                'memory' => $serverStat['memory'] ?? '-',
                'uptime' => $serverStat['uptime'] ?? '-'
            ];
        }

        return response()->json(['servers' => $stats]);
    }

    public function tenants()
    {
        $tenants = User::where('role', 'tenant')->with(['vpnAccounts.ports'])->orderBy('created_at', 'desc')->get();
        return view('superadmin.tenants', compact('tenants'));
    }

    public function resetPassword(Request $request, $id)
    {
        $tenant = User::where('role', 'tenant')->findOrFail($id);
        \Illuminate\Support\Facades\DB::table('users')->where('id', $tenant->id)->update([
            'password' => \Illuminate\Support\Facades\Hash::make('password123')
        ]);

        return back()->with('success', 'Password untuk ' . $tenant->name . ' berhasil direset menjadi "password123".');
    }

    public function deleteTenant($id)
    {
        $tenant = User::where('role', 'tenant')->findOrFail($id);
        
        // Delete related routers (if relationships are configured with cascade, this might be redundant but safe)
        $tenant->routers()->delete();
        
        $tenant->delete();

        return back()->with('success', 'Akun Tenant ' . $tenant->name . ' berhasil dihapus beserta data router-nya.');
    }

    public function bulkDeleteTenants(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        $tenants = User::where('role', 'tenant')->whereIn('id', $request->ids)->get();
        foreach ($tenants as $tenant) {
            $tenant->routers()->delete();
            $tenant->delete();
        }

        return back()->with('success', count($request->ids) . ' Akun Tenant berhasil dihapus.');
    }

    public function subscriptions()
    {
        $transactions = \App\Models\SubscriptionTransaction::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.subscriptions.index', compact('transactions'));
    }

    public function approveSubscription($id)
    {
        $transaction = \App\Models\SubscriptionTransaction::findOrFail($id);

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

        return back()->with('success', 'Pembayaran berhasil disetujui, akun Tenant telah diperpanjang.');
    }

    public function clearSubscriptions($period)
    {
        if ($period === 'month') {
            \App\Models\SubscriptionTransaction::whereMonth('created_at', now()->month)
                                               ->whereYear('created_at', now()->year)
                                               ->delete();
            $msg = 'Riwayat tagihan bulan ini berhasil dihapus.';
        } elseif ($period === 'all') {
            \App\Models\SubscriptionTransaction::truncate();
            $msg = 'Seluruh riwayat tagihan berhasil dihapus.';
        } else {
            return back()->with('error', 'Periode tidak valid.');
        }

        return back()->with('success', $msg);
    }

    public function bulkDeleteSubscriptions(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:subscription_transactions,id'
        ]);

        \App\Models\SubscriptionTransaction::whereIn('id', $request->ids)->delete();

        return back()->with('success', count($request->ids) . ' Riwayat tagihan berhasil dihapus.');
    }
}
