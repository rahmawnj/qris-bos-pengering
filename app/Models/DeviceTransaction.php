<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeviceTransaction extends Model
{
    use HasFactory;

    protected $table = 'device_transactions'; // optional, karena Laravel otomatis infer ini dari nama model

    protected $fillable = [
        'transaction_id',
        'device_code',
        'service_type',
        'activated_at',
        'status',
        'bypass_activation'
    ];


    protected $casts = [
        'activated_at' => 'datetime',
        'bypass_activation' => 'datetime',

    ];

    // Relasi ke transaksi
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_code', 'code');
    }
}