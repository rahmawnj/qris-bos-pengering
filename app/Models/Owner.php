<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class Owner extends Model
{
    use HasFactory, SoftDeletes;


    protected $guarded = [];
    protected $casts = [
        'receipt_config' => 'array', // Penting: Laravel akan menangani encode/decode JSON secara otomatis
        'withdrawal_fee_charged' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function outlets()
    {
        return $this->hasMany(Outlet::class);
    }
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
    public function owners()
    {
        return $this->hasMany(Owner::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'subscription', 'owner_id', 'member_id')
            ->withPivot('is_verified', 'amount')
            ->withTimestamps();
    }

    // public function transactions()
    // {
    //     return $this->hasManyThrough(
    //         Transaction::class,
    //         Outlet::class,
    //         'id', // Foreign key pada tabel outlets
    //         'outlet_id', // Foreign key pada tabel transactions
    //         'user_id', // Lokal key pada tabel owners
    //         'id' // Lokal key pada tabel outlets
    //     );
    // }

    public function devices()
    {
        return $this->hasManyThrough(
            Device::class,
            Outlet::class,
            'owner_id', // Foreign key on outlets table...
            'outlet_id', // Foreign key on devices table...
            'id', // Local key on owner table...
            'id'
        )->select([
            'devices.*', // Ambil semua kolom dari tabel devices
            'outlets.id as outlet_id',
            // Beri alias untuk id dari tabel outlets
            'outlets.outlet_name as outlet_name',
        ]);
    }

    public function cashiers()
    {
        return $this->hasManyThrough(
            Cashier::class, // Target model
            Outlet::class,  // Perantara
            'owner_id',     // foreign key di table outlets (kunci outlet ke owner)
            'outlet_id',    // foreign key di table cashiers (kunci cashier ke outlet)
            'id',           // primary key di owners
            'id'            // primary key di outlets
        )->select([
            'cashiers.*',
            'outlets.id as outlet_id',
            'outlets.outlet_name as outlet_name'
        ]);
    }


    public function addons()
    {
        return $this->hasManyThrough(
            Addon::class,
            Outlet::class,
            'owner_id',
            'outlet_id',
            'id',
            'id'
        )->addSelect([
            'addons.*',
            DB::raw('outlets.outlet_name as outlet_name'),
        ]);
    }
}