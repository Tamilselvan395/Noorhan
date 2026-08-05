<?php

namespace App\Livewire\Customers;

use App\Actions\Customers\CreateCustomerAction;
use App\Actions\Customers\UpdateCustomerAction;
use App\DTOs\Customers\CustomerDTO;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CustomerForm extends Component
{
    public bool $open = false;

    public ?int $customerId = null;
    public ?int $company_id = null;

    public string $name = '';
    public string $company_name = '';
    public string $email = '';
    public string $phone = '';
    public string $whatsapp = '';
    public string $type = 'retail';
    public string $status = 'active';
    public string $division = 'automotive';
    public string $vehicle_brand_category = '';
    public string $address = '';
    public string $city = '';
    public string $country = '';
    public string $credit_limit = '';
    public string $notes = '';
    public ?int $owner_id = null;

    #[On('open-customer-form')]
    public function openForm(?int $customerId = null, ?int $companyId = null): void
    {
        $this->resetValidation();

        $this->customerId = $customerId;

        if ($customerId) {
            $customer = Customer::findOrFail($customerId);

            Gate::authorize('update', $customer);

            foreach ([
                'name',
                'company_name',
                'email',
                'phone',
                'whatsapp',
                'type',
                'status',
                'division',
                'vehicle_brand_category',
                'address',
                'city',
                'country',
                'notes',
                'owner_id',
            ] as $field) {
                $this->{$field} = $customer->{$field} ?? '';
            }

            $this->company_id = $customer->company_id;
            $this->credit_limit = $customer->credit_limit
                ? (string) $customer->credit_limit
                : '';
        } else {
            Gate::authorize('create', Customer::class);

            $this->reset([
                'name',
                'company_name',
                'email',
                'phone',
                'whatsapp',
                'address',
                'city',
                'country',
                'credit_limit',
                'notes',
                'owner_id',
                'company_id',
            ]);

            [$this->type, $this->status, $this->division] = [
                'retail',
                'active',
                'automotive',
            ];

            $this->company_id = $companyId;
        }

        $this->open = true;
    }

    public function save(
        CreateCustomerAction $create,
        UpdateCustomerAction $update
    ): void {
        $data = $this->validate(StoreCustomerRequest::rules());

        foreach ([
            'company_name',
            'email',
            'phone',
            'whatsapp',
            'vehicle_brand_category',
            'address',
            'city',
            'country',
            'credit_limit',
            'notes',
            'owner_id',
            'company_id',
        ] as $nullable) {
            $data[$nullable] = $data[$nullable] ?: null;
        }

        $dto = CustomerDTO::fromArray($data);

        $this->customerId
            ? $update->execute(Customer::findOrFail($this->customerId), $dto)
            : $create->execute($dto, auth()->user());

        $this->open = false;

        $this->dispatch('customer-saved');
        $this->dispatch(
            'notify',
            message: 'Customer saved.',
            type: 'success'
        );
    }

    public function render(): View
    {
        return view('livewire.customers.customer-form', [
            'users' => User::orderBy('name')->get(),
            'companies' => Company::orderBy('name')->get(),
        ]);
    }
} 