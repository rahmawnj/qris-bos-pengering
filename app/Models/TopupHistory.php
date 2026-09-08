<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopupHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'outlet_id',
        'owner_id',
        'amount',
        'time',
        'timezone',
        'cashier_name',
        'notes',
    ];

    protected $dates = [
        'topup_time',
    ];

    /**
     * Relasi ke model Member.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Relasi ke model Outlet.
     */
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
