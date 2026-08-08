@extends('layouts.app')
@section('title', 'Notification Preferences')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notification Preferences</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Choose how each category reaches you.</p>
    </div>
    <livewire:notifications.preferences-form />
</div>
@endsection