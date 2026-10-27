<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MikrotikRouter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'host',
        'port',
        'username',
        'password',
        'is_primary',
        'software_id'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'port' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
