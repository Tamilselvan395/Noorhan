@extends('layouts.guest')
@section('title', 'Get a Quote')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900">
    <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8" x-data="{ }">
        <div class="flex items-center space-x-2 mb-6">
            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">N</div>
            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ config('noorhan.name') }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Request a Quotation</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tell us what you need — our team responds within one business day.</p>

        <form method="POST" action="{{ route('capture.web.store') }}" class="mt-6 space-y-4" id="captureForm">
            @csrf
            {{-- Honeypot --}}
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

            {{-- UTM tracking --}}
            <input type="hidden" name="utm_source" id="utm_source">
            <input type="hidden" name="utm_medium" id="utm_medium">
            <input type="hidden" name="utm_campaign" id="utm_campaign">
            <input type="hidden" name="landing_url" id="landing_url">

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name *</label>
                    <input name="name" required class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                    <input name="company_name" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input name="email" type="email" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone / WhatsApp</label>
                    <input name="phone" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">I'm interested in *</label>
                    <select name="division" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        @foreach (\App\Enums\Division::cases() as $d) <option value="{{ $d->value }}">{{ $d->label() }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle Brand</label>
                    <select name="vehicle_brand_category" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                        <option value="">— Select —</option>
                        @foreach (\App\Enums\VehicleBrandCategory::cases() as $v) <option value="{{ $v->value }}">{{ $v->label() }}</option> @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">What do you need?</label>
                <textarea name="message" rows="3" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Parts, lubricants, wipers or service details…"></textarea>
            </div>

            @error('name') <p class="text-sm text-red-500">{{ $message }}</p> @enderror

            <button class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 active:scale-[.99] text-white text-sm font-semibold shadow-sm transition">Submit Enquiry</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Populate UTM + landing URL from the query string.
    (function () {
        const params = new URLSearchParams(window.location.search);
        ['utm_source', 'utm_medium', 'utm_campaign'].forEach(k => {
            const el = document.getElementById(k);
            if (el && params.get(k)) el.value = params.get(k);
        });
        document.getElementById('landing_url').value = window.location.href;
    })();
</script>
@endpush
@endsection