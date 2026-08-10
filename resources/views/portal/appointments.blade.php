@extends('layouts.portal')
@section('title', 'Appointments')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold">Service Appointments</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Book and track your Otozaar premium services.</p>
    </div>

    <livewire:portal.book-service />

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Reference</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Service</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">When</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400"></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($appointments as $apt)
                    <tr>
                        <td class="px-5 py-3 text-sm font-medium">{{ $apt->reference }}</td>
                        <td class="px-5 py-3 text-sm">{{ $apt->service->name }}</td>
                        <td class="px-5 py-3 text-sm text-gray-500">{{ $apt->scheduled_at->format('M d, Y h:i A') }}</td>
                        <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $apt->status()->badge() }}">{{ $apt->status()->label() }}</span></td>
                        <td class="px-5 py-3 text-right">
                            @if ($apt->status->value === 'booked')
                                <form method="POST" action="{{ route('portal.appointments.cancel', $apt) }}" onsubmit="return confirm('Cancel this appointment?')">
                                    @csrf
                                    <button class="text-xs font-medium text-red-600 dark:text-red-400 hover:underline">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">No appointments yet — book your first service above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection