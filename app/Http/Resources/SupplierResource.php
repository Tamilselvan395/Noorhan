<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'division' => $this->division,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
            'payment_terms' => $this->payment_terms,
            'rating' => $this->overallRating(),
            'status' => $this->status,
        ];
    }
}