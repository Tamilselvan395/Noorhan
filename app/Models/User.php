<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Concerns\HasNotificationPreferences;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasActivityLog, HasAuditLog, HasNotificationPreferences, HasRoles;

    /** Attributes the audit trail must NEVER store. */
    public array $auditExclude = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected $fillable = [
        'name', 'email', 'password',
        'last_login_at', 'last_login_ip', 'failed_login_attempts', 'locked_until','notification_preferences', 'theme', 'customer_id',
        'password_changed_at', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'last_login_at'           => 'datetime',
            'locked_until'            => 'datetime',
            'password_changed_at'     => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password'                => 'hashed',
            'notification_preferences' => 'array',
            'is_active' => 'bool',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }
}