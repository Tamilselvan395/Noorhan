<?php

namespace App\Http\Requests\Capture;

use Illuminate\Foundation\Http\FormRequest;

class GenericApiLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'division' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:200'],
            'requirements' => ['nullable', 'string'],
            'vehicle_brand_category' => ['nullable', 'string'],
            'customer_type' => ['nullable', 'string'],
        ];
    }
}