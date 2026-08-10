@extends('layouts.app')
@section('title', 'Communication Center')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Communication Center</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Every email, call, WhatsApp, meeting & SMS across the CRM.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2"><livewire:communications.communication-center /></div>
        <div><livewire:communications.compose-email /></div>
    </div>
</div>
@endsection