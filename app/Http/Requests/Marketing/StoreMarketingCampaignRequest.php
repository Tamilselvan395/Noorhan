<?php

namespace App\Http\Requests\Marketing;

use App\Enums\Division;
use App\Enums\MarketingCampaignStatus;
use App\Enums\MarketingChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarketingCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public static function rules(): array
    {
        $values = fn (string $enum) => collect($enum::cases())->map->value->all();

        return [
            'name' => ['required', 'string', 'max:160'],
            'division' => ['required', Rule::in($values(Division::class))],
            'channel' => ['required', Rule::in($values(MarketingChannel::class))],
            'status' => ['required', Rule::in($values(MarketingCampaignStatus::class))],
            'utm_campaign' => ['required', 'string', 'max:160'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'spent' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'goals' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}