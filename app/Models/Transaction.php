<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'trx_id',
        'merchant_id',
        'order_id',
        'service_name',
        'customer_name',
        'customer_phone',
        'amount',
        'customer_fee',
        'unique_code',
        'total_amount',
        'merchant_net',
        'status',
        'gateway_mode',
        'is_deposit_topup',
        'deposit_fee_deducted',
        'callback_url',
        'callback_status',
        'callback_response',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'customer_fee' => 'decimal:2',
        'unique_code' => 'integer',
        'total_amount' => 'decimal:2',
        'merchant_net' => 'decimal:2',
        'deposit_fee_deducted' => 'decimal:2',
        'is_deposit_topup' => 'boolean',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($trx) {
            if (empty($trx->trx_id)) {
                $trx->trx_id = 'KP-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
            if (empty($trx->expired_at)) {
                $trx->expired_at = now()->addHours(24);
            }
        });
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'completed']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
