<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommercialDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'customer' => $this->customer?->name,
            'customer_id' => $this->customer_id,
            'division' => $this->division,
            'currency' => $this->currency,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount_amount,
            'tax' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'balance_due' => isset($this->balance_due) ? (float) $this->balance_due : null,
            'due_date' => $this->due_date?->toDateString(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'line_total' => (float) $i->line_total,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}