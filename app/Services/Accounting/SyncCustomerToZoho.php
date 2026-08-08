<?php

namespace App\Services\Accounting;

use App\Models\Customer;

class SyncCustomerToZoho
{
    public function __construct(private ZohoBooksClient $client) {}

    public function execute(Customer $customer): string
    {
        $payload = [
            'contact_name' => $customer->displayName(),
            'company_name' => $customer->company_name,
            'contact_type' => 'customer',
            'email' => $customer->email,
            'phone' => $customer->phone,
            'mobile' => $customer->whatsapp ?? $customer->phone,
            'credit_limit' => $customer->credit_limit ? (float) $customer->credit_limit : 0,
            'notes' => $customer->notes,
            'billing_address' => [
                'address' => $customer->address,
                'city' => $customer->city,
                'country' => $customer->country,
            ],
        ];

        $result = $customer->zoho_id
            ? $this->client->put('/contacts/'.$customer->zoho_id, $payload)
            : $this->client->post('/contacts', $payload);

        $zohoId = $customer->zoho_id ?? $result['contact']['contact_id'];

        $customer->update(['zoho_id' => $zohoId]);

        return $zohoId;
    }
}