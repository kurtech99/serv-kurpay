<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpnPort extends Model
{
    use HasFactory;

    protected $fillable = [
        'vpn_account_id',
        'public_port',
        'local_port',
        'protocol',
    ];

    public function account()
    {
        return $this->belongsTo(VpnAccount::class, 'vpn_account_id');
    }

    /**
     * Generator Port Publik Unik (100% Anti-Bentrok & Aman dari Port Keramat CHR)
     */
    public static function generateUniquePort(array $extraExclude = []): int
    {
        // 1. Port terlarang sistem MikroTik CHR & OS (JANGAN PERNAH DIACAK)
        $reservedPorts = [
            20, 21, 22, 23, 25, 53, 80, 443, 500, 1194, 1701, 1723, 
            2699, 4500, 5000, 8080, 8291, 8728, 8729, 9000, 10000
        ];

        // 2. Port yang sudah digunakan oleh seluruh tenant di database
        $usedPorts = self::pluck('public_port')->map(fn($p) => (int)$p)->toArray();

        // 3. Gabungkan seluruh blacklist
        $blocked = array_unique(array_merge($reservedPorts, $usedPorts, $extraExclude));

        // 4. Cari port acak yang benar-benar bersih dan aman
        $attempts = 0;
        do {
            $port = rand(5001, 9999);
            $attempts++;
            if ($attempts > 4000) {
                // Jika rentang 5001-9999 mulai penuh, lebarkan ke rentang 10001 - 25000
                $port = rand(10001, 25000);
            }
        } while (in_array($port, $blocked));

        return $port;
    }
}
