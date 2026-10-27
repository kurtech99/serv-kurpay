<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpnTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'trx_id',
        'user_id',
        'vpn_account_id',
        'amount',
        'duration_months',
        'payment_method',
        'status',
        'proof_of_payment',
        'checkout_url',
        'qr_url',
        'qr_expired_at',
        'total_amount',
        'requested_username',
        'requested_password',
        'requested_ports',
        'local_ports_json',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'duration_months' => 'integer',
        'requested_ports' => 'integer',
        'local_ports_json' => 'array',
    ];

    protected $appends = ['is_extension'];

    public function getIsExtensionAttribute()
    {
        return !empty($this->vpn_account_id) || str_starts_with($this->trx_id ?? '', 'VPN-EXT-');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vpnAccount()
    {
        return $this->belongsTo(VpnAccount::class, 'vpn_account_id');
    }
}