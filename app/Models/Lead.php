<?php

namespace App\Models;

use App\Enums\Division;
use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Enums\LeadSource;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Document;

class Lead extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'name', 'company_name', 'email', 'phone', 'division', 'source', 'customer_type',
        'vehicle_brand_category', 'status', 'priority', 'subject', 'requirements',
        'estimated_value', 'score', 'needs_triage', 'assigned_to', 'created_by',
        'customer_id', 'last_contacted_at', 'next_follow_up_at', 'closed_at', 'lost_reason',
        'utm_source', 'utm_medium', 'utm_campaign', 'landing_url', 'business_card_path',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value'     => 'decimal:2',
            'needs_triage'        => 'bool',
            'last_contacted_at'   => 'datetime',
            'next_follow_up_at'   => 'datetime',
            'closed_at'           => 'datetime',
        ];
    }

    /* ---- Relations ---- */

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---- Enum accessors ---- */

    public function status(): LeadStatus
    {
        return LeadStatus::from($this->status);
    }

    public function division(): Division
    {
        return Division::from($this->division);
    }

    public function priority(): LeadPriority
    {
        return LeadPriority::from($this->priority);
    }

    public function source(): LeadSource
    {
        return LeadSource::from($this->source);
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(Communication::class, 'communicable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /* ---- Scopes ---- */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [LeadStatus::Won->value, LeadStatus::Lost->value]);
    }

    public function scopeStatus(Builder $query, LeadStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeDivision(Builder $query, Division $division): Builder
    {
        return $query->where('division', $division->value);
    }

    public function scopeTriage(Builder $query): Builder
    {
        return $query->where('needs_triage', true);
    }

    public function scopeFollowUpDue(Builder $query): Builder
    {
        return $query->open()->where('next_follow_up_at', '<=', now());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('subject', 'like', "%{$term}%");
        }));
    }

    /* ---- Helpers ---- */

    public function isOpen(): bool
    {
        return ! $this->status()->isClosed();
    }

    public function addNote(string $note): void
    {
        $this->logActivity('added a note', ['note' => $note]);
    }
}