<?php

namespace App\Livewire\Accounting;

use App\Jobs\Accounting\ProcessZohoSyncJob;
use App\Models\ZohoConnection;
use App\Models\ZohoSyncLog;
use App\Services\Accounting\ZohoBooksClient;
use Illuminate\View\View;
use Livewire\Component;

class ZohoSettings extends Component
{
    public bool $sync_customers = true;
    public bool $sync_estimates = true;
    public bool $sync_sales_orders = true;
    public bool $sync_invoices = true;
    public bool $sync_payments = true;

    public string $testResult = '';

    public function mount(): void
    {
        $connection = ZohoConnection::query()->first();

        if ($connection) {
            foreach (['sync_customers', 'sync_estimates', 'sync_sales_orders', 'sync_invoices', 'sync_payments'] as $key) {
                $this->{$key} = $connection->setting($key);
            }
        }
    }

    public function saveSettings(): void
    {
        $connection = ZohoConnection::query()->first();

        if (! $connection) {
            $this->dispatch('notify', message: 'Connect Zoho Books first.', type: 'error');
            return;
        }

        $connection->update(['settings' => [
            'sync_customers' => $this->sync_customers,
            'sync_estimates' => $this->sync_estimates,
            'sync_sales_orders' => $this->sync_sales_orders,
            'sync_invoices' => $this->sync_invoices,
            'sync_payments' => $this->sync_payments,
        ]]);

        $this->dispatch('notify', message: 'Sync preferences saved.', type: 'success');
    }

    public function testConnection(ZohoBooksClient $client): void
    {
        try {
            $result = $client->testConnection();
            $count = count($result['organizations'] ?? []);
            $this->testResult = "✓ Connection OK — {$count} organization(s) visible.";
        } catch (\Throwable $e) {
            $this->testResult = '✗ '.$e->getMessage();
        }
    }

    public function retry(int $logId): void
    {
        $log = ZohoSyncLog::findOrFail($logId);

        ProcessZohoSyncJob::dispatch($log);

        $this->dispatch('notify', message: 'Sync re-queued.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.accounting.zoho-settings', [
            'connection' => ZohoConnection::query()->first(),
            'logs' => ZohoSyncLog::query()->latest()->limit(20)->get(),
        ]);
    }
}