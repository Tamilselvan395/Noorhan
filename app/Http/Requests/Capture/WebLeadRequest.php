<?php

namespace App\Http\Requests\Capture;

use App\Enums\Division;
use App\Enums\VehicleBrandCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $values = fn (string $enum) => collect($enum::cases())->map->value->all();

        return [
            'name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'division' => ['required', Rule::in($values(Division::class))],
            'vehicle_brand_category' => ['nullable', Rule::in($values(VehicleBrandCategory::class))],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:5000'],
            'utm_source' => ['nullable', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'string', 'max:160'],
            'landing_url' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'string'], // honeypot
        ];
    }
}