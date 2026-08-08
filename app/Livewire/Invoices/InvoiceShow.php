<?php

namespace App\Livewire\Invoices;

use App\Actions\Invoices\RecordInvoicePaymentAction;
use App\Actions\Invoices\SendInvoiceAction;
use App\Enums\CommunicationChannel;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public string $sendVia = 'email';
    public string $paymentAmount = '';
    public ?string $publicUrl = null;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function send(SendInvoiceAction $action): void
    {
        Gate::authorize('send', $this->invoice);

        try {
            $this->publicUrl = $action->execute($this->invoice->fresh(), CommunicationChannel::from($this->sendVia));
            $this->dispatch('notify', message: 'Invoice sent.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function recordPayment(RecordInvoicePaymentAction $action): void
    {
        Gate::authorize('update', $this->invoice); // Finance role matrix arrives later

        $this->validate(['paymentAmount' => 'required|numeric|min:0.01']);

        try {
            $action->execute($this->invoice->fresh(), (float) $this->paymentAmount);
            $this->paymentAmount = '';
            $this->dispatch('notify', message: 'Payment recorded.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.invoices.invoice-show', [
            'items' => $this->invoice->items()->with('product')->get(),
            'timeline' => $this->invoice->activities()->with('user')->latest()->get(),
        ]);
    }
}