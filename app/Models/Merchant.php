<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'merchant_code',
        'business_name',
        'api_key',
        'secret_key',
        'balance',
        'webhook_url',
        'bank_name',
        'bank_account',
        'bank_holder',
        'auto_withdraw_enabled',
        'auto_withdraw_target',
        'auto_withdraw_weekend',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'auto_withdraw_enabled' => 'boolean',
        'auto_withdraw_target' => 'decimal:2',
        'auto_withdraw_weekend' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($merchant) {
            if (empty($merchant->merchant_code)) {
                $merchant->merchant_code = 'KP-' . strtoupper(Str::random(6));
            }
            if (empty($merchant->api_key)) {
                $merchant->api_key = 'kp_live_' . Str::random(32);
            }
            if (empty($merchant->secret_key)) {
                $merchant->secret_key = 'kp_sec_' . Str::random(32);
            }
        });
    }

    public function addBalance($amount)
    {
        $this->balance += $amount;
        $this->save();
    }

    public function deductBalance($amount)
    {
        $this->balance -= $amount;
        $this->save();
    }
}
