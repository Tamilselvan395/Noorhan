<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Service Bay Console</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Today's schedule, technician load & appointments.</p>
        </div>
        <button wire:click="openForm" class="py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">+ Book Appointment</button>
    </div>

    {{-- Capacity --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @forelse ($capacity as $tech => $minutes)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-400">{{ $tech }}</p>
                <p class="mt-1 text-xl font-bold {{ $minutes > 420 ? 'text-red-500' : ($minutes > 300 ? 'text-amber-500' : 'text-green-600 dark:text-green-400') }}">
                    {{ intdiv($minutes, 60) }}h {{ $minutes % 60 }}m
                </p>
                <p class="text-xs text-gray-400">{{ $minutes > 420 ? 'Over capacity' : 'booked today' }}</p>
            </div>
        @empty
            <div class="col-span-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 text-center text-sm text-gray-400">No bookings today.</div>
        @endforelse
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Today's schedule --}}
        <x-card>
            <x-slot:header><h3 class="font-semibold">Today's Schedule</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($today as $apt)
                    <li class="p-4" wire:key="apt-{{ $apt->id }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $apt->scheduled_at->format('h:i A') }} · {{ $apt->customer->name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $apt->service->name }} · {{ $apt->vehicle() ?: '—' }} · {{ $apt->technician?->name ?? 'Unassigned' }}
                                </p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $apt->status()->badge() }}">{{ $apt->status()->label() }}</span>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            @foreach ($transitions[$apt->status->value] ?? [] as $next)
                                <button wire:click="advance({{ $apt->id }}, '{{ $next->value }}')"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $next->value === 'completed' ? 'bg-green-600 hover:bg-green-700 text-white' : ($next->value === 'in_progress' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300') }}">
                                    {{ $next->label() }}
                                </button>
                            @endforeach
                            @if ($apt->sales_order_id)
                                <a href="{{ route('sales-orders.show', $apt->sales_order_id) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400">Order</a>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm text-gray-400">Bay is free today.</li>
                @endforelse
            </ul>
        </x-card>

        {{-- Upcoming --}}
        <x-card>
            <x-slot:header><h3 class="font-semibold">Upcoming Bookings</h3></x-slot:header>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($upcoming as $apt)
                    <li class="p-3 flex justify-between text-sm" wire:key="up-{{ $apt->id }}">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">{{ $apt->scheduled_at->format('M d, h:i A') }} — {{ $apt->customer->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $apt->service->name }} · {{ $apt->estimated_minutes }} min</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $apt->reference }}</span>
                    </li>
                @empty
                    <li class="p-8 text-center text-sm text-gray-400">No upcoming bookings.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    {{-- Booking modal --}}
    @if ($formOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('formOpen', false)"></div>
            <div class="relative mx-auto my-8 bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Book Appointment</h3>
                <form wire:submit="book" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer *</label>
                        <select wire:model="customer_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">—</option>
                            @foreach ($customers as $c) <option value="{{ $c->id }}">{{ $c->displayName() }}</option> @endforeach
                        </select>
                        @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service *</label>
                        <select wire:model="product_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">—</option>
                            @foreach ($services as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date & Time *</label>
                        <input wire:model="scheduled_at" type="datetime-local" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @error('scheduled_at') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Technician</label>
                        <select wire:model="assigned_to" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                            <option value="">—</option>
                            @foreach ($users as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Make</label><input wire:model="vehicle_make" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label><input wire:model="vehicle_model" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label><input wire:model="vehicle_year" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plate</label><input wire:model="plate" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm"></div>
                    <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price Estimate</label><input wire:model="price_estimate" type="number" step="0.01" min="0" class="mt-1 w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm"></div>
                    <div class="col-span-2 flex justify-end space-x-3">
                        <button type="button" wire:click="$set('formOpen', false)" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Book</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>