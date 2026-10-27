<?php

namespace App\Http\Controllers;

use App\Models\VpnAccount;
use App\Models\VpnServer;
use App\Models\VpnPort;
use App\Models\VpnTransaction;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class SuperAdminVpnController extends Controller
{
    public function servers()
    {
        $server = VpnServer::first();
        return view('superadmin.vpn.server', compact('server'));
    }

    public function createServer()
    {
        $server = VpnServer::first();
        return view('superadmin.vpn.server', compact('server'));
    }

    public function storeServer(Request $request)
    {
        $request->validate([
            'host' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'port' => 'required|integer',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $server = VpnServer::first();
        if ($server) {
            $server->update([
                'name' => 'Main Server CHR',
                'host' => $request->host,
                'domain' => $request->domain,
                'port' => $request->port,
                'username' => $request->username,
                'password' => $request->password,
                'is_active' => true,
            ]);
        } else {
            VpnServer::create([
                'name' => 'Main Server CHR',
                'host' => $request->host,
                'domain' => $request->domain,
                'port' => $request->port,
                'username' => $request->username,
                'password' => $request->password,
                'is_active' => true,
            ]);
        }

        return redirect()->route('superadmin.vpn.server')->with('success', 'Konfigurasi Server VPN CHR berhasil disimpan.');
    }

    public function editServer($id)
    {
        $server = VpnServer::findOrFail($id);
        return view('superadmin.vpn.server', compact('server'));
    }

    public function updateServer(Request $request, $id)
    {
        $server = VpnServer::findOrFail($id);
        $request->validate([
            'host' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'port' => 'required|integer',
            'username' => 'required|string|max:255',
        ]);

        $data = [
            'name' => 'Main Server CHR',
            'host' => $request->host,
            'domain' => $request->domain,
            'port' => $request->port,
            'username' => $request->username,
            'is_active' => true,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $server->update($data);

        return redirect()->route('superadmin.vpn.server')->with('success', 'Server VPN CHR berhasil diperbarui.');
    }

    public function destroyServer($id)
    {
        $server = VpnServer::findOrFail($id);
        $server->delete();

        return redirect()->route('superadmin.vpn.server')->with('success', 'Server VPN CHR berhasil dihapus.');
    }

    public function accounts()
    {
        $accounts = VpnAccount::with(['user', 'server', 'ports'])->latest()->get();
        return view('superadmin.vpn.accounts', compact('accounts'));
    }

    public function transactions()
    {
        $transactions = VpnTransaction::with(['user', 'vpnAccount'])->latest()->get();
        return view('superadmin.vpn.transactions', compact('transactions'));
    }

    public function approveTransaction(Request $request, $id)
    {
        $trx = VpnTransaction::findOrFail($id);
        if ($trx->status !== 'pending_verification' && $trx->status !== 'pending_payment') {
            return back()->withErrors(['msg' => 'Status transaksi tidak valid atau sudah disetujui.']);
        }

        $server = VpnServer::first();
        if (!$server) {
            return back()->withErrors(['msg' => 'Data VPN Server CHR belum dikonfigurasi.']);
        }

        $pkg = $trx->package ?? null;
        $dur = (int) ($trx->duration_months ?: 1);
        $monthlyRate = $dur > 0 ? ($trx->amount / $dur) : ($pkg ? $pkg->price : 2000);
        $isGenieacs = (str_contains($trx->trx_id ?? '', 'ACS') || str_contains(strtolower($trx->requested_package ?? ''), 'acs') || str_contains(strtolower($trx->requested_package ?? ''), 'genieacs') || str_contains(strtolower($trx->requested_package ?? ''), 'enterprise'));
        $isOlt = str_contains(strtolower($trx->requested_username ?? ''), 'olt');
        $isMikhmon = !$isOlt && (
            str_contains($trx->trx_id ?? "", "VPN-MIKH-") || 
            str_contains($trx->trx_id ?? "", "VPN-LITE-") || 
            str_contains($trx->trx_id ?? "", "VPN-PRO-") || 
            str_contains(strtolower($trx->requested_package ?? ''), 'mikhmon') || 
            (!str_starts_with($trx->trx_id ?? '', 'VPN-EXT-') && $monthlyRate >= 15000) || 
            $isGenieacs
        );

        if ($dur > 0) {
            $months = $dur;
        } elseif ($monthlyRate > 0) {
            $months = round($trx->amount / $monthlyRate);
            if ($months < 1) $months = 1;
        } else {
            $months = 0; // Trial
        }

        // Cek apakah akun dengan username yang diminta sudah ada
        $existingAcc = VpnAccount::where('user_id', $trx->user_id)
            ->where(function($q) use ($trx) {
                if (!empty($trx->requested_username)) $q->where('username', $trx->requested_username);
                if (!empty($trx->vpn_account_id)) $q->orWhere('id', $trx->vpn_account_id);
            })->first();

        // Tentukan apakah transaksi adalah perpanjangan (Extension / Renewal) atau akun sudah ada
        $isExtension = $existingAcc || !empty($trx->vpn_account_id) || str_starts_with($trx->trx_id ?? '', 'VPN-EXT-') || ($trx->is_extension ?? false);

        // --- RENEWAL / EXTENSION OF EXISTING ACCOUNT ---
        if ($isExtension) {
            // Cari akun VPN milik user
            $account = $existingAcc ?: ($trx->vpnAccount ?: VpnAccount::where('user_id', $trx->user_id)->latest()->first());
            if (!$account && $trx->vpn_account_id) {
                $account = VpnAccount::find($trx->vpn_account_id);
            }

            if ($account) {
                $newExpiry = ($account->expired_at && Carbon::parse($account->expired_at)->isFuture())
                    ? Carbon::parse($account->expired_at)->addMonths($months)
                    : now()->addMonths($months);

                $updateData = [
                    'expired_at' => $newExpiry,
                    'status'     => 'active'
                ];
                if (!empty($trx->requested_password)) {
                    $updateData['password'] = $trx->requested_password;
                }
                if (str_contains($trx->trx_id ?? "", "VPN-MIKH-") || str_contains($trx->trx_id ?? "", "VPN-LITE-") || str_contains($trx->trx_id ?? "", "VPN-PRO-") || str_contains(strtolower($trx->requested_package ?? ''), 'mikhmon')) {
                    $updateData['is_mikhmon'] = true;
                }
                if ($isGenieacs) {
                    $updateData['is_genieacs'] = true;
                }
                $account->update($updateData);

                // Ensure Mikrotik secret is updated & enabled (Safe Try-Catch)
                try {
                    MikrotikService::createVpnAccount($server, $account->username, $account->password, $account->remote_ip);
                    MikrotikService::enableVpnAccount($server, $account->username);
                } catch (\Throwable $e) {
                    \Log::warning("Mikrotik enable warning: " . $e->getMessage());
                }

                // Ensure ports exist
                $localPorts = $trx->local_ports_json ?? [8291];
                if (!is_array($localPorts)) {
                    $localPorts = json_decode($localPorts, true) ?: [8291];
                }

                if ($account->ports()->count() == 0) {
                    $usedPorts = VpnPort::pluck('public_port')->toArray();
                    foreach ($localPorts as $lPort) {
                        $randomPort = VpnPort::generateUniquePort($usedPorts);

                        VpnPort::create([
                            'vpn_account_id' => $account->id,
                            'public_port' => $randomPort,
                            'local_port' => $lPort,
                        ]);
                        $usedPorts[] = $randomPort;
                        try {
                            MikrotikService::createVpnNat($server, $randomPort, $account->remote_ip, $lPort, $account->username);
                        } catch (\Throwable $e) {
                            \Log::warning("Mikrotik NAT warning: " . $e->getMessage());
                        }
                    }
                }

                // Auto-Provision Mikhmon if upgraded - Ensure 4 Ports (8291, 8728, 80, 3000)
                if ($isMikhmon) {
                    try {
                        if ($account->port_limit < 4) {
                            $account->update(['port_limit' => 4]);
                        }

                        $standardPorts = [8291, 8728, 80, 3000];
                        $existingLocalPorts = $account->ports()->pluck('local_port')->map(fn($p) => (int)$p)->toArray();
                        $usedPublicPorts = VpnPort::pluck('public_port')->toArray();

                        foreach ($standardPorts as $stdPort) {
                            if (!in_array($stdPort, $existingLocalPorts)) {
                                $newRandomPort = VpnPort::generateUniquePort($usedPublicPorts);

                                VpnPort::create([
                                    'vpn_account_id' => $account->id,
                                    'public_port' => $newRandomPort,
                                    'local_port' => $stdPort,
                                ]);
                                $usedPublicPorts[] = $newRandomPort;
                                $existingLocalPorts[] = $stdPort;

                                try {
                                    MikrotikService::createVpnNat($server, $newRandomPort, $account->remote_ip, $stdPort, $account->username);
                                } catch (\Throwable $e) {
                                    \Log::warning("Gagal buat NAT port {$stdPort}: " . $e->getMessage());
                                }
                            }
                        }

                        $apiPort = $account->ports()->where('local_port', 8728)->first();
                        $publicApiPort = $apiPort ? $apiPort->public_port : 8728;
                        $provFile = base_path('../mikhmon/cloud_saas/tenant_provisioner.php');
                        if (file_exists($provFile)) {
                            require_once $provFile;
                            $provisioner = new \MikhmonCloudProvisioner();
                            $provisioner->createTenant([
                                'tenant_slug' => $account->username,
                                'business_name' => 'WiFi ' . ucfirst($account->username),
                                'vpn_host' => $server->domain ?: $server->host,
                                'vpn_api_port' => $publicApiPort,
                                'router_user' => 'admin',
                                'router_pass' => '',
                                'plan_type' => ($monthlyRate >= 90000 || $isGenieacs) ? 'mikhmon_acs' : (($monthlyRate >= 35000) ? 'mikhmon_wa_pro' : 'mikhmon_lite'),
                                'expired_date' => Carbon::parse($account->expired_at)->format('Y-m-d'),
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Mikhmon provision warning: " . $e->getMessage());
                    }
                }

                $trx->update([
                    'status' => 'completed',
                    'vpn_account_id' => $account->id,
                    'requested_username' => $account->username
                ]);

                return back()->with('success', "Transaksi {$months} Bulan untuk akun '{$account->username}' berhasil disetujui & aktif!");
            }
        }

        // --- BRAND NEW ACCOUNT ---
        $reqUsername = $trx->requested_username;
        if (empty($reqUsername)) {
            // Fallback: jika username kosong ambil dari username user atau user_id
            $reqUsername = $trx->user ? $trx->user->name : ('vpn_' . time());
            $reqUsername = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($reqUsername));
        }

        // 1. Find Available Remote IP
        $usedIps = VpnAccount::whereNotNull('remote_ip')->pluck('remote_ip')->toArray();
        $remoteIp = null;
        for ($subnet = 9; $subnet <= 90; $subnet++) {
            for ($i = 2; $i <= 254; $i++) {
                $ip = "26.8.{$subnet}.{$i}";
                if (!in_array($ip, $usedIps)) {
                    $remoteIp = $ip;
                    break 2;
                }
            }
        }

        if (!$remoteIp) {
            return back()->withErrors(['msg' => 'IP Pool (26.8.9.X - 26.8.90.X) sudah penuh!']);
        }

        // 2. Create VPN Account in DB
        $account = VpnAccount::create([
            'user_id' => $trx->user_id,
            'vpn_server_id' => $server->id,
            'username' => $reqUsername,
            'password' => $trx->requested_password ?: '12345',
            'remote_ip' => $remoteIp,
            'status' => 'active',
            'port_limit' => $trx->requested_ports ?: 1,
            'is_mikhmon' => $isMikhmon,
            'is_genieacs' => $isGenieacs,
            'expired_at' => ($months > 0) ? now()->addMonths($months) : now()->addDays(7),
        ]);

        // 3. Inject to MikroTik CHR via API
        try {
            $isCreated = MikrotikService::createVpnAccount($server, $account->username, $account->password, $remoteIp);
            if (!$isCreated) {
                \Log::warning("Gagal inject ke Mikrotik, tapi akun DB tetap dibuat.");
            }
        } catch (\Throwable $e) {
            \Log::warning("Mikrotik create warning: " . $e->getMessage());
        }

        // 4. Create Ports & NAT Rules
        $localPorts = $trx->local_ports_json ?? [8291];
        if (!is_array($localPorts)) {
            $localPorts = json_decode($localPorts, true) ?: [8291];
        }

        $usedPorts = VpnPort::pluck('public_port')->toArray();
        foreach ($localPorts as $lPort) {
            $randomPort = VpnPort::generateUniquePort($usedPorts);

            VpnPort::create([
                'vpn_account_id' => $account->id,
                'public_port' => $randomPort,
                'local_port' => $lPort,
            ]);
            $usedPorts[] = $randomPort;
            try {
                MikrotikService::createVpnNat($server, $randomPort, $remoteIp, $lPort, $account->username);
            } catch (\Throwable $e) {
                \Log::warning("Mikrotik NAT warning: " . $e->getMessage());
            }
        }

        // 5. Auto-Provision Mikhmon if requested
        if ($isMikhmon) {
            try {
                $apiPort = $account->ports()->where('local_port', 8728)->first();
                $publicApiPort = $apiPort ? $apiPort->public_port : 8728;
                $provFile = base_path('../mikhmon/cloud_saas/tenant_provisioner.php');
                if (file_exists($provFile)) {
                    require_once $provFile;
                    $provisioner = new \MikhmonCloudProvisioner();
                    $provisioner->createTenant([
                        'tenant_slug' => $account->username,
                        'business_name' => 'WiFi ' . ucfirst($account->username),
                        'vpn_host' => $server->domain ?: $server->host,
                        'vpn_api_port' => $publicApiPort,
                        'router_user' => 'admin',
                        'router_pass' => '',
                        'plan_type' => ($monthlyRate >= 90000 || $isGenieacs) ? 'mikhmon_acs' : (($monthlyRate >= 35000) ? 'mikhmon_wa_pro' : 'mikhmon_lite'),
                        'expired_date' => Carbon::parse($account->expired_at)->format('Y-m-d'),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Mikhmon provision warning: " . $e->getMessage());
            }
        }

        // 6. Update Transaction
        $trx->update([
            'status' => 'completed',
            'vpn_account_id' => $account->id,
            'requested_username' => $account->username
        ]);

        return back()->with('success', 'Transaksi berhasil disetujui & VPN + Mikhmon Otomatis Aktif!');
    }

    public function deleteTransaction(Request $request, $id)
    {
        $trx = VpnTransaction::findOrFail($id);
        $trx->delete();

        return back()->with('success', 'Data transaksi berhasil dihapus.');
    }

    public function destroyAccount($id)
    {
        $account = VpnAccount::findOrFail($id);
        $server = $account->server;

        if ($server) {
            foreach ($account->ports as $port) {
                try {
                    MikrotikService::deleteVpnNat($server, $port->public_port);
                } catch (\Throwable $e) {}
            }
            try {
                MikrotikService::deleteVpnAccount($server, $account->username);
            } catch (\Throwable $e) {}
        }

        $account->ports()->delete();
        $account->delete();

        return back()->with('success', 'Akun VPN berhasil dihapus beserta seluruh port-nya.');
    }

    public function activateManualVpn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package' => 'required|in:regular,mikhmon_lite,mikhmon_pro,mikhmon_enterprise',
            'duration' => 'required',
            'username' => 'required|string|max:30',
            'password' => 'required|string|max:50',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $pkg = $request->package;
        $durationInput = $request->input('duration', '1');
        $isLifetime = ($durationInput === 'lifetime');
        $duration = $isLifetime ? 1200 : (int) $durationInput;

        $amount = 0; // Superadmin manual aktivasi gratis
        $allowedCount = 1;
        $portsArray = [8291];
        $trxType = $isLifetime ? "VPN 1 Port (Gratis Selamanya / Lifetime 2099)" : "VPN 1 Port ({$duration} Bulan)";

        if ($pkg === 'mikhmon_lite') {
            $allowedCount = 4;
            $portsArray = [8291, 8728, 80, 3000];
            $trxType = $isLifetime ? "VPN + Mikhmon Basic Lite (Gratis Selamanya / Lifetime 2099)" : "VPN + Mikhmon Basic Lite ({$duration} Bulan)";
        } elseif ($pkg === 'mikhmon_pro') {
            $allowedCount = 4;
            $portsArray = [8291, 8728, 80, 3000];
            $trxType = $isLifetime ? "VPN + Billing Kurtech Pro (Gratis Selamanya / Lifetime 2099)" : "VPN + Billing Kurtech Pro ({$duration} Bulan)";
        } elseif ($pkg === 'mikhmon_enterprise') {
            $allowedCount = 4;
            $portsArray = [8291, 8728, 80, 3000];
            $trxType = $isLifetime ? "VPN + Enterprise GenieACS (Gratis Selamanya / Lifetime 2099)" : "VPN + Enterprise GenieACS ({$duration} Bulan)";
        }

        if ($pkg === 'mikhmon_enterprise') {
            $trxPrefix = 'VPN-ACS-';
        } elseif ($pkg === 'mikhmon_pro') {
            $trxPrefix = 'VPN-PRO-';
        } elseif ($pkg === 'mikhmon_lite') {
            $trxPrefix = 'VPN-LITE-';
        } else {
            $trxPrefix = 'VPN-';
        }
        $trxId = $trxPrefix . strtoupper(uniqid());

        $reqUser = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($request->username));

        $trx = VpnTransaction::create([
            'trx_id' => $trxId,
            'user_id' => $user->id,
            'amount' => $amount,
            'duration_months' => $duration,
            'status' => 'pending_payment',
            'requested_username' => $reqUser,
            'requested_password' => $request->password,
            'requested_ports' => $allowedCount,
            'local_ports_json' => $portsArray,
            'payment_method' => 'manual_superadmin',
        ]);

        $res = \App\Services\VpnService::provision($trx, 30 * $duration);

        if (!empty($res['status'])) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'category' => 'SuperAdmin',
                'action' => "SuperAdmin aktivasi manual VPN '{$reqUser}' ({$trxType}) untuk {$user->name}",
            ]);

            return back()->with('success', "Akun VPN '{$reqUser}' ({$trxType}) berhasil diaktifkan untuk tenant {$user->name}!");
        } else {
            return back()->withErrors(['msg' => 'Gagal aktivasi VPN: ' . ($res['message'] ?? 'Terjadi kesalahan sistem')]);
        }
    }

    public function updateAccount(Request $request, $id)
    {
        $account = VpnAccount::findOrFail($id);
        $server = $account->server;

        // 1. Password change
        if ($request->filled('password')) {
            $account->password = $request->password;
            if ($server) {
                try {
                    MikrotikService::createVpnAccount($server, $account->username, $account->password, $account->remote_ip);
                } catch (\Throwable $e) {
                    \Log::warning("Gagal update password di MikroTik: " . $e->getMessage());
                }
            }
        }

        // 2. Status change
        if ($request->filled('status')) {
            $account->status = $request->status;
            if ($server) {
                try {
                    if ($account->status === 'active') {
                        MikrotikService::enableVpnAccount($server, $account->username);
                    } else {
                        MikrotikService::disableVpnAccount($server, $account->username);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Gagal update status di MikroTik: " . $e->getMessage());
                }
            }
        }

        // 3. Extend / Masa Aktif
        $extend = $request->extend_period;
        if ($extend === 'lifetime') {
            $account->expired_at = \Carbon\Carbon::parse('2099-12-31 23:59:59');
            $account->status = 'active';
            if ($server) {
                try { MikrotikService::enableVpnAccount($server, $account->username); } catch (\Throwable $e) {}
            }
        } elseif ($extend === 'custom' && $request->filled('custom_expiry')) {
            $account->expired_at = \Carbon\Carbon::parse($request->custom_expiry . ' 23:59:59');
            $account->status = 'active';
            if ($server) {
                try { MikrotikService::enableVpnAccount($server, $account->username); } catch (\Throwable $e) {}
            }
        } elseif (!empty($extend)) {
            $monthsToAdd = match ($extend) {
                '1_month' => 1,
                '3_months' => 3,
                '6_months' => 6,
                '12_months' => 12,
                '24_months' => 24,
                default => 0
            };
            if ($monthsToAdd > 0) {
                $base = ($account->expired_at && \Carbon\Carbon::parse($account->expired_at)->isFuture())
                    ? \Carbon\Carbon::parse($account->expired_at)
                    : now();
                $account->expired_at = $base->addMonths($monthsToAdd);
                $account->status = 'active';
                if ($server) {
                    try { MikrotikService::enableVpnAccount($server, $account->username); } catch (\Throwable $e) {}
                }
            }
        }

        // 4. Mikhmon toggle
        if ($request->has('is_mikhmon')) {
            $account->is_mikhmon = (bool) $request->is_mikhmon;
        }

        $account->save();

        return back()->with('success', "Akun VPN '{$account->username}' berhasil diperbarui / diperpanjang!");
    }
}