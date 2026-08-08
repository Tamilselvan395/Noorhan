<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">WhatsApp CRM</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Campaigns, broadcasts & automations (welcome, reminders, reactivation, cross-sell).</p>
        </div>
        <button wire:click="openBuilder" class="py-2 px-4 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold shadow-sm">+ New Campaign</button>
    </div>

    @php $stats = $this->stats(); @endphp
    <div class="grid grid-cols-3 gap-4">
        <x-dashboard.stat-card label="Campaigns" :value="number_format($stats['campaigns'])" icon="chart" accent="bg-green-500/10 text-green-600 dark:text-green-400" />
        <x-dashboard.stat-card label="Messages Delivered" :value="number_format($stats['sent'])" icon="bolt" accent="bg-blue-500/10 text-blue-600 dark:text-blue-400" />
        <x-dashboard.stat-card label="Failed / Skipped" :value="number_format($stats['failed'])" icon="shield" accent="bg-red-500/10 text-red-600 dark:text-red-400" />
    </div>

    <x-card>
        <x-slot:header><h3 class="font-semibold">Campaigns & Broadcasts</h3></x-slot:header>
        <x-table>
            <x-slot:head>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Campaign</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Audience</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Delivered</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
            </x-slot:head>
            <x-slot:body>
                @forelse ($campaigns as $campaign)
                    <tr wire:key="wc-{{ $campaign->id }}">
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $campaign->name }}</p>
                            <p class="text-xs text-gray-400">{{ $campaign->created_at->format('M d, Y') }} · by {{ $campaign->creator?->name ?? 'System' }}</p>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300 capitalize">{{ $campaign->audience_type }}{{ $campaign->audience_value ? ': '.str_replace('_', ' ', $campaign->audience_value) : '' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300 capitalize">{{ $campaign->message_type }}</td>
                        <td class="px-6 py-3 text-sm"><span class="text-green-600 dark:text-green-400 font-medium">{{ $campaign->sent_count }}</span> <span class="text-gray-400">/ {{ $campaign->sent_count + $campaign->failed_count }}</span></td>
                        <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $campaign->status === 'sent' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : ($campaign->status === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400') }}">{{ ucfirst($campaign->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">No campaigns yet.</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
    </x-card>

    {{-- Builder modal --}}
    @if ($builderOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('builderOpen', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Campaign / Broadcast</h3>

                <form wire:submit="launch" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Campaign Name *</label>
                        <input wire:model="name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Audience</label>
                            <select wire:model.live="audience_type" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                <option value="all">All active customers</option>
                                <option value="division">By division</option>
                                <option value="type">By customer type</option>
                            </select>
                        </div>
                        <div>
                            @if ($audience_type === 'division')
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                                <select wire:model="audience_value" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                    @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                                </select>
                            @elseif ($audience_type === 'type')
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                                <select wire:model="audience_value" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                    @foreach (\App\Enums\CustomerType::cases() as $t) <option value="{{ $t->value }}">{{ $t->label() }}</option> @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message Type</label>
                        <select wire:model.live="message_type" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="text">Free text</option>
                            <option value="template">Meta template</option>
                            <option value="media">Media (image/document)</option>
                        </select>
                    </div>

                    @if ($message_type === 'template')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template</label>
                            <select wire:model="template_name" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                @foreach (array_keys(config('whatsapp.templates', [])) as $key) <option value="{{ $key }}">{{ ucwords(str_replace('_', ' ', $key)) }}</option> @endforeach
                            </select>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message Body *</label>
                            <textarea wire:model="body" rows="3" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-green-500 outline-none"></textarea>
                            @error('body') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if ($message_type === 'media')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Media URL *</label>
                                <input wire:model="media_url" type="url" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kind</label>
                                <select wire:model="media_kind" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                    <option value="image">Image</option>
                                    <option value="document">Document</option>
                                </select>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Schedule (optional)</label>
                            <input wire:model="scheduled_at" type="datetime-local" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        </div>
                        <label class="flex items-center text-sm text-gray-700 dark:text-gray-300 pb-2">
                            <input type="checkbox" wire:model="sendNow" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="ml-2">Send immediately</span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="$set('builderOpen', false)" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">Launch</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>