<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'amount',
        'requested_amount',
        'status',
        'notes',
        'approved_at',
        'bank_name',
        'bank_account_number',
        'bank_account_holder_name',
        'amount_before_fee',
        'withdrawal_fee',
        'amount_after_fee',
    ];
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}