<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Document Center</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Every file across the CRM.
                @if ($expiringCount > 0)
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">{{ $expiringCount }} expiring within 30 days</span>
                @endif
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search file name…" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="type" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Entities</option>
            @foreach (['App\Models\Customer' => 'Customers', 'App\Models\Company' => 'Companies', 'App\Models\Lead' => 'Leads', 'App\Models\Supplier' => 'Suppliers', 'App\Models\Quotation' => 'Quotations', 'App\Models\SalesOrder' => 'Sales Orders', 'App\Models\Invoice' => 'Invoices'] as $class => $label)
                <option value="{{ $class }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="category" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
            <option value="all">All Categories</option>
            @foreach (\App\Enums\DocumentCategory::cases() as $c) <option value="{{ $c->value }}">{{ $c->label() }}</option> @endforeach
        </select>
        <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" wire:model.live="expiringOnly" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
            <span class="ml-2">Expiring soon only</span>
        </label>
    </div>

    <x-card>
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Document</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Entity</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Size</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Uploaded By</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Expiry</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
            <x-slot:body>
                @forelse ($documents as $document)
                    <tr wire:key="dc-{{ $document->id }}">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $document->name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">
                            @php $link = $this->entityLink($document); @endphp
                            @if ($link) <a href="{{ $link }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ class_basename($document->documentable_type) }} #{{ $document->documentable_id }}</a>
                            @else {{ class_basename((string) $document->documentable_type) }} @endif
                        </td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $document->category()->badge() }}">{{ $document->category()->label() }}</span></td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $document->humanSize() }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $document->uploader?->name ?? 'System' }}</td>
                        <td class="px-6 py-3 text-sm">
                            @if ($document->isExpired()) <span class="text-red-500 font-semibold">Expired</span>
                            @elseif ($document->isExpiringSoon()) <span class="text-amber-500 font-semibold">{{ $document->expires_at->diffInDays(now()) }}d left</span>
                            @else <span class="text-gray-400">{{ $document->expires_at?->format('M d, Y') ?? '—' }}</span> @endif
                        </td>
                        <td class="px-6 py-3 text-right space-x-2">
                            <a href="{{ route('documents.download', $document) }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">Download</a>
                            @can('delete', $document)
                                <button wire:click="delete({{ $document->id }})" wire:confirm="Delete document and file?" class="text-xs font-medium text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">No documents match the filters.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
        <div class="p-4">{{ $documents->links() }}</div>
    </x-card>
</div>