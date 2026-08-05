<?php

namespace App\Livewire\Customers;

use App\Actions\Customers\AddCommunicationAction;
use App\Actions\Customers\UploadCustomerDocumentAction;
use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomerShow extends Component
{
    use WithFileUploads;

    public Customer $customer;

    public string $tab = 'overview';

    // Communication form
    public string $channel = 'phone';
    public string $direction = 'outbound';
    public string $subject = '';
    public string $body = '';

    // Document upload
    public $file = null;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function addCommunication(AddCommunicationAction $action): void
    {
        Gate::authorize('update', $this->customer);

        $this->validate(['body' => 'required|string|max:5000']);

        $action->execute(
            $this->customer,
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
        Gate::authorize('update', $this->customer);

        $this->validate(['file' => 'required|file|max:10240']);

        $action->execute($this->customer, $this->file, auth()->user());

        $this->reset('file');
        $this->dispatch('notify', message: 'Document uploaded.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.customers.customer-show', [
            'timeline' => $this->customer->activities()->with('user')->latest()->get(),
            'communications' => $this->customer->communications()->with('user')->latest('occurred_at')->get(),
            'documents' => $this->customer->documents()->with('uploader')->latest()->get(),
        ]);
    }
}