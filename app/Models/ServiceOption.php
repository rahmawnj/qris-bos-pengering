<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'service_type_id',
    ];

    /**
     * Hubungan ke model Service.
     * Sebuah entri di tabel pivot ini dimiliki oleh sebuah Service.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Hubungan ke model ServiceType.
     * Ini adalah hubungan yang sedang dicari oleh kontroler Anda.
     * Sebuah entri di tabel pivot ini dimiliki oleh sebuah ServiceType.
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}