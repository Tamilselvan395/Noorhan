<?php

namespace App\Http\Requests\Leads;

use App\Enums\CustomerType;
use App\Enums\Division;
use App\Enums\LeadPriority;
use App\Enums\LeadSource;
use App\Enums\VehicleBrandCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public static function rules(): array
    {
        $values = fn (string $enum) => collect($enum::cases())->map->value->all();

        return [
            'name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'division' => ['required', Rule::in($values(Division::class))],
            'source' => ['required', Rule::in($values(LeadSource::class))],
            'customer_type' => ['nullable', Rule::in($values(CustomerType::class))],
            'vehicle_brand_category' => ['nullable', Rule::in($values(VehicleBrandCategory::class))],
            'priority' => ['required', Rule::in($values(LeadPriority::class))],
            'subject' => ['nullable', 'string', 'max:200'],
            'requirements' => ['nullable', 'string'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'next_follow_up_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}