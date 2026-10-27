<?php

namespace App\Services;

use App\Models\VpnTransaction;
use App\Models\VpnServer;
use App\Models\VpnAccount;
use App\Models\VpnPort;

class VpnService
{
    public static function provision(VpnTransaction $trx, $daysToExpire = 30)
    {
        $server = VpnServer::first();
        if (!$server) {
            return ['status' => false, 'message' => 'Data VPN Server CHR belum dikonfigurasi.'];
        }

        $months = (int) ($trx->duration_months ?: 0);
        $isExtension = !empty($trx->vpn_account_id) || str_starts_with($trx->trx_id ?? '', 'VPN-EXT-');

        // Handle Add-on / Extension of existing account
        if ($isExtension) {
            $account = $trx->vpnAccount ?: VpnAccount::find($trx->vpn_account_id);
            if (!$account) {
                $account = VpnAccount::where('user_id', $trx->user_id)->latest()->first();
            }

            if ($account) {
                $isMikhmon = str_contains($trx->trx_id ?? '', 'VPN-MIKH-') || str_contains($trx->trx_id ?? '', 'VPN-LITE-') || str_contains($trx->trx_id ?? '', 'VPN-PRO-') || str_contains($trx->trx_id ?? '', 'VPN-ACS-');
                $isGenieacs = str_contains($trx->trx_id ?? '', 'VPN-ACS-');

                if ($months >= 1000) {
                    $newExpiry = \Carbon\Carbon::parse('2099-12-31 23:59:59');
                } else {
                    $newExpiry = ($account->expired_at && \Carbon\Carbon::parse($account->expired_at)->isFuture())
                        ? \Carbon\Carbon::parse($account->expired_at)->addMonths($months > 0 ? $months : 1)
                        : now()->addMonths($months > 0 ? $months : 1);
                }

                $updateData = ['expired_at' => $newExpiry, 'status' => 'active'];
                if ($isMikhmon) $updateData['is_mikhmon'] = true;
                if ($isGenieacs) $updateData['is_genieacs'] = true;
                $account->update($updateData);

                // Re-enable / ensure secret in Mikrotik CHR
                try {
                    MikrotikService::createVpnAccount($server, $account->username, $account->password, $account->remote_ip);
                    MikrotikService::enableVpnAccount($server, $account->username);
                } catch (\Throwable $e) {}

                // If Mikhmon upgrade, guarantee 4 Standard Ports: Winbox(8291), API(8728), WebFig(80), Custom(3000)
                if ($isMikhmon) {
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

                    // Provision Mikhmon tenant config
                    $provFile = base_path('../mikhmon/cloud_saas/tenant_provisioner.php');
                    if (file_exists($provFile)) {
                        require_once $provFile;
                        $provisioner = new \MikhmonCloudProvisioner();
                        $provisioner->createTenant([
                            'tenant_slug' => $account->username,
                            'business_name' => 'WiFi ' . ucfirst($account->username),
                            'vpn_host' => $server->domain ?: $server->host,
                            'vpn_api_port' => $apiPort ? $apiPort->public_port : 8728,
                            'router_user' => 'admin',
                            'router_pass' => '',
                            'plan_type' => $isGenieacs ? 'mikhmon_acs' : (($trx->amount >= 35000) ? 'mikhmon_wa_pro' : 'mikhmon_lite'),
                            'expired_date' => \Carbon\Carbon::parse($account->expired_at)->format('Y-m-d'),
                        ]);
                    }
                }

                $trx->update(['status' => 'completed', 'vpn_account_id' => $account->id]);
                return ['status' => true, 'message' => 'Layanan VPN / Mikhmon berhasil diperbarui!'];
            }
        }

        // 1. Find Available Remote IP
        $usedIps = VpnAccount::whereNotNull('remote_ip')->pluck('remote_ip')->toArray();
        $remoteIp = null;
        
        // Alokasi IP untuk VPN Remote: 26.8.9.2 hingga 26.8.90.254 (terpisah dari hotspot 26.8.99.X)
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
            return ['status' => false, 'message' => 'IP Pool (26.8.9.X - 26.8.90.X) sudah penuh!'];
        }

        $months = (int) ($trx->duration_months ?: 0);

        // 2. Create VPN Account in DB
        $account = VpnAccount::create([
            'user_id' => $trx->user_id,
            'vpn_server_id' => $server->id,
            'username' => $trx->requested_username,
            'password' => $trx->requested_password,
            'remote_ip' => $remoteIp,
            'status' => 'active',
            'port_limit' => $trx->requested_ports,
            'is_mikhmon' => (!str_contains(strtolower($trx->requested_username ?? ''), 'olt') && (
                str_contains($trx->trx_id ?? '', 'VPN-MIKH-') || 
                str_contains($trx->trx_id ?? '', 'VPN-LITE-') || 
                str_contains($trx->trx_id ?? '', 'VPN-PRO-') || 
                str_contains($trx->trx_id ?? '', 'VPN-ACS-') || 
                (!str_starts_with($trx->trx_id ?? '', 'VPN-EXT-') && (($months > 0 && ($trx->amount / $months) >= 15000) || ($months == 0 && $trx->amount >= 15000)))
            )),
            'is_genieacs' => (!str_contains(strtolower($trx->requested_username ?? ''), 'olt') && (str_contains($trx->trx_id ?? '', 'VPN-ACS-') || (!str_starts_with($trx->trx_id ?? '', 'VPN-EXT-') && (($months > 0 && ($trx->amount / $months) >= 100000) || ($months == 0 && $trx->amount >= 100000))))),
            'expired_at' => ($months >= 1000) ? \Carbon\Carbon::parse('2099-12-31 23:59:59') : (($months > 0) ? now()->addMonths($months) : now()->addDays($daysToExpire)),
        ]);

        // 3. Inject to MikroTik CHR via API
        $isCreated = MikrotikService::createVpnAccount($server, $account->username, $account->password, $remoteIp, 'L2TP CLIENT', ($trx->amount == 0));
        if (!$isCreated) {
            $account->delete();
            return ['status' => false, 'message' => 'Gagal membuat akun L2TP di MikroTik CHR. Pastikan koneksi API aktif.'];
        }

        // 4. Create Ports & NAT Rules
        $localPorts = $trx->local_ports_json ?? [];
        if ($account->is_mikhmon) {
            $standardPorts = [8291, 8728, 80, 3000];
            foreach ($standardPorts as $stdPort) {
                if (!in_array($stdPort, $localPorts)) {
                    $localPorts[] = $stdPort;
                }
            }
            if ($account->port_limit < 4) {
                $account->update(['port_limit' => 4]);
            }
        }
        $usedPorts = VpnPort::pluck('public_port')->toArray();
        $apiPublicPort = null;

        foreach ($localPorts as $lPort) {
            $randomPort = VpnPort::generateUniquePort($usedPorts);
            
            VpnPort::create([
                'vpn_account_id' => $account->id,
                'public_port' => $randomPort,
                'local_port' => $lPort,
            ]);
            $usedPorts[] = $randomPort;

            // Jika port lokal 8728 (API Mikrotik)
            if ((int)$lPort === 8728) {
                $apiPublicPort = $randomPort;
            }

            MikrotikService::createVpnNat($server, $randomPort, $remoteIp, $lPort, $account->username);
        }

        // 5. Trigger Otomatis Pembuatan Mikhmon Cloud Jika Paket Mikhmon
        $monthlyRate = $months > 0 ? ($trx->amount / $months) : $trx->amount;
        if ($apiPublicPort && ($monthlyRate >= 15000 || $trx->amount == 0)) {
            try {
                $targetEndpoints = [
                    'http://10.10.10.248/cloud_saas/api_provision.php',
                    'http://10.10.10.250/cloud_saas/api_provision.php',
                    'https://mikhmon.kurtech.my.id/cloud_saas/api_provision.php'
                ];

                if ($monthlyRate >= 90000 || str_contains($trx->trx_id ?? '', 'VPN-ACS-')) {
                    $planType = 'mikhmon_acs';
                } elseif ($monthlyRate >= 35000 || str_contains($trx->trx_id ?? '', 'VPN-PRO-')) {
                    $planType = 'mikhmon_wa_pro';
                } else {
                    $planType = 'mikhmon_lite'; // Paket 15rb murni Mikhmon Standar tanpa billing
                }
                
                $postData = [
                    'action' => 'create',
                    'tenant_slug' => $account->username,
                    'business_name' => 'WiFi ' . ucfirst($account->username),
                    'vpn_host' => $server->host ?? 'id1.kurtech.my.id',
                    'vpn_api_port' => $apiPublicPort,
                    'router_user' => 'admin',
                    'router_pass' => '',
                    'plan_type' => $planType,
                    'expired_date' => $account->expired_at ? $account->expired_at->format('Y-m-d') : date('Y-m-d', strtotime('+30 days'))
                ];

                foreach ($targetEndpoints as $ep) {
                    try {
                        $ch = curl_init($ep);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_exec($ch);
                        curl_close($ch);
                    } catch (\Throwable $t) {}
                }
            } catch (\Exception $e) {
                // Jangan gagalkan pembuatan VPN jika panggilan API SaaS timeout
            }
        }

        // 6. Update Transaction
        $trx->update([
            'status' => 'completed',
            'vpn_account_id' => $account->id
        ]);

        return ['status' => true, 'message' => 'VPN Otomatis Dibuat!'];
    }
}

