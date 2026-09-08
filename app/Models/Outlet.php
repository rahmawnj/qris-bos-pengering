<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Gunakan class Authenticatable
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'owner_id',
        'code',
        'address',
        'image',
        'status',
        'qris_billing_enabled',
        'qris_billing_due_date',
        'timezone',
        'outlet_name',
        'phone_number',
    ];

    protected $casts = [
        'status' => 'boolean',
        'qris_billing_enabled' => 'boolean',
        'qris_billing_due_date' => 'date',
    ];


    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }
    
    public function cashiers()
    {
        return $this->hasMany(Cashier::class);
    }
    
    public function addons()
    {
        return $this->hasMany(Addon::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function qrisBillingPayments()
    {
        return $this->hasMany(QrisBillingPayment::class);
    }

    public function getRoleAttribute()
    {
        return 'outlet';
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function getQrisBillingAmountAttribute(): int
    {
        return $this->qrisBillingUnitAmount();
    }

    public function getQrisBillingDayAttribute(): ?int
    {
        return $this->qris_billing_due_date ? (int) $this->qris_billing_due_date->format('d') : null;
    }

    public function getQrisBillingPeriodAttribute(): string
    {
        return now()->format('Y-m');
    }

    public function getQrisBillingDueDateAttribute(): ?Carbon
    {
        $value = $this->attributes['qris_billing_due_date'] ?? null;

        return $value ? Carbon::parse($value)->startOfDay() : null;
    }

    public function getQrisBillingPaidCurrentPeriodAttribute(): bool
    {
        return !$this->has_overdue_billing;
    }

    public function getQrisBillingDueCurrentPeriodAttribute(): bool
    {
        return $this->has_overdue_billing;
    }

    public function getHasOverdueBillingAttribute(): bool
    {
        if (!$this->qris_billing_enabled) {
            return false;
        }

        $dueDate = $this->qris_billing_due_date;

        return !$dueDate || $dueDate->copy()->endOfDay()->lessThan(now());
    }

    public function qrisBillingUnitAmount(): int
    {
        if (!$this->qris_billing_enabled) {
            return 0;
        }

        return $this->qrisBillingAmountBreakdown()['total'];
    }

    public function qrisBillingAmountBreakdown(): array
    {
        $deviceCount = $this->devices_count ?? ($this->relationLoaded('devices') ? $this->devices->count() : $this->devices()->count());
        $pricePerDevice = Setting::getInt('qris_billing_price_per_device', 100000);
        $maxFeeMode = Setting::getValue('qris_billing_max_fee_mode', 'capped');
        $maxFee = Setting::getInt('qris_billing_max_fee', 500000);
        $subtotal = $deviceCount * $pricePerDevice;
        $isUnlimited = $maxFeeMode === 'unlimited';
        $total = $isUnlimited ? $subtotal : min($subtotal, $maxFee);

        return [
            'device_count' => $deviceCount,
            'price_per_device' => $pricePerDevice,
            'subtotal' => $subtotal,
            'max_fee_mode' => $maxFeeMode,
            'max_fee' => $maxFee,
            'discount_by_cap' => $isUnlimited ? 0 : max(0, $subtotal - $maxFee),
            'total' => $total,
        ];
    }

    public function qrisBillingDueDateForPeriod(?string $period): ?Carbon
    {
        if (!$period) {
            return null;
        }

        return $this->qris_billing_enabled ? $this->qris_billing_due_date : null;
    }

    public function qrisBillingDuePeriodsUntil(?Carbon $endDate = null): array
    {
        if (!$this->qris_billing_enabled || !$this->qris_billing_due_date) {
            return [];
        }

        $dueDate = $this->qris_billing_due_date;
        if (!$dueDate || $dueDate->copy()->endOfDay()->greaterThanOrEqualTo($endDate ?? now())) {
            return [];
        }

        return [[
            'period' => $dueDate->format('Y-m'),
            'target_date' => $dueDate->format('Y-m-d'),
            'due_date' => $dueDate,
            'amount' => $this->qris_billing_amount,
        ]];
    }

    public function qrisBillingUnpaidPeriods(?Carbon $endDate = null): array
    {
        if (!$this->qris_billing_enabled) {
            return [];
        }

        $pendingPayment = $this->latestQrisBillingPayment('pending');
        if ($pendingPayment) {
            return [[
                'period' => $pendingPayment->period_end?->format('Y-m') ?? now()->format('Y-m'),
                'target_date' => $pendingPayment->period_end?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'due_date' => $pendingPayment->period_end ?? now()->copy()->addMonthNoOverflow()->startOfDay(),
                'amount' => $pendingPayment->amount,
                'payment' => $pendingPayment,
                'status' => 'pending',
            ]];
        }

        if (!$this->has_overdue_billing) {
            return [];
        }

        $renewalStart = now()->copy()->startOfDay();
        $renewalEnd = $this->qris_billing_due_date ?: now()->copy()->addMonthNoOverflow()->startOfDay();

        return [[
            'period' => $renewalStart->format('Y-m'),
            'target_date' => $renewalStart->format('Y-m-d'),
            'due_date' => $renewalEnd,
            'amount' => $this->qris_billing_amount,
            'payment' => null,
            'status' => 'due',
        ]];
    }

    public function qrisBillingUnpaidSummary(?Carbon $endDate = null): array
    {
        $periods = $this->qrisBillingUnpaidPeriods($endDate);
        $amount = collect($periods)->sum('amount');
        $pendingCount = collect($periods)->where('status', 'pending')->count();

        return [
            'count' => count($periods),
            'amount' => $amount,
            'pending_count' => $pendingCount,
            'due_count' => count($periods) - $pendingCount,
            'oldest_period' => $periods[0]['period'] ?? null,
            'latest_period' => !empty($periods) ? $periods[count($periods) - 1]['period'] : null,
            'oldest_due_date' => $periods[0]['due_date'] ?? null,
            'periods' => $periods,
        ];
    }

    public function getQrisBillingActiveUntilAttribute(): ?Carbon
    {
        return $this->qris_billing_due_date;
    }

    public function latestQrisBillingPayment(?string $status = null): ?QrisBillingPayment
    {
        $payments = $this->relationLoaded('qrisBillingPayments')
            ? $this->qrisBillingPayments
            : $this->qrisBillingPayments()->get();

        return $payments
            ->when($status, fn ($collection) => $collection->where('status', $status))
            ->sortByDesc(fn (QrisBillingPayment $payment) => optional($payment->period_end)->timestamp ?? 0)
            ->first();
    }

    public function qrisBillingPaymentForTargetDate(string $targetDate): ?QrisBillingPayment
    {
        $targetPeriod = substr($targetDate, 0, 7); // YYYY-MM

        if ($this->relationLoaded('qrisBillingPayments')) {
            return $this->qrisBillingPayments
                ->first(function (QrisBillingPayment $payment) use ($targetPeriod) {
                    return $payment->period_start && $payment->period_end
                        && $payment->period_start->format('Y-m') <= $targetPeriod
                        && $payment->period_end->format('Y-m') >= $targetPeriod;
                });
        }

        return $this->qrisBillingPayments()
            ->whereRaw("DATE_FORMAT(period_start, '%Y-%m') <= ?", [$targetPeriod])
            ->whereRaw("DATE_FORMAT(period_end, '%Y-%m') >= ?", [$targetPeriod])
            ->orderByDesc('id')
            ->first();
    }
}
