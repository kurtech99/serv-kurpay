<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trx_id',
        'package_type',
        'amount',
        'status',
        'proof',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
