<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZohoConnection extends Model
{
    protected $guarded = [];

    protected $casts = ['settings' => 'array', 'token_expires_at' => 'datetime'];

    public function setting(string $key, bool $default = true): bool
    {
        return (bool) ($this->settings[$key] ?? $default);
    }
}