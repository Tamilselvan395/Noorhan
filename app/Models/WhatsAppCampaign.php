<?php

namespace App\Models;

use App\Enums\CustomerType;
use App\Enums\Division;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class WhatsAppCampaign extends Model
{
    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'name', 'audience_type', 'audience_value', 'message_type', 'template_name',
        'body', 'media_url', 'media_kind', 'status', 'scheduled_at', 'sent_count', 'marketing_campaign_id',
        'failed_count', 'created_by',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppCampaignRecipient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function marketingCampaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    /** Resolve the target audience at send time. */
    public function audience(): Collection
    {
        $query = Customer::query()
            ->active()
            ->where('whatsapp_opted_out', false)
            ->where(fn ($q) => $q->whereNotNull('whatsapp')->where('whatsapp', '!=', ''));

        return match ($this->audience_type) {
            'division' => $query->where('division', $this->audience_value)->get(),
            'type' => $query->where('type', $this->audience_value)->get(),
            default => $query->get(),
        };
    }
}