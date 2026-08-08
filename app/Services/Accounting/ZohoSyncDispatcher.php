<?php

namespace App\Services\Accounting;

use App\Jobs\Accounting\ProcessZohoSyncJob;
use App\Models\ZohoConnection;
use App\Models\ZohoSyncLog;
use Illuminate\Database\Eloquent\Model;

class ZohoSyncDispatcher
{
    private const SETTING_KEY = [
        'Customer' => 'sync_customers',
        'Quotation' => 'sync_estimates',
        'SalesOrder' => 'sync_sales_orders',
        'Invoice' => 'sync_invoices',
        'Payment' => 'sync_payments',
    ];

    public function queue(Model $model, string $operation = 'create'): ?ZohoSyncLog
    {
        if (! config('zoho.enabled')) {
            return null;
        }

        $connection = ZohoConnection::query()->first();

        if (! $connection) {
            return null;
        }

        $entity = class_basename($model);

        $key = self::SETTING_KEY[$entity] ?? null;

        if ($key && ! $connection->setting($key)) {
            return null;
        }

        $log = ZohoSyncLog::create([
            'syncable_type' => get_class($model),
            'syncable_id' => $model->id,
            'entity_type' => $entity,
            'operation' => $operation,
            'status' => 'pending',
        ]);

        ProcessZohoSyncJob::dispatch($log);

        return $log;
    }
}