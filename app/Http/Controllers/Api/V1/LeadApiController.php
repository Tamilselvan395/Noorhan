<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Leads\CreateLeadAction;
use App\DTOs\Leads\LeadDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeadResource;
use App\Http\Requests\Leads\StoreLeadRequest;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function index(Request $request)
    {
        $leads = Lead::query()->with('assignee')
            ->when($request->query('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->query('division'), fn ($q) => $q->where('division', $request->query('division')))
            ->when($request->query('source'), fn ($q) => $q->where('source', $request->query('source')))
            ->search($request->query('search'))
            ->latest()
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return LeadResource::collection($leads);
    }

    public function store(StoreLeadRequest $request, CreateLeadAction $action)
    {
        $data = $request->validated();

        $lead = $action->execute(new LeadDTO(
            name: $data['name'],
            division: $data['division'],
            source: $data['source'] ?? 'manual',
            priority: $data['priority'] ?? 'medium',
            company_name: $data['company_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            customer_type: $data['customer_type'] ?? null,
            vehicle_brand_category: $data['vehicle_brand_category'] ?? null,
            subject: $data['subject'] ?? null,
            requirements: $data['requirements'] ?? null,
            estimated_value: isset($data['estimated_value']) ? (float) $data['estimated_value'] : null,
        ), $request->user());

        return (new LeadResource($lead))->response()->setStatusCode(201);
    }

    public function show(Lead $lead)
    {
        return new LeadResource($lead->load(['assignee', 'activities' => fn ($q) => $q->latest()->limit(10)]));
    }
}