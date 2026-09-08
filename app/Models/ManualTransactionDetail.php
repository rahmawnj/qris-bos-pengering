<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualTransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'notes',
        'cashier_name',
        'service_id',
        'payment_method',
        'addons',
        'service_price',
        'estimated_completion_at',
        'customer_name',
        'customer_phone_number',
        'progress',
        'quantity',
        'unit'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    protected $casts = [
        'addons' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
