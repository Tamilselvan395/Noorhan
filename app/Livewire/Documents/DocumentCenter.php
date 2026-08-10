<?php

namespace App\Livewire\Documents;

use App\Actions\Documents\DeleteDocumentAction;
use App\Models\Document;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentCenter extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = 'all';
    public string $category = 'all';
    public bool $expiringOnly = false;

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function delete(int $documentId, DeleteDocumentAction $action): void
    {
        $document = Document::findOrFail($documentId);

        Gate::authorize('delete', $document);

        $action->execute($document);

        $this->dispatch('notify', message: 'Document deleted.', type: 'success');
    }

    public function entityLink(Document $document): ?string
    {
        $map = [
            'App\Models\Customer' => 'customers.show',
            'App\Models\Company' => 'companies.show',
            'App\Models\Lead' => 'leads.show',
            'App\Models\Supplier' => 'suppliers.show',
            'App\Models\Quotation' => 'quotations.show',
            'App\Models\SalesOrder' => 'sales-orders.show',
            'App\Models\Invoice' => 'invoices.show',
        ];

        $route = $map[$document->documentable_type] ?? null;

        return $route && $document->documentable ? route($route, $document->documentable) : null;
    }

    public function render(): View
    {
        $documents = Document::query()
            ->with('uploader')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->type !== 'all', fn ($q) => $q->where('documentable_type', $this->type))
            ->when($this->category !== 'all', fn ($q) => $q->where('category', $this->category))
            ->when($this->expiringOnly, fn ($q) => $q->expiringSoon(30))
            ->latest()
            ->paginate(15);

        return view('livewire.documents.document-center', [
            'documents' => $documents,
            'expiringCount' => Document::query()->expiringSoon(30)->count(),
        ]);
    }
}