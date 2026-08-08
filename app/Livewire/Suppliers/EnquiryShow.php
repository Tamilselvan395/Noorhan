<?php

namespace App\Livewire\Suppliers;

use App\Actions\Suppliers\CloseSupplierEnquiryAction;
use App\Actions\Suppliers\RecordSupplierResponseAction;
use App\Actions\Suppliers\SendSupplierEnquiryAction;
use App\Enums\CommunicationChannel;
use App\Enums\EnquiryItemStatus;
use App\Models\SupplierEnquiry;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

class EnquiryShow extends Component
{
    public SupplierEnquiry $enquiry;

    public string $sendVia = 'email';

    /** @var array<int, array{price: string, lead: string, valid: string, notes: string, status: string}> */
    public array $responses = [];

    public function mount(SupplierEnquiry $enquiry): void
    {
        $this->enquiry = $enquiry;

        foreach ($enquiry->items as $item) {
            $this->responses[$item->id] = [
                'price' => $item->offered_price !== null ? (string) $item->offered_price : '',
                'lead' => $item->lead_time_days !== null ? (string) $item->lead_time_days : '',
                'valid' => $item->valid_until?->format('Y-m-d') ?? '',
                'notes' => (string) ($item->supplier_notes ?? ''),
                'status' => 'quoted',
            ];
        }
    }

    public function send(SendSupplierEnquiryAction $send): void
    {
        Gate::authorize('update', $this->enquiry);

        try {
            $send->execute($this->enquiry, CommunicationChannel::from($this->sendVia));
            $this->dispatch('notify', message: 'Enquiry marked as sent.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function recordResponse(int $itemId, RecordSupplierResponseAction $record): void
    {
        Gate::authorize('update', $this->enquiry);

        $item = $this->enquiry->items()->findOrFail($itemId);

        $data = $this->responses[$itemId];

        $this->validate([
            "responses.{$itemId}.price" => ['required_if:responses.'.$itemId.'.status,quoted', 'nullable', 'numeric', 'min:0'],
        ]);

        try {
            $record->execute(
                $item,
                EnquiryItemStatus::from($data['status']),
                $data['price'] !== '' ? (float) $data['price'] : null,
                $data['lead'] !== '' ? (int) $data['lead'] : null,
                $data['valid'] ?: null,
                $data['notes'] ?: null,
            );

            $this->dispatch('notify', message: 'Response recorded.', type: 'success');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function close(bool $cancel = false, CloseSupplierEnquiryAction $close): void
    {
        Gate::authorize('update', $this->enquiry);

        try {
            $close->execute($this->enquiry, $cancel);
            $this->dispatch('notify', message: $cancel ? 'Enquiry cancelled.' : 'Enquiry closed.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        return view('livewire.suppliers.enquiry-show', [
            'items' => $this->enquiry->items()->with('product')->get(),
            'timeline' => $this->enquiry->activities()->with('user')->latest()->get(),
        ]);
    }
}