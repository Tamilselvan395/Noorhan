<?php

namespace App\Http\Requests\Products;

use App\Enums\Division;
use App\Enums\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public static function rules(?int $productId = null): array
    {
        $values = fn (string $enum) => collect($enum::cases())->map->value->all();

        return [
            'sku' => ['required', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['required', 'string', 'max:160'],
            'division' => ['required', Rule::in($values(Division::class))],
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'brand' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', Rule::in($values(ProductUnit::class))],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}