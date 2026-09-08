<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'outlet_id',
        'device_status',
        'name',
        'timezone',
        'bypass_activation',
        'bypass_note'
    ];

    protected $casts = [
        'bypass_activation' => 'datetime', 
    ];

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'device_service_type')
            ->withPivot('price');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public static function generateUniqueCode(int $deviceId): string
    {
        $idString = (string) $deviceId;
        $remainingLength = 6 - strlen($idString);

        if ($remainingLength < 0) {
            throw new \InvalidArgumentException("ID terlalu panjang.");
        }

        do {
            $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $remainingLength));
            $code = 'DEV-' . $random . $idString;
        } while (static::where('code', $code)->exists());

        return $code;
    }
}