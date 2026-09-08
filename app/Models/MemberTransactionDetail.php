<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberTransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'member_id',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
