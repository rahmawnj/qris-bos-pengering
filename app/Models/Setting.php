<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, $default = null)
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::getValue($key, $default);
    }

    public static function setValue(string $key, $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }
}
