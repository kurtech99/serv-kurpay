<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VpnServer;
use App\Models\VpnAccount;
use App\Models\VpnTransaction;
use App\Models\HttpProxyAccount;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $user = auth()->user();

        // For Super Admin
        if ($user->role === 'superadmin') {
            $data['totalServers'] = VpnServer::count();
            $data['totalAccounts'] = VpnAccount::count();
            $data['totalTransactions'] = VpnTransaction::count();
            $data['revenue'] = VpnTransaction::where('status', 'completed')->sum('amount');
        } else {
            // For Tenant / Client
            $data['totalAccounts'] = VpnAccount::where('user_id', $userId)->count();
            $data['activeAccounts'] = VpnAccount::where('user_id', $userId)->where('status', 'active')->count();
            $data['totalHttpProxies'] = HttpProxyAccount::where('user_id', $userId)->count();
            $data['totalTransactions'] = VpnTransaction::where('user_id', $userId)->count();
            $data['totalSpent'] = VpnTransaction::where('user_id', $userId)->where('status', 'completed')->sum('amount');
            
            // Fetch VPN accounts directly for dashboard display
            $data['vpnAccounts'] = VpnAccount::where('user_id', $userId)
                ->with(['server', 'ports'])
                ->latest()
                ->get();
                
            // Fetch pending transaction if any (with smart auto-reconcile)
            $pendingTrx = VpnTransaction::where('user_id', $userId)
                ->whereIn('status', ['pending_payment', 'pending_verification'])
                ->latest()
                ->first();

            if ($pendingTrx) {
                $alreadyActive = VpnAccount::where('user_id', $userId)
                    ->where('status', 'active')
                    ->where(function($q) use ($pendingTrx) {
                        if (!empty($pendingTrx->requested_username)) {
                            $cleanReqUser = strtolower(trim($pendingTrx->requested_username));
                            $q->whereRaw('LOWER(username) = ?', [$cleanReqUser]);
                        }
                        if (!empty($pendingTrx->vpn_account_id)) {
                            $q->orWhere('id', $pendingTrx->vpn_account_id);
                        }
                    })
                    ->first();

                // Fallback: If user only has 1 active account, match directly
                if (!$alreadyActive && $data['activeAccounts'] === 1) {
                    $singleAcc = $data['vpnAccounts']->first();
                    if ($singleAcc && $singleAcc->status === 'active') {
                        $alreadyActive = $singleAcc;
                    }
                }

                if ($alreadyActive) {
                    $pendingTrx->update([
                        'status' => 'completed',
                        'vpn_account_id' => $alreadyActive->id
                    ]);
                    $pendingTrx = null;
                }
            }
            $data['pendingTrx'] = $pendingTrx;

            // Fetch recent activity logs
            $data['activityLogs'] = ActivityLog::where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', $data);
    }
}
