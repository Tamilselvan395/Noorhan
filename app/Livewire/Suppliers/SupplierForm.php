<?php

namespace App\Livewire\Suppliers;

use App\Actions\Suppliers\CreateSupplierAction;
use App\Actions\Suppliers\UpdateSupplierAction;
use App\DTOs\Suppliers\SupplierDTO;
use App\Http\Requests\Suppliers\StoreSupplierRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class SupplierForm extends Component
{
    public bool $open = false;
    public ?int $supplierId = null;

    public string $name = '';
    public string $division = 'automotive';
    public string $status = 'active';
    public string $currency = 'USD';
    public string $contact_person = '';
    public string $email = '';
    public string $phone = '';
    public string $whatsapp = '';
    public string $website = '';
    public string $country = '';
    public string $city = '';
    public string $address = '';
    public string $payment_terms = '';
    public string $notes = '';
    public ?int $owner_id = null;

    #[On('open-supplier-form')]
    public function openForm(?int $supplierId = null): void
    {
        $this->resetValidation();
        $this->supplierId = $supplierId;

        if ($supplierId) {
            $supplier = Supplier::findOrFail($supplierId);
            Gate::authorize('update', $supplier);

            foreach (['name','division','status','currency','contact_person','email','phone','whatsapp','website','country','city','address','payment_terms','notes','owner_id'] as $field) {
                $this->{$field} = $supplier->{$field} ?? '';
            }
        } else {
            Gate::authorize('create', Supplier::class);
            $this->reset(['name','contact_person','email','phone','whatsapp','website','country','city','address','payment_terms','notes','owner_id']);
            [$this->division, $this->status, $this->currency] = ['automotive', 'active', 'USD'];
        }

        $this->open = true;
    }

    public function save(CreateSupplierAction $create, UpdateSupplierAction $update): void
    {
        $data = $this->validate(StoreSupplierRequest::rules());

        foreach (['contact_person','email','phone','whatsapp','website','country','city','address','payment_terms','notes','owner_id'] as $nullable) {
            $data[$nullable] = $data[$nullable] ?: null;
        }

        $dto = SupplierDTO::fromArray($data);

        $this->supplierId
            ? $update->execute(Supplier::findOrFail($this->supplierId), $dto)
            : $create->execute($dto, auth()->user());

        $this->open = false;
        $this->dispatch('supplier-saved');
        $this->dispatch('notify', message: 'Supplier saved.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.suppliers.supplier-form', ['users' => User::orderBy('name')->get()]);
    }
}