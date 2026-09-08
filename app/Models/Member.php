<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function owners()
    {
        return $this->belongsToMany(Owner::class, 'subscription', 'member_id', 'owner_id')
                    ->withPivot('is_verified', 'amount')
                    ->withTimestamps();
    }
}