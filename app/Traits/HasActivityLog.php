<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Provides polymorphic activity logging for any Eloquent model.
 *
 * This trait is intended to be used exclusively inside Eloquent models.
 * The @mixin annotation informs static analysis (Intelephense) that
 * $this is an Eloquent Model, resolving P1013 warnings.
 *
 * @mixin Model
 */
trait HasActivityLog
{
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function logActivity(string $description, ?array $properties = null): Activity
    {
        return $this->activities()->create([
            'user_id'     => auth()->id(),
            'description' => $description,
            // Passed as array — the Activity model casts it to JSON automatically.
            'properties'  => $properties,
        ]);
    }
}