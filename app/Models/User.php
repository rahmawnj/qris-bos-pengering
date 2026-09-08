<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->hasOne(Outlet::class);
    }

    public function owner()
    {
        return $this->hasOne(Owner::class);
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }

    public function cashier()
    {
        return $this->hasOne(Cashier::class);
    }

    public function hasPermission($permission)
    {
        if (!$this->permissions) {
            return false;
        }
        $permissions = explode(',', $this->permissions);
        return in_array($permission, $permissions);
    }

    public function getDataRootEntity()
    {
        if ($this->role == 'owner') {
            return $this->owner; // Mengembalikan instance Owner
        } elseif ($this->role == 'cashier') {
            // Cashier terkait ke Outlet, jadi Outlet adalah root entity untuk data mereka
            $cashier = $this->cashier;
            return $cashier ? $cashier->outlet : null; // Mengembalikan instance Outlet
        }
        return null; // Untuk admin atau member, biarkan helper yang menangani
    }
}