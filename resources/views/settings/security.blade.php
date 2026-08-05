@extends('layouts.app')
@section('title', 'Security Center')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Security Center</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Sessions, device activity and security events.</p>
    </div>

    <livewire:security.session-manager />
    <livewire:security.login-history-table />
    <livewire:security.security-log-table />
</div>
@endsection