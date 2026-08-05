<?php

namespace App\Livewire\Companies;

use App\Actions\Companies\CreateCompanyAction;
use App\Actions\Companies\UpdateCompanyAction;
use App\DTOs\Companies\CompanyDTO;
use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CompanyForm extends Component
{
    public bool $open = false;
    public ?int $companyId = null;

    public string $name = '';
    public string $trade_license_no = '';
    public string $tax_number = '';
    public string $type = 'garage';
    public string $status = 'active';
    public string $division = 'automotive';
    public string $email = '';
    public string $phone = '';
    public string $website = '';
    public string $address = '';
    public string $city = '';
    public string $country = '';
    public string $notes = '';
    public ?int $owner_id = null;

    #[On('open-company-form')]
    public function openForm(?int $companyId = null): void
    {
        $this->resetValidation();
        $this->companyId = $companyId;

        if ($companyId) {
            $company = Company::findOrFail($companyId);
            Gate::authorize('update', $company);

            foreach (['name','trade_license_no','tax_number','type','status','division','email','phone','website','address','city','country','notes','owner_id'] as $field) {
                $this->{$field} = $company->{$field} ?? '';
            }
        } else {
            Gate::authorize('create', Company::class);
            $this->reset(['name','trade_license_no','tax_number','email','phone','website','address','city','country','notes','owner_id']);
            [$this->type, $this->status, $this->division] = ['garage', 'active', 'automotive'];
        }

        $this->open = true;
    }

    public function save(CreateCompanyAction $create, UpdateCompanyAction $update): void
    {
        $data = $this->validate(StoreCompanyRequest::rules());

        foreach (['trade_license_no','tax_number','email','phone','website','address','city','country','notes','owner_id'] as $nullable) {
            $data[$nullable] = $data[$nullable] ?: null;
        }

        $dto = CompanyDTO::fromArray($data);

        $this->companyId
            ? $update->execute(Company::findOrFail($this->companyId), $dto)
            : $create->execute($dto, auth()->user());

        $this->open = false;
        $this->dispatch('company-saved');
        $this->dispatch('notify', message: 'Company saved.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.companies.company-form', ['users' => User::orderBy('name')->get()]);
    }
}