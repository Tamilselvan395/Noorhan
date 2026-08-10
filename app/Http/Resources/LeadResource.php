<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'division' => $this->division,
            'source' => $this->source,
            'priority' => $this->priority,
            'estimated_value' => (float) $this->estimated_value,
            'assigned_to' => $this->assignee?->name,
            'customer_id' => $this->customer_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}