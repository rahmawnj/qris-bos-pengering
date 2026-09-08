<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Constants\OrderStatus;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i:s',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function memberTransaction()
    {
        return $this->hasOne(MemberTransactionDetail::class);
    }

    public function manualTransaction()
    {
        return $this->hasOne(ManualTransactionDetail::class);
    }

    public function qrisTransaction()
    {
        return $this->hasOne(QrisTransactionDetail::class);
    }

    public function getQrisProviderLabelAttribute(): string
    {
        if ($this->type !== 'qris') {
            return '-';
        }

        return strtoupper((string) ($this->qrisTransaction?->payment_provider ?: 'xendit'));
    }

    public function deviceTransactions()
    {
        return $this->hasMany(DeviceTransaction::class);
    }

    public function scopePendingServiceOrders(Builder $query, ?array $finalStatuses = null): Builder
    {
        $finalStatuses ??= OrderStatus::finalStatuses();

        return $query->where('type', 'manual')
            ->whereHas('manualTransaction', function (Builder $manualQuery) use ($finalStatuses) {
                $manualQuery->whereNotIn('progress', $finalStatuses);
            });
    }

    public function scopeUnfinishedServiceOrders($query)
    {
        $now = Carbon::now();

        return $query->where('type', 'manual')
            ->where(function ($q) use ($now) {
                $q->whereDoesntHave('deviceTransactions')
                    ->orWhereHas('deviceTransactions', function ($qInner) use ($now) {
                        $qInner->whereNull('activated_at')
                            ->orWhere('activated_at', '>', $now->subHours(24));
                    });
            });
    }
}
