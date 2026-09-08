<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrisBillingPayment extends Model
{
    protected $fillable = [
        'outlet_id',
        'period_start',
        'period_end',
        'amount',
        'status',
        'paid_at',
        'proof_of_payment',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
