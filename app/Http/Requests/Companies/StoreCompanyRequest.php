<?php

namespace App\Http\Requests\Companies;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\Division;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
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
            'trade_license_no' => ['nullable', 'string', 'max:60'],
            'tax_number' => ['nullable', 'string', 'max:60'],
            'type' => ['required', Rule::in($values(CustomerType::class))],
            'status' => ['required', Rule::in($values(CustomerStatus::class))],
            'division' => ['required', Rule::in($values(Division::class))],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'string', 'max:200'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}