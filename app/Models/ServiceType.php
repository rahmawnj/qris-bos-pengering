<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceType extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    protected $appends = ['slug'];

    public function getSlugAttribute()
    {
        return Str::slug($this->name, '_');
    }

    public function devices()
    {
        return $this->belongsToMany(Device::class, 'device_service_type')
            ->withPivot('price');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_service_type', 'service_type_id', 'service_id');
    }

    public function serviceDevicesOptions()
    {
        return $this->hasMany(ServiceDevicesOption::class);
    }
}