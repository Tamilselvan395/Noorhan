<?php

namespace App\Http\Requests\Suppliers;

use App\Enums\CustomerStatus;
use App\Enums\Division;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
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
            'status' => ['required', Rule::in($values(CustomerStatus::class))],
            'currency' => ['required', 'string', 'max:10'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'string', 'max:200'],
            'country' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:500'],
            'payment_terms' => ['nullable', 'string', 'max:120'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}