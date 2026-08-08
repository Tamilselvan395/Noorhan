<?php

namespace App\Livewire\Payments;

use App\Actions\Payments\CreatePaymentAction;
use App\DTOs\Payments\PaymentDTO;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class PaymentForm extends Component
{
    public bool $open = false;

    public ?int $customer_id = null;
    public string $amount = '';
    public string $currency = 'USD';
    public string $payment_date = '';
    public string $method = 'bank_transfer';
    public string $reference_number = '';
    public string $notes = '';

    /** @var array<int, string> invoice_id => allocated_amount */
    public array $allocations = [];

    #[On('open-payment-form')]
    public function openForm(?int $customerId = null, ?int $invoiceId = null): void
    {
        Gate::authorize('create', \App\Models\Payment::class);

        $this->resetValidation();
        $this->reset(['amount', 'reference_number', 'notes', 'allocations']);
        
        $this->customer_id = $customerId;
        $this->payment_date = now()->format('Y-m-d');
        $this->method = 'bank_transfer';

        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice && (float) $invoice->balance_due > 0) {
                $this->customer_id = $invoice->customer_id;
                $this->amount = (string) $invoice->balance_due;
                $this->allocations[$invoice->id] = (string) $invoice->balance_due;
            }
        }

        $this->open = true;
    }

    public function updatedCustomerId(): void
    {
        $this->allocations = [];
        $this->amount = '';
    }

    public function getOutstandingInvoicesProperty()
    {
        if (! $this->customer_id) return collect();

        return Invoice::query()
            ->where('customer_id', $this->customer_id)
            ->outstanding()
            ->orderBy('due_date')
            ->get();
    }

    public function save(CreatePaymentAction $action)
    {
        $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string'],
        ]);

        $cleanAllocations = [];
        foreach ($this->allocations as $invId => $amt) {
            if ((float) $amt > 0) {
                $cleanAllocations[(int) $invId] = (float) $amt;
            }
        }

        $dto = new PaymentDTO(
            customer_id: $this->customer_id,
            amount: (float) $this->amount,
            currency: $this->currency,
            payment_date: $this->payment_date,
            method: $this->method,
            reference_number: $this->reference_number ?: null,
            notes: $this->notes ?: null,
            allocations: $cleanAllocations,
        );

        try {
            $payment = $action->execute($dto, auth()->user());
            $this->open = false;
            $this->dispatch('payment-saved');
            $this->dispatch('notify', message: "Payment {$payment->reference} recorded.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.payments.payment-form', [
            'customers' => Customer::active()->orderBy('name')->get(),
            'outstandingInvoices' => $this->outstandingInvoices,
        ]);
    }
}