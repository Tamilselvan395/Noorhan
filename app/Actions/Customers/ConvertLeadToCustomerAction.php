<?php

namespace App\Actions\Customers;

use App\DTOs\Customers\CustomerDTO;
use App\Events\Leads\LeadConverted;
use App\Models\Customer;
use App\Models\Lead;

class ConvertLeadToCustomerAction
{
    public function __construct(private CreateCustomerAction $create) {}

    /** Idempotent: converting twice returns the existing customer. */
    public function execute(Lead $lead): Customer
    {
        if ($lead->customer_id) {
            return Customer::findOrFail($lead->customer_id);
        }

        $customer = $this->create->execute(new CustomerDTO(
            name: $lead->name,
            type: $lead->customer_type ?? 'retail',
            division: $lead->division,
            company_name: $lead->company_name,
            email: $lead->email,
            phone: $lead->phone,
            whatsapp: $lead->phone,
            vehicle_brand_category: $lead->vehicle_brand_category,
            owner_id: $lead->assigned_to ?? $lead->created_by,
            notes: $lead->requirements,
        ));

        $customer->update(['lead_id' => $lead->id]);

        $lead->update(['customer_id' => $customer->id]);
        $lead->logActivity('converted the lead to customer');

        event(new LeadConverted($lead, $customer));

        return $customer;
    }
}