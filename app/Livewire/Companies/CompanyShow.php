<?php

namespace App\Livewire\Companies;

use App\Actions\Companies\AttachContactAction;
use App\Actions\Customers\AddCommunicationAction;
use App\Actions\Customers\UploadCustomerDocumentAction;
use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompanyShow extends Component
{
    use WithFileUploads;

    public Company $company;

    public string $tab = 'overview';

    public ?int $attachContactId = null;

    public string $channel = 'phone';
    public string $direction = 'outbound';
    public string $subject = '';
    public string $body = '';

    public $file = null;

    public function mount(Company $company): void
    {
        $this->company = $company;
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function attachContact(AttachContactAction $action): void
    {
        Gate::authorize('update', $this->company);

        $customer = Customer::find($this->attachContactId);

        abort_unless($customer !== null, 422);

        $action->execute($this->company, $customer);

        $this->attachContactId = null;
        $this->dispatch('notify', message: 'Contact linked to company.', type: 'success');
    }

    public function addCommunication(AddCommunicationAction $action): void
    {
        Gate::authorize('update', $this->company);

        $this->validate(['body' => 'required|string|max:5000']);

        $action->execute(
            $this->company,
            CommunicationChannel::from($this->channel),
            CommunicationDirection::from($this->direction),
            $this->subject ?: null,
            $this->body,
            auth()->user(),
        );

        $this->reset(['subject', 'body']);
        $this->dispatch('notify', message: 'Communication logged.', type: 'success');
    }

    public function uploadDocument(UploadCustomerDocumentAction $action): void
    {
        Gate::authorize('update', $this->company);

        $this->validate(['file' => 'required|file|max:10240']);

        $action->execute($this->company, $this->file, auth()->user());

        $this->reset('file');
        $this->dispatch('notify', message: 'Document uploaded.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.companies.company-show', [
            'contacts' => $this->company->contacts()->with('owner')->get(),
            'unlinkedCustomers' => Customer::query()->whereNull('company_id')->orderBy('name')->limit(200)->get(),
            'communications' => $this->company->communications()->with('user')->latest('occurred_at')->get(),
            'documents' => $this->company->documents()->with('uploader')->latest()->get(),
            'timeline' => $this->company->activities()->with('user')->latest()->get(),
        ]);
    }
}