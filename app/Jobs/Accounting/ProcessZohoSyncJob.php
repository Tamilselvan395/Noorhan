<?php

namespace App\Jobs\Accounting;

use App\Models\ZohoSyncLog;
use App\Services\Accounting\SyncCustomerToZoho;
use App\Services\Accounting\SyncEstimateToZoho;
use App\Services\Accounting\SyncInvoiceToZoho;
use App\Services\Accounting\SyncPaymentToZoho;
use App\Services\Accounting\SyncSalesOrderToZoho;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class ProcessZohoSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public ZohoSyncLog $log) {}

    public function backoff(): array
    {
        return [30, 60, 300, 600, 900];
    }

    public function handle(): void
    {
        $this->log->update([
            'attempts' => $this->log->attempts + 1,
            'last_attempted_at' => now(),
            'status' => 'pending',
        ]);

        $model = $this->log->syncable;

        if (! $model) {
            $this->log->update(['status' => 'failed', 'error' => 'Source record no longer exists.']);
            return;
        }

        $zohoId = match ($this->log->entity_type) {
            'Customer' => app(SyncCustomerToZoho::class)->execute($model),
            'Quotation' => app(SyncEstimateToZoho::class)->execute($model),
            'SalesOrder' => app(SyncSalesOrderToZoho::class)->execute($model),
            'Invoice' => app(SyncInvoiceToZoho::class)->execute($model),
            'Payment' => app(SyncPaymentToZoho::class)->execute($model),
            default => throw new \RuntimeException("No Zoho sync service for {$this->log->entity_type}"),
        };

        $this->log->update(['status' => 'success', 'zoho_id' => $zohoId, 'error' => null]);
    }

    public function failed(Throwable $exception): void
    {
        $this->log->update([
            'status' => 'failed',
            'error' => Str::limit($exception->getMessage(), 500),
        ]);
    }
}