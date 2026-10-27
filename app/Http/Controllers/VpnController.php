<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VpnAccount;
use App\Models\VpnTransaction;
use App\Models\VpnPort;
use App\Models\VpnServer;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use App\Services\VpnService;
use App\Services\TripayService;

class VpnController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $accounts = VpnAccount::where('user_id', $user->id)
            ->with(['server', 'ports'])
            ->latest()
            ->get();

        // Check if any free trial account is expired and block it
        $server = VpnServer::first();
        foreach ($accounts as $acc) {
            try {
                if ($acc->status === 'active' && $acc->expired_at && $acc->expired_at->isPast()) {
                    $acc->update(['status' => 'expired']);
                    if ($server) {
                        MikrotikService::disableVpnAccount($server, $acc->username);
                    }
                }
            } catch (\Exception $e) {
                // Ignore API timeout
            }
        }
            
        // Fetch pending transactions with auto-reconciliation
        $pendingTrx = VpnTransaction::where('user_id', $user->id)
            ->whereIn('status', ['pending_payment', 'pending_verification'])
            ->latest()
            ->first();

        if ($pendingTrx) {
            $alreadyActive = VpnAccount::where('user_id', $user->id)
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
            if (!$alreadyActive && $accounts->where('status', 'active')->count() === 1) {
                $singleAcc = $accounts->where('status', 'active')->first();
                if ($singleAcc) {
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

        return view('vpn.index', compact('accounts', 'pendingTrx'));
    }

    public function create()
    {
        $hasUsedTrial = (bool) (auth()->user()->has_used_trial || VpnTransaction::where('user_id', auth()->id())->where('amount', 0)->exists());
        return view('vpn.create', compact('hasUsedTrial'));
    }

    public function store(Request $request)
    {
        $pkg = $request->package ?: $request->package_type;
        $request->merge(['package' => $pkg]);

        $request->validate([
            'package' => 'required|in:1,4,free,mikhmon_lite,mikhmon_pro,mikhmon_cloud,mikhmon_wa_pro,mikhmon_enterprise,mikhmon_acs',
            'duration' => 'nullable|in:1,3,6,12',
            'username' => 'required|string|alpha_dash|unique:vpn_accounts,username',
            'password' => 'required|string|min:4',
            'port_1' => 'nullable|integer',
            'port_2' => 'nullable|integer',
            'port_3' => 'nullable|integer',
            'port_4' => 'nullable|integer',
        ]);

        $duration = (int) ($request->duration ?: 1);

        $portsArray = [];
        if ($request->filled('port_1')) {
            $portsArray[] = (int) $request->port_1;
        }
        
        if (in_array($pkg, ['4', 'free', 'mikhmon_lite', 'mikhmon_pro', 'mikhmon_cloud', 'mikhmon_wa_pro', 'mikhmon_enterprise', 'mikhmon_acs'])) {
            if ($request->filled('port_2')) {
                $portsArray[] = (int) $request->port_2;
            }
            if ($request->filled('port_3')) {
                $portsArray[] = (int) $request->port_3;
            }
            if (in_array($pkg, ['4', 'free', 'mikhmon_lite', 'mikhmon_pro', 'mikhmon_cloud', 'mikhmon_wa_pro', 'mikhmon_enterprise', 'mikhmon_acs']) && $request->filled('port_4')) {
                $portsArray[] = (int) $request->port_4;
            }
        }
        
        // Determine price and logic
        if ($pkg === 'free') {
            $hasTrial = (bool) (auth()->user()->has_used_trial || VpnTransaction::where('user_id', auth()->id())->where('amount', 0)->exists());
                
            if ($hasTrial) {
                return back()->withErrors(['package' => 'Akun Anda sudah pernah mengambil paket Free Trial VPN 1 Minggu. Silakan pilih paket berbayar.'])->withInput();
            }

            $duration = 0;
            $allowedCount = 4;
            $amount = 0;
            $trxType = 'Free Trial 1 Minggu (Full 4 Port + Mikhmon Online)';
        } else if ($pkg === '1') {
            $amount = 2000 * $duration;
            $allowedCount = 1;
            $trxType = "Standar VPN (1 Port) - " . ($duration === 12 ? '1 Tahun' : "{$duration} Bulan");
        } else if ($pkg === '4') {
            $amount = ($duration === 12) ? 96000 : (8000 * $duration);
            $allowedCount = 4;
            $trxType = "Pro VPN (4 Port) - " . ($duration === 12 ? '1 Tahun' : "{$duration} Bulan");
        } else if ($pkg === 'mikhmon_lite') {
            $amount = ($duration === 12) ? 150000 : (15000 * $duration);
            $allowedCount = 4;
            $trxType = "VPN + Mikhmon Basic (Hotspot + PPPoE) - " . ($duration === 12 ? '1 Tahun' : "{$duration} Bulan");
        } else if (in_array($pkg, ['mikhmon_pro', 'mikhmon_cloud', 'mikhmon_wa_pro'])) {
            $amount = ($duration === 12) ? 500000 : (45000 * $duration);
            $allowedCount = 4;
            $trxType = "VPN + Billing Kurtech Pro - " . ($duration === 12 ? '1 Tahun' : "{$duration} Bulan");
        } else if (in_array($pkg, ['mikhmon_enterprise', 'mikhmon_acs'])) {
            $amount = ($duration === 12) ? 1000000 : (100000 * $duration);
            $allowedCount = 4;
            $trxType = "VPN + Enterprise GenieACS TR-069 + Billing - " . ($duration === 12 ? '1 Tahun' : "{$duration} Bulan");
        }

        $trxPrefix = in_array($pkg, ['mikhmon_enterprise', 'mikhmon_acs']) ? 'VPN-ACS-' : (in_array($pkg, ['mikhmon_lite', 'mikhmon_pro', 'mikhmon_cloud', 'mikhmon_wa_pro', 'free']) ? 'VPN-MIKH-' : 'VPN-');
        $trxId = $trxPrefix . strtoupper(uniqid());
        $server = VpnServer::first();
        if (!$server) {
            return back()->withErrors(['msg' => 'Server VPN belum dikonfigurasi oleh Super Admin.'])->withInput();
        }

        $trx = VpnTransaction::create([
            'trx_id' => $trxId,
            'user_id' => auth()->id(),
            'amount' => $amount,
            'duration_months' => $duration,
            'status' => 'pending_payment',
            'requested_username' => $request->username,
            'requested_password' => $request->password,
            'requested_ports' => $allowedCount,
            'local_ports_json' => $portsArray,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'category' => 'VPN Remote',
            'action' => "Order VPN: {$trxType} ({$request->username})",
        ]);

        if ($pkg === 'free') {
            auth()->user()->update(['has_used_trial' => true]);
            $result = \App\Services\VpnService::provision($trx, 7);
            if ($result['status']) {
                return redirect()->route('vpn.index')->with('success', 'Akun Free Trial VPN Anda berhasil diaktifkan!');
            } else {
                return redirect()->route('vpn.index')->withErrors(['msg' => $result['message']]);
            }
        }

        return redirect()->route('vpn.payment', $trx->trx_id);
    }

    public function payment(Request $request, $trxId)
    {
        $trx = VpnTransaction::where('trx_id', $trxId)->firstOrFail();
        
        if (in_array($trx->status, ['completed', 'paid', 'active'])) {
            return redirect()->route('vpn.index')->with('success', 'Transaksi ini sudah selesai dan aktif.');
        }

        $gatewayActive = \App\Models\Setting::get('saas_payment_gateway_active') ?: (\App\Models\Setting::get('saas_payment_gateway') ?: (\App\Models\Setting::get('payment_gateway_active') ?: 'tripay'));
        $isGatewayActive = in_array(strtolower((string)$gatewayActive), ['tripay', '1', 'true', 'active', 'on', 'yes', '']);
        
        $tripayService = new TripayService();
        $tripayChannels = $isGatewayActive ? ($tripayService->getChannels() ?? []) : [];

        $manualDanaActive = \App\Models\Setting::get('saas_payment_manual_dana_active', '1');
        $manualBankActive = \App\Models\Setting::get('saas_payment_manual_bank_active', '1');
        $manualNumber = \App\Models\Setting::get('saas_payment_manual_number', '083826164767');
        $manualBank = \App\Models\Setting::get('saas_payment_manual_bank', 'BCA');
        $manualAccount = \App\Models\Setting::get('saas_payment_manual_account', '1234567890');
        $manualName = \App\Models\Setting::get('saas_payment_manual_name', 'Kurtech');

        $validMethods = ['QRIS', 'QRIS2', 'QRISC', 'DANA', 'OVO', 'SHOPEEPAY', 'MANUAL'];
        $reqMethod = strtoupper($request->input('method', ''));

        // Prefer active QRIS2 variant
        $defaultQris = 'QRIS2';
        if (!empty($tripayChannels)) {
            $codes = array_map(function($c) { return strtoupper($c['code'] ?? ''); }, $tripayChannels);
            if (in_array('QRIS2', $codes)) {
                $defaultQris = 'QRIS2';
            } elseif (in_array('QRIS', $codes)) {
                $defaultQris = 'QRIS';
            }
        }

        if (in_array($reqMethod, $validMethods)) {
            $selectedMethod = $reqMethod;
        } elseif (in_array(strtoupper($trx->payment_method ?: ''), $validMethods)) {
            $selectedMethod = strtoupper($trx->payment_method);
        } else {
            $selectedMethod = $defaultQris;
        }

        if ($selectedMethod === 'QRIS' && $defaultQris !== 'QRIS') {
            $selectedMethod = $defaultQris;
        }

        // Ensure unique nominal for automated payment matching
        if ($trx->status === 'pending_payment') {
            if (empty($trx->total_amount) || (int)$trx->total_amount === (int)$trx->amount) {
                $uniqueCode = rand(111, 899);
                $trx->total_amount = $trx->amount + $uniqueCode;
                $trx->save();
            }
        }

        $tripayError = session('tripay_last_error') ?: '';

        if ($selectedMethod === 'MANUAL') {
            $tripayError = '';
            session()->forget('tripay_last_error');
            if ($trx->payment_method !== 'manual') {
                $trx->update(['payment_method' => 'manual']);
            }
        }

        // Ensure channels are available
        if (empty($tripayChannels)) {
            $tripayChannels = [
                ['code' => 'QRIS', 'name' => 'QRIS Kurtech (Semua Bank / E-Wallet)', 'icon_url' => asset('images/qris.svg')],
                ['code' => 'DANA', 'name' => 'DANA Bisnis', 'icon_url' => asset('images/dana.svg')],
                ['code' => 'OVO', 'name' => 'OVO', 'icon_url' => asset('images/ovo.svg')],
                ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'icon_url' => asset('images/shopeepay.svg')],
            ];
        }

        // If qr_url is not set, default to Kurtech's official DANA Bisnis QRIS standee
        if (empty($trx->qr_url)) {
            $trx->qr_url = asset('images/qris-kurtech.jpg');
        }

        $isManualActive = true;
        return view('vpn.payment', compact(
            'trx', 'gatewayActive', 'isGatewayActive', 'isManualActive', 
            'tripayChannels', 'selectedMethod', 'tripayError',
            'manualDanaActive', 'manualBankActive', 'manualNumber', 'manualBank', 'manualAccount', 'manualName'
        ));
    }

    public function cancelTransaction($trxId)
    {
        $trx = VpnTransaction::where('trx_id', $trxId)
            ->where('user_id', auth()->id())
            ->where('status', 'pending_payment')
            ->firstOrFail();

        $trx->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'category' => 'VPN Remote',
            'action' => 'Membatalkan pesanan VPN (' . $trxId . ')',
        ]);

        return redirect()->route('vpn.index')->with('success', 'Pesanan VPN berhasil dibatalkan.');
    }

    public function payWithTripay(Request $request, $trxId)
    {
        $trx = VpnTransaction::where('trx_id', $trxId)->firstOrFail();

        $method = strtoupper($request->input('method', 'QRIS2'));
        if ($method === 'QRIS') {
            $method = 'QRIS2';
        }

        $tripayService = new TripayService();
        $tripayResponse = $tripayService->requestClosedPayment($trx, $method);

        if ($tripayResponse) {
            $checkoutUrl = $tripayResponse['checkout_url'] 
                ?? $tripayResponse['pay_url'] 
                ?? (!empty($tripayResponse['reference']) ? 'https://tripay.co.id/checkout/' . $tripayResponse['reference'] : null);

            $trx->update([
                'payment_method' => $method,
                'checkout_url'   => $checkoutUrl,
                'qr_url'         => $tripayResponse['qr_url'] ?? $trx->qr_url,
                'qr_expired_at'  => $tripayResponse['expired_time'] ?? (time() + 86400),
                'total_amount'   => $tripayResponse['amount'] ?? $trx->amount,
            ]);
            return redirect()->route('vpn.payment', ['trx_id' => $trx->trx_id, 'method' => $method])
                ->with('success', 'Kode pembayaran ' . $method . ' berhasil dibuat!');
        }

        $errMsg = session('tripay_last_error') ?: 'Gagal membuat tagihan Tripay. Pastikan konfigurasi Anda benar atau hubungi admin.';
        return redirect()->route('vpn.payment', ['trx_id' => $trx->trx_id, 'method' => $method])
            ->withErrors(['msg' => $errMsg]);
    }

    public function uploadProof(Request $request, $trxId)
    {
        $request->validate([
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $trx = VpnTransaction::where('trx_id', $trxId)->where('user_id', auth()->id())->firstOrFail();

        if ($request->hasFile('proof_of_payment')) {
            $path = $request->file('proof_of_payment')->store('vpn_proofs', 'public');
            $trx->update([
                'proof_of_payment' => $path,
                'status' => 'pending_verification'
            ]);
        }

        return redirect()->route('vpn.index')->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }

    public function status($id)
    {
        $user = auth()->user();
        $query = VpnAccount::query();
        if (!$user || $user->role !== 'superadmin') {
            $query->where('user_id', $user ? $user->id : 0);
        }
        $account = $query->findOrFail($id);
        
        if (!$account->server) {
            return response()->json(['error' => 'Server tidak ditemukan'], 404);
        }

        $status = MikrotikService::getAccountStatus($account->server, $account->username, $account->remote_ip);

        if (!$status) {
            return response()->json(['error' => 'Gagal terhubung ke Router'], 500);
        }

        return response()->json($status);
    }

    public function extend(Request $request, $id)
    {
        $request->validate([
            'duration' => 'required|in:1,3,6,12'
        ]);

        $user = auth()->user();
        $query = VpnAccount::query();
        if (!$user || $user->role !== 'superadmin') {
            $query->where('user_id', $user ? $user->id : 0);
        }
        $account = $query->findOrFail($id);
        
        // Calculate amount based on is_mikhmon, port_limit and duration
        if ($account->is_mikhmon) {
            $basePrice = 15000;
            $tenantConfigPath = 'c:/laragon/www/mikhmon/cloud_saas/tenants/' . $account->username . '/tenant_config.json';
            if (file_exists($tenantConfigPath)) {
                $tConfig = json_decode(file_get_contents($tenantConfigPath), true);
                if (!empty($tConfig['plan_type']) && in_array($tConfig['plan_type'], ['mikhmon_wa_pro', 'mikhmon_pro', 'mikhmon_cloud'])) {
                    if ($tConfig['plan_type'] !== 'mikhmon_lite') {
                        $basePrice = 45000;
                    }
                }
            }
        } else {
            $basePrice = $account->port_limit > 1 ? 6000 : 2000;
        }
        $amount = $basePrice * $request->duration;
        
        // Create transaction
        $trxId = 'VPN-EXT-' . strtoupper(uniqid());
        $trx = VpnTransaction::create([
            'trx_id' => $trxId,
            'user_id' => auth()->id(),
            'vpn_account_id' => $account->id,
            'amount' => $amount,
            'duration_months' => $request->duration,
            'status' => 'pending_payment',
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'category' => 'VPN Remote',
            'action' => 'Extend vpn remote ' . $account->username,
        ]);

        return redirect()->route('vpn.payment', $trx->trx_id)->with('success', "Order perpanjangan VPN selama {$request->duration} bulan berhasil dibuat.");
    }

        public function resetMikhmon($id)
    {
        $account = VpnAccount::where('user_id', auth()->id())->with(['server', 'ports'])->findOrFail($id);
        $username = $account->username;
        $serverHost = $account->server ? ($account->server->domain ?? $account->server->host) : 'id1.kurtech.my.id';
        $apiPort = $account->ports->firstWhere('local_port', 8728);
        $apiPublicPort = $apiPort ? $apiPort->public_port : 8728;
        
        // 1. Hapus file tenant lama
        $tenantFile = 'c:/laragon/www/mikhmon/include/tenants/' . $username . '.php';
        if (file_exists($tenantFile)) {
            @unlink($tenantFile);
        }

        // 2. Hapus config cloud saas jika ada
        $saasFile = 'c:/laragon/www/mikhmon/cloud_saas/tenants/' . $username . '/tenant_config.json';
        if (file_exists($saasFile)) {
            @unlink($saasFile);
        }
        
        // 3. Hapus file logo kustom jika ada
        $logoFiles = glob('c:/laragon/www/mikhmon/img/' . $username . '*');
        if (!empty($logoFiles)) {
            foreach ($logoFiles as $lf) {
                @unlink($lf);
            }
        }

        // 4. Buat ulang konfigurasi default fresh seketika
        $hVal = $serverHost . ($apiPublicPort ? ':' . $apiPublicPort : '');
        $phpCode = "<?php\n\$data['{$username}'] = array ('1'=>'{$username}!{$hVal}','{$username}@|@admin','{$username}#|#','{$username}%WiFi " . ucfirst($username) . "','{$username}^','{$username}&Rp','{$username}*10','{$username}(1','{$username})','{$username}=10','{$username}@!@enable');\n";
        @file_put_contents($tenantFile, $phpCode);

        return redirect()->route('vpn.index')->with('success', "Login Mikhmon untuk router '{$username}' berhasil di-reset ke default (User: mikhmon / Pass: 1234)! Silakan klik 'Open Mikhmon'.");
    }

    public function upgradeMikhmon(Request $request, $id)
    {
        $request->validate([
            'plan_type' => 'required|in:mikhmon_lite,mikhmon_pro,mikhmon_cloud,mikhmon_wa_pro,genieacs_enterprise',
            'duration' => 'required|in:1,3,6,12'
        ]);

        $user = auth()->user();
        $query = VpnAccount::query();
        if (!$user || $user->role !== 'superadmin') {
            $query->where('user_id', $user ? $user->id : 0);
        }
        $account = $query->findOrFail($id);
        $duration = (int) $request->duration;

        if ($request->plan_type === 'genieacs_enterprise') {
            $amount = ($duration === 12) ? 1000000 : (100000 * $duration);
            $planName = 'Enterprise GenieACS TR-069 + Billing Kurtech';
        } elseif ($request->plan_type === 'mikhmon_lite') {
            $amount = ($duration === 12) ? 150000 : (15000 * $duration);
            $planName = 'Mikhmon Standar (Hotspot + PPPoE)';
        } else {
            $amount = ($duration === 12) ? 500000 : (45000 * $duration);
            $planName = 'Billing Kurtech Pro';
        }
        $trxPrefix = ($request->plan_type === 'genieacs_enterprise') ? 'VPN-ACS-' : 'VPN-MIKH-';
        $trxId = $trxPrefix . strtoupper(uniqid());

        $trx = VpnTransaction::create([
            'trx_id' => $trxId,
            'user_id' => auth()->id(),
            'vpn_account_id' => $account->id,
            'amount' => $amount,
            'duration_months' => $duration,
            'status' => 'pending_payment',
            'requested_username' => $account->username,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'category' => 'Mikhmon Cloud',
            'action' => "Add-on {$planName} ({$duration} Bulan) untuk VPN {$account->username}",
        ]);

        return redirect()->route('vpn.payment', $trx->trx_id)->with('success', "Order Add-on {$planName} ({$duration} Bulan) untuk {$account->username} berhasil dibuat.");
    }

    public function updatePort(Request $request, $id)
    {
        $request->validate([
            'local_port' => 'required|integer|min:1|max:65535'
        ]);

        $port = VpnPort::whereHas('account', function($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        $server = $port->account->server;

        // 1. Selalu update ke Database Lokal terlebih dahulu agar tersimpan
        $port->update(['local_port' => $request->local_port]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'category' => 'VPN Remote',
            'action' => "Update Port Forwarding: {$port->account->username} (Port {$port->public_port} -> {$request->local_port})",
        ]);

        // 2. Coba update ke MikroTik CHR jika server terhubung
        $isUpdated = false;
        if ($server) {
            try {
                $isUpdated = MikrotikService::updateVpnNat($server, $port->public_port, $request->local_port, $port->account->username);
            } catch (\Exception $e) {
                \Log::error("VPN NAT Update Exception: " . $e->getMessage());
            }
        }

        if ($isUpdated) {
            return back()->with('success', "Target Port berhasil diubah menjadi {$request->local_port} (Database & MikroTik CHR ter-update).");
        }

        return back()->with('warning', "Target Port {$request->local_port} berhasil disimpan di Database lokal! (Catatan: Sync ke MikroTik CHR belum aktif/offline).");
    }

    public function transactionHistory()
    {
        $transactions = VpnTransaction::where('user_id', auth()->id())
            ->with(['vpnAccount'])
            ->latest()
            ->paginate(15);

        return view('vpn.transactions_history', compact('transactions'));
    }

    public function updateBillingAuth(Request $request, $id)
    {
        $request->validate([
            'admin_user' => 'required|string|min:3|max:35|alpha_dash',
            'admin_pass' => 'required|string|min:4|max:100',
        ]);

        $user = auth()->user();
        $query = VpnAccount::query();
        if (!$user || $user->role !== 'superadmin') {
            $query->where('user_id', $user ? $user->id : 0);
        }
        $account = $query->findOrFail($id);
        $username = $account->username;
        $newUser = trim($request->admin_user);
        $newPass = $request->admin_pass;

        // 1. Update / create cloud_saas tenant_config.json
        $saasDir = 'c:/laragon/www/mikhmon/cloud_saas/tenants/' . $username;
        if (!is_dir($saasDir)) {
            @mkdir($saasDir, 0777, true);
        }
        $saasFile = $saasDir . '/tenant_config.json';
        $conf = [];
        if (file_exists($saasFile)) {
            $conf = json_decode(@file_get_contents($saasFile), true) ?: [];
        }
        
        $conf['tenant_id'] = $username;
        if (empty($conf['business_name'])) {
            $conf['business_name'] = ucfirst($username) . ' Network';
        }
        $conf['admin_user'] = $newUser;
        $conf['admin_pass'] = password_hash($newPass, PASSWORD_BCRYPT);
        @file_put_contents($saasFile, json_encode($conf, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // 2. Update / create include/tenants/{username}.php
        $tenantDir = 'c:/laragon/www/mikhmon/include/tenants';
        if (!is_dir($tenantDir)) {
            @mkdir($tenantDir, 0777, true);
        }
        $tenantFile = $tenantDir . '/' . $username . '.php';
        
        if (file_exists($tenantFile)) {
            $content = file_get_contents($tenantFile);
            $content = preg_replace('/\\$data\\[[\'"][^\'"]+[\'"]\\]\\[[\'"]adm_user[\'"]\\]\\s*=\\s*[\'"][^\'"]*[\'"];?\\r?\\n?/', '', $content);
            $content = preg_replace('/\\$data\\[[\'"][^\'"]+[\'"]\\]\\[[\'"]adm_pass[\'"]\\]\\s*=\\s*[\'"][^\'"]*[\'"];?\\r?\\n?/', '', $content);
            $content = trim($content);
            $appendCode = "\n\$data['{$username}']['adm_user'] = '" . addslashes($newUser) . "';\n\$data['{$username}']['adm_pass'] = '" . addslashes($newPass) . "';\n";
            file_put_contents($tenantFile, $content . $appendCode);
        } else {
            $serverHost = $account->server ? ($account->server->domain ?? $account->server->host) : 'id1.kurtech.my.id';
            $apiPort = $account->ports->firstWhere('local_port', 8728);
            $apiPublicPort = $apiPort ? $apiPort->public_port : 8728;
            $hVal = $serverHost . ($apiPublicPort ? ':' . $apiPublicPort : '');
            $phpCode = "<?php\n\$data['{$username}'] = array ('1'=>'{$username}!{$hVal}','{$username}@|@admin','{$username}#|#','{$username}%WiFi " . ucfirst($username) . "','{$username}^','{$username}&Rp','{$username}*10','{$username}(1','{$username})','{$username}=10','{$username}@!@enable');\n\$data['{$username}']['adm_user'] = '" . addslashes($newUser) . "';\n\$data['{$username}']['adm_pass'] = '" . addslashes($newPass) . "';\n";
            file_put_contents($tenantFile, $phpCode);
        }

        // 3. Clear rate-limit lockout files
        foreach (glob('c:/laragon/www/mikhmon/data/login_security_*.json') as $lf) {
            @unlink($lf);
        }

        $appLabel = $account->has_billing ? 'Billing Kurtech' : 'Mikhmon';
        return redirect()->route('vpn.index')->with('success', "Akun login {$appLabel} untuk router '{$username}' berhasil diperbarui! Username: {$newUser} | Password telah disimpan.");
    }

    /**
     * Delete VPN Account
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $query = VpnAccount::query();
        if (!$user || $user->role !== 'superadmin') {
            $query->where('user_id', $user ? $user->id : 0);
        }
        $account = $query->findOrFail($id);
        $server = $account->server;
        $username = $account->username;

        if ($server) {
            foreach ($account->ports as $port) {
                try {
                    MikrotikService::deleteVpnNat($server, $port->public_port);
                } catch (\Throwable $e) {
                    \Log::warning("Gagal hapus NAT port {$port->public_port}: " . $e->getMessage());
                }
            }
            try {
                MikrotikService::deleteVpnAccount($server, $account->username);
            } catch (\Throwable $e) {
                \Log::warning("Gagal hapus Akun VPN MikroTik {$account->username}: " . $e->getMessage());
            }
        }

        // Hapus file konfigurasi session tenant jika ada
        $tenantFile = 'c:/laragon/www/mikhmon/include/tenants/' . $username . '.php';
        if (file_exists($tenantFile)) {
            @unlink($tenantFile);
        }

        $account->ports()->delete();
        $account->delete();

        return redirect()->route('vpn.index')->with('success', "Akun VPN '{$username}' berhasil dihapus.");
    }

    /**
     * Real-time status check for AJAX polling
     */
    public function checkPaymentStatus($trxId)
    {
        $trx = VpnTransaction::where('trx_id', $trxId)->first();
        if (!$trx) {
            return response()->json(['status' => 'not_found', 'is_paid' => false], 404);
        }

        // Live check with Tripay API if pending
        if ($trx->status === 'pending_payment' && (!empty($trx->qr_url) || !empty($trx->checkout_url))) {
            $tripayService = new TripayService();
            $status = $tripayService->checkStatusFromTripay($trx);
            if ($status === 'PAID') {
                $provisionResult = \App\Services\VpnService::provision($trx, 30);
                if ($provisionResult['status']) {
                    \App\Models\ActivityLog::create([
                        'user_id' => $trx->user_id,
                        'category' => 'VPN Remote',
                        'action' => 'Pembuatan Akun VPN Otomatis (Tripay Polling)',
                        'details' => "Akun VPN {$trx->requested_username} dan {$trx->requested_ports} port berhasil dibuat. Status pembayaran: PAID."
                    ]);
                }
                $trx->refresh();
            }
        }

        return response()->json([
            'status' => $trx->status,
            'is_paid' => in_array($trx->status, ['completed', 'paid', 'active']),
        ]);
    }
}

