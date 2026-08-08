<?php

namespace App\Models;

use App\Enums\Division;
use App\Enums\MarketingCampaignStatus;
use App\Enums\MarketingChannel;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingCampaign extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'name', 'division', 'channel', 'status', 'budget', 'spent', 'start_date',
        'end_date', 'utm_campaign', 'goals', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['budget' => 'decimal:2', 'spent' => 'decimal:2', 'start_date' => 'date', 'end_date' => 'date'];
    }

    /* ---- Relations ---- */

    /** Attribution: leads captured with this campaign's UTM tag. */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'utm_campaign', 'utm_campaign');
    }

    public function whatsappCampaigns(): HasMany
    {
        return $this->hasMany(WhatsAppCampaign::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---- Accessors ---- */

    public function division(): Division
    {
        return Division::from($this->division);
    }

    public function channel(): MarketingChannel
    {
        return MarketingChannel::from($this->channel);
    }

    public function status(): MarketingCampaignStatus
    {
        return MarketingCampaignStatus::from($this->status);
    }

    public function budgetUtilization(): float
    {
        $budget = (float) $this->budget;

        return $budget > 0 ? round(((float) $this->spent) / $budget * 100, 1) : 0;
    }
}