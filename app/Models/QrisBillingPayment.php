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
        'payment_method',
        'payment_provider',
        'gateway_order_id',
        'va_bank',
        'va_number',
        'expires_at',
        'gateway_transaction_id',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
