<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'service_options', 'service_id', 'service_type_id');
    }
    
    public function serviceOptions()
    {
        return $this->hasMany(ServiceOption::class);
    }
}