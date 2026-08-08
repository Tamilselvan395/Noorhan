<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = [];

    /**
     * Read a setting with graceful fallback: DB → cache → default.
     * Key format: "group.key" (e.g. "defaults.currency").
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        [$group, $k] = explode('.', $key, 2);

        return Cache::remember("setting.{$group}.{$k}", 300, function () use ($group, $k, $default) {
            $row = static::query()->where('group', $group)->where('key', $k)->first();

            return $row ? json_decode($row->value, true) : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        [$group, $k] = explode('.', $key, 2);

        static::updateOrCreate(
            ['group' => $group, 'key' => $k],
            ['value' => json_encode($value)],
        );

        Cache::forget("setting.{$group}.{$k}");
    }
}