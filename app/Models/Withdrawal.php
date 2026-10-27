<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'withdrawal_id',
        'merchant_id',
        'amount_requested',
        'admin_fee',
        'amount_transferred',
        'bank_target',
        'account_target',
        'holder_target',
        'status',
        'proof_receipt',
        'notes',
        'is_auto',
        'processed_at',
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'amount_transferred' => 'decimal:2',
        'is_auto' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($wd) {
            if (empty($wd->withdrawal_id)) {
                $wd->withdrawal_id = 'WD-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });
    }
}
