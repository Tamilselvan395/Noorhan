<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Document extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size;

        return match (true) {
            $bytes >= 1048576 => round($bytes / 1048576, 1).' MB',
            $bytes >= 1024 => round($bytes / 1024, 1).' KB',
            default => $bytes.' B',
        };
    }

    public function scopeExpiringSoon(Builder $query, int $days = 14): Builder
    {
        return $query
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function isExpiringSoon(int $days = 14): bool
    {
        return $this->expires_at !== null
            && ! $this->isExpired()
            && $this->expires_at->lte(now()->addDays($days));
    }

    public function category(): DocumentCategory
    {
        return DocumentCategory::from($this->category);
    }
    
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}