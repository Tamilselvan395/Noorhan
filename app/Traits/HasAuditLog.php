<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Automatically writes an immutable audit trail for every
 * created / updated / deleted event on the model.
 *
 * Models may define `public array $auditExclude = [...]` to hide
 * sensitive attributes from the audit trail.
 *
 * @mixin Model
 */
trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        static::created(function (Model $model): void {
            static::writeAudit($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model): void {
            $changes = $model->getChanges();

            $old = [];
            $new = [];

            foreach (array_keys($changes) as $attribute) {
                $old[$attribute] = $model->getOriginal($attribute);
                $new[$attribute] = $model->getAttribute($attribute);
            }

            static::writeAudit($model, 'updated', $old, $new);
        });

        static::deleted(function (Model $model): void {
            static::writeAudit($model, 'deleted', $model->getAttributes(), null);
        });
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Persist the audit entry, stripping sensitive attributes
     * (passwords, tokens, 2FA secrets) so they are never stored.
     */
    protected static function writeAudit(Model $model, string $event, ?array $old, ?array $new): void
    {
        $exclude = property_exists($model, 'auditExclude')
            ? array_flip($model->auditExclude)
            : array_flip(['password', 'remember_token']);

        $old = $old ? array_diff_key($old, $exclude) : null;
        $new = $new ? array_diff_key($new, $exclude) : null;

        AuditLog::create([
            'user_id'        => auth()->id(),
            'auditable_type' => $model->getMorphClass(),
            'auditable_id'   => $model->getKey(),
            'event'          => $event,
            'old_values'     => $old,
            'new_values'     => $new,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}