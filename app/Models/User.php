<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function merchants()
    {
        return $this->hasMany(Merchant::class);
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class);
    }

    public function activeMerchant(): ?Merchant
    {
        $activeId = session('active_merchant_id');
        if ($activeId) {
            $m = $this->merchants()->find($activeId);
            if ($m) return $m;
        }
        $m = $this->merchants()->first();
        if ($m) {
            session(['active_merchant_id' => $m->id]);
            return $m;
        }
        return null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }
}
