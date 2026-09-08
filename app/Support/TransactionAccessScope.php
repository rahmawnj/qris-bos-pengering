<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionAccessScope
{
    public static function isAdminContext(): bool
    {
        return !session('impersonating') &&
            (Auth::guard('admin_config')->check() || (Auth::check() && Auth::user()->role === 'admin'));
    }

    public static function apply(Builder $query): Builder
    {
        if (self::isAdminContext()) {
            return $query;
        }

        $user = Auth::user();

        if (!$user) {
            return self::empty($query);
        }

        if ($user->role === 'owner') {
            $user->loadMissing('owner');
            $ownerId = optional($user->owner)->id;

            return $ownerId ? $query->where('owner_id', $ownerId) : self::empty($query);
        }

        if ($user->role === 'cashier') {
            $user->loadMissing('cashier');
            $outletId = optional($user->cashier)->outlet_id;

            return $outletId ? $query->where('outlet_id', $outletId) : self::empty($query);
        }

        return self::empty($query);
    }

    protected static function empty(Builder $query): Builder
    {
        return $query->whereRaw('1 = 0');
    }
}
