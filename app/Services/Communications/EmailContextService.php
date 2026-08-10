<?php

namespace App\Services\Communications;

use App\Models\Customer;
use App\Models\Lead;

class EmailContextService
{
    public function build(?Customer $customer = null, ?Lead $lead = null, array $extra = []): array
    {
        $context = [
            'company' => ['name' => config('app.name')],
            'date' => now()->format('M d, Y'),
        ];

        if ($customer) {
            $context['customer'] = [
                'name' => $customer->name,
                'email' => (string) $customer->email,
                'company' => (string) $customer->company_name,
            ];

            $context['unsubscribe_url'] = route('unsubscribe', [
                'customer' => $customer->id,
                'token' => sha1($customer->id.$customer->email.config('app.key')),
            ]);
        }

        if ($lead) {
            $context['lead'] = ['name' => $lead->name, 'email' => (string) $lead->email];
        }

        return array_merge($context, $extra);
    }
}