<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrisTransactionDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'payment_provider',
        'gateway_reference',
        'payment_url',
        'qr_code_image',
        'device_code',
        'service_type',
        'proof_of_payment',
        'bypass_status',
        'bypass_activation',
    ];

    /**
     * Relasi ke model Transaction.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
