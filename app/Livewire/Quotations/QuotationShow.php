<?php

namespace App\Livewire\Quotations;

use App\Actions\Quotations\AcceptQuotationAction;
use App\Actions\Quotations\ApproveQuotationAction;
use App\Actions\Quotations\NewQuotationVersionAction;
use App\Actions\Quotations\RejectQuotationAction;
use App\Actions\Quotations\SendQuotationAction;
use App\Actions\Quotations\SubmitForApprovalAction;
use App\Actions\Customers\UploadCustomerDocumentAction;
use App\Enums\CommunicationChannel;
use App\Models\Quotation;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

class QuotationShow extends Component
{
    use WithFileUploads;

    public Quotation $quotation;

    public string $sendVia = 'email';
    public string $rejectReason = '';
    public ?string $publicUrl = null;
    public $file = null;

    public function mount(Quotation $quotation): void
    {
        $this->quotation = $quotation;
    }

    public function submitForApproval(SubmitForApprovalAction $action): void
    {
        Gate::authorize('update', $this->quotation);

        try {
            $action->execute($this->quotation);
            $this->dispatch('notify', message: 'Submitted for approval.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function approve(ApproveQuotationAction $action): void
    {
        Gate::authorize('approve', $this->quotation);

        try {
            $action->execute($this->quotation->fresh(), auth()->user());
            $this->dispatch('notify', message: 'Quotation approved.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function reject(RejectQuotationAction $action): void
    {
        Gate::authorize('approve', $this->quotation);

        $this->validate(['rejectReason' => 'required|string|max:300']);

        $action->execute($this->quotation->fresh(), auth()->user(), $this->rejectReason);
        $this->rejectReason = '';

        $this->dispatch('notify', message: 'Quotation rejected.', type: 'success');
    }

    public function send(SendQuotationAction $action): void
    {
        Gate::authorize('send', $this->quotation);

        try {
            $this->publicUrl = $action->execute($this->quotation->fresh(), CommunicationChannel::from($this->sendVia));
            $this->dispatch('notify', message: 'Quotation sent.', type: 'success');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function newVersion(NewQuotationVersionAction $action)
    {
        $version = $action->execute($this->quotation->fresh());

        $this->dispatch('notify', message: "Version {$version->version} created as draft.", type: 'success');

        return redirect()->route('quotations.edit', $version);
    }

    public function attach(UploadCustomerDocumentAction $action): void
    {
        $this->validate(['file' => 'required|file|max:10240']);

        $path = \App\Helpers\UploadHelper::upload($this->file, "quotations/{$this->quotation->id}");

        $this->quotation->attachments()->create([
            'name' => $this->file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'size' => $this->file->getSize(),
            'mime' => $this->file->getMimeType(),
            'uploaded_by' => auth()->id(),
        ]);

        $this->quotation->logActivity('attached a document');
        $this->reset('file');
        $this->dispatch('notify', message: 'Attachment added.', type: 'success');
    }

    public function convertToOrder(\App\Actions\SalesOrders\ConvertQuotationToOrderAction $convert)
    {
        $order = $convert->execute($this->quotation->fresh());

        $this->dispatch('notify', message: "Converted to {$order->reference}.", type: 'success');

        return redirect()->route('sales-orders.show', $order);
    }

    public function render(): View
    {
        return view('livewire.quotations.quotation-show', [
            'items' => $this->quotation->items()->with('product')->get(),
            'versions' => $this->quotation->parent_id
                ? $this->quotation->parent->versions()->with('creator')->orderBy('version')->get()
                : $this->quotation->versions()->with('creator')->orderBy('version')->get(),
            'attachments' => $this->quotation->attachments()->latest()->get(),
        ]);
    }
    
}