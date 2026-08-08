<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('quotations.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&larr;</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $quotation->reference }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $quotation->status()->badge() }}">{{ $quotation->status()->label() }}</span>
                @if ($quotation->requires_approval) <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Approval required</span> @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $quotation->customer?->displayName() ?? ($quotation->lead ? 'Lead: '.$quotation->lead->name : '—') }} · v{{ $quotation->version }} · by {{ $quotation->creator?->name ?? 'System' }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($quotation->status()->value === 'draft')
                <a href="{{ route('quotations.edit', $quotation) }}" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Edit</a>
                <button wire:click="submitForApproval" class="px-3 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold">Submit for Approval</button>
            @endif
            @if ($quotation->status()->value === 'pending_approval')
                <button wire:click="approve" class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs font-semibold">Approve</button>
                <input wire:model="rejectReason" placeholder="Reason…" class="px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs w-40">
                <button wire:click="reject" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold">Reject</button>
            @endif
            @if (in_array($quotation->status()->value, ['approved', 'accepted']))
            <select wire:model="sendVia" class="px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs">
                <option value="email">Email</option>
                <option value="whatsapp">WhatsApp</option>
            </select>

            <button
                wire:click="send"
                class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold">
                Send
            </button>

            @if (! $quotation->converted_order_id)
                <button
                    wire:click="convertToOrder"
                    class="px-3 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold">
                    Convert to Sales Order
                </button>
            @else
                <a
                    href="{{ route('sales-orders.show', $quotation->converted_order_id) }}"
                    class="px-3 py-2 rounded-lg text-xs font-semibold bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400">
                    View Sales Order
                </a>
            @endif
        @endif
            @if (in_array($quotation->status()->value, ['sent', 'accepted', 'rejected']))
                <button wire:click="newVersion" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">New Version (Negotiate)</button>
            @endif
            <a href="{{ route('quotations.public', $quotation) . '?signature=preview' }}" target="_blank" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Print / PDF</a>
        </div>
    </div>

    @if ($quotation->rejected_reason)
        <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-400">Rejected: {{ $quotation->rejected_reason }}</div>
    @endif
    @if ($publicUrl)
        <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-400 break-all">
            Customer link: <a href="{{ $publicUrl }}" target="_blank" class="underline">{{ $publicUrl }}</a>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Items --}}
        <div class="lg:col-span-2">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Items</h3></x-slot:header>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead><tr>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Item</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Qty</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Disc</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($items as $item)
                            <tr wire:key="i-{{ $item->id }}">
                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $item->description }}</td>
                                <td class="px-4 py-2 text-sm text-right text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="px-4 py-2 text-sm text-right text-gray-600 dark:text-gray-300">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-4 py-2 text-sm text-right text-gray-600 dark:text-gray-300">{{ $item->discount_percent }}%</td>
                                <td class="px-4 py-2 text-sm text-right font-medium text-gray-800 dark:text-gray-200">{{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 flex justify-end">
                    <dl class="w-56 text-sm space-y-1">
                        <div class="flex justify-between"><dt class="text-gray-400">Subtotal</dt><dd>{{ number_format((float) $quotation->subtotal, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Discount</dt><dd>-{{ number_format((float) $quotation->discount_amount, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Tax</dt><dd>{{ number_format((float) $quotation->tax_amount, 2) }}</dd></div>
                        <div class="flex justify-between font-bold text-base"><dt>Total</dt><dd class="text-blue-600 dark:text-blue-400">{{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</dd></div>
                        <div class="flex justify-between text-xs"><dt class="text-gray-400">Margin</dt><dd class="{{ (float) $quotation->margin_percent < (float) config('noorhan.quotation.min_margin') ? 'text-red-500' : 'text-green-600' }}">{{ $quotation->margin_percent }}%</dd></div>
                    </dl>
                </div>
            </x-card>

            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Attachments</h3>
                        <input type="file" wire:model="file" class="text-xs">
                    </div>
                </x-slot:header>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($attachments as $doc)
                        <li class="p-3"><a href="{{ $doc->url() }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ $doc->name }}</a></li>
                    @empty
                        <li class="p-6 text-center text-sm text-gray-400">No attachments.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>

        {{-- Meta / versions --}}
        <div class="space-y-6">
            <x-card>
                <x-slot:header><h3 class="font-semibold">Lifecycle</h3></x-slot:header>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-400">Valid Until</dt><dd class="{{ $quotation->isExpired() ? 'text-red-500' : 'text-gray-800 dark:text-gray-200' }}">{{ $quotation->valid_until?->format('M d, Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Approved By</dt><dd class="text-gray-800 dark:text-gray-200">{{ $quotation->approver?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Sent</dt><dd class="text-gray-800 dark:text-gray-200">{{ $quotation->sent_at?->format('M d, h:i A') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-400">Accepted</dt><dd class="text-gray-800 dark:text-gray-200">{{ $quotation->accepted_at?->format('M d, Y') ?? '—' }}</dd></div>
                </dl>
            </x-card>

            <x-card>
                <x-slot:header><h3 class="font-semibold">Version History</h3></x-slot:header>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li class="p-3 flex justify-between text-sm"><span class="font-medium text-gray-800 dark:text-gray-200">v{{ $quotation->parent_id ? $quotation->parent->version : $quotation->version }} {{ $quotation->parent_id ? '('.($quotation->parent->reference ?? 'original').')' : '(original)' }}</span><span class="text-gray-400">{{ $quotation->total }}</span></li>
                    @foreach ($versions as $v)
                        <li class="p-3 flex justify-between text-sm" wire:key="v-{{ $v->id }}">
                            <a href="{{ route('quotations.show', $v) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">v{{ $v->version }} · {{ $v->reference }}</a>
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $v->status()->badge() }}">{{ $v->status()->label() }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </div>
    </div>
</div>