<?php

namespace App\Http\Requests\Customers;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\Division;
use App\Enums\VehicleBrandCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'type' => ['required', Rule::in($values(CustomerType::class))],
            'status' => ['required', Rule::in($values(CustomerStatus::class))],
            'division' => ['required', Rule::in($values(Division::class))],
            'vehicle_brand_category' => ['nullable', Rule::in($values(VehicleBrandCategory::class))],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ];
    }
}