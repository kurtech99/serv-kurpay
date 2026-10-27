<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpnAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vpn_server_id',
        'username',
        'password',
        'remote_ip',
        'status',
        'port_limit',
        'is_mikhmon',
        'is_genieacs',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'date',
        'port_limit' => 'integer',
        'is_mikhmon' => 'boolean',
        'is_genieacs' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function server()
    {
        return $this->belongsTo(VpnServer::class, 'vpn_server_id');
    }

    public function ports()
    {
        return $this->hasMany(VpnPort::class);
    }

    public function getHasBillingAttribute()
    {
        if ($this->is_genieacs) {
            return true;
        }

        // Router utama legacy
        if (in_array(strtolower($this->username), ['arka-cibiuk', 'arka-cijolang'])) {
            return true;
        }

        if (!$this->is_mikhmon) {
            return false;
        }

        // 1. Prioritas Utama: Cek cloud_saas tenant_config.json (Authoritative Plan)
        $tPaths = [
            base_path('public/mikhmon/cloud_saas/tenants/' . $this->username . '/tenant_config.json'),
            base_path('../billing-kurtech/cloud_saas/tenants/' . $this->username . '/tenant_config.json'),
            '/var/www/html/mikhmon/cloud_saas/tenants/' . $this->username . '/tenant_config.json',
            '/var/www/html/cloud_saas/tenants/' . $this->username . '/tenant_config.json',
        ];
        foreach ($tPaths as $tPath) {
            if (file_exists($tPath)) {
                $conf = json_decode(@file_get_contents($tPath), true);
                if (!empty($conf['plan_type'])) {
                    if ($conf['plan_type'] === 'mikhmon_lite') {
                        return false;
                    }
                    if (in_array($conf['plan_type'], ['mikhmon_wa_pro', 'mikhmon_pro', 'mikhmon_cloud', 'mikhmon_enterprise', 'mikhmon_acs'])) {
                        return true;
                    }
                }
            }
        }

        // 2. Cek transaksi terbaru klien
        $lastTrx = VpnTransaction::where(function($q) {
                $q->where('vpn_account_id', $this->id)
                  ->orWhere('requested_username', $this->username);
            })
            ->whereIn('status', ['completed', 'active', 'paid'])
            ->latest()
            ->first();

        if ($lastTrx) {
            $trxId = $lastTrx->trx_id ?? '';

            // Jika transaksi secara eksplisit paket Lite -> BUKAN billing!
            if (str_starts_with($trxId, 'VPN-LITE-')) {
                return false;
            }

            // Jika transaksi secara eksplisit paket Pro atau ACS -> WAJIB billing!
            if (str_starts_with($trxId, 'VPN-PRO-') || str_starts_with($trxId, 'VPN-ACS-')) {
                return true;
            }

            $months = max(1, (int)$lastTrx->duration_months);
            $monthlyRate = $lastTrx->amount / $months;

            // Jika tarif bulanan <= 20.000 (Paket 15rb Mikhmon Standar), WAJIB false!
            if ($monthlyRate <= 20000 && !str_starts_with($trxId, 'VPN-ACS-') && !str_starts_with($trxId, 'VPN-PRO-')) {
                return false;
            }

            // Jika tarif bulanan >= 35.000 atau paket ACS/PRO, aktifkan Billing
            if ($monthlyRate >= 35000 || str_starts_with($trxId, 'VPN-ACS-') || str_starts_with($trxId, 'VPN-PRO-')) {
                return true;
            }
        }

        // 3. Cek database pelanggan aktif bila ada
        $dataPaths = [
            base_path('../billing-kurtech/data/billing_customers_' . $this->username . '.json'),
            base_path('public/mikhmon/data/billing_customers_' . $this->username . '.json'),
            '/var/www/html/mikhmon/data/billing_customers_' . $this->username . '.json',
        ];
        foreach ($dataPaths as $dPath) {
            if (file_exists($dPath) && filesize($dPath) > 50) {
                return true;
            }
        }

        return false;
    }

    public function getLaunchUrlAttribute()
    {
        if ($this->has_billing) {
            return 'https://billing.kurtech.my.id/' . urlencode($this->username) . '/';
        }
        return 'https://mikhmon.kurtech.my.id/' . urlencode($this->username) . '/';
    }

    public function getLaunchLabelAttribute()
    {
        if ($this->has_billing) {
            return 'Open Billing';
        }
        return 'Open Mikhmon';
    }

    public function getBadgeNameAttribute()
    {
        if ($this->is_genieacs) {
            return 'GENIEACS + BILLING';
        }
        if ($this->has_billing) {
            return 'BILLING KURTECH';
        }
        if ($this->is_mikhmon) {
            return 'MIKHMON CLOUD';
        }
        return 'STANDAR VPN';
    }

    public function getBillingAdminUserAttribute()
    {
        $tPath = 'c:/laragon/www/mikhmon/cloud_saas/tenants/' . $this->username . '/tenant_config.json';
        if (file_exists($tPath)) {
            $conf = json_decode(@file_get_contents($tPath), true);
            if (!empty($conf['admin_user'])) {
                return $conf['admin_user'];
            }
        }

        $tenantFile = 'c:/laragon/www/mikhmon/include/tenants/' . $this->username . '.php';
        if (file_exists($tenantFile)) {
            $data = [];
            @include($tenantFile);
            if (!empty($data[$this->username]['adm_user'])) {
                return $data[$this->username]['adm_user'];
            }
        }

        return 'admin';
    }
}