<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'division' => $this->division,
            'category' => $this->category?->name,
            'brand' => $this->brand,
            'unit' => $this->unit,
            'cost_price' => (float) $this->cost_price,
            'sale_price' => (float) $this->sale_price,
            'tax_rate' => (float) $this->tax_rate,
            'attributes' => $this->attributes,
            'is_active' => $this->is_active,
        ];
    }
}