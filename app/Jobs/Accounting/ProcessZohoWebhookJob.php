<?php

namespace App\Jobs\Accounting;

use App\Actions\Payments\CreatePaymentAction;
use App\DTOs\Payments\PaymentDTO;
use App\Models\Invoice;
use App\Models\ZohoWebhookEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class ProcessZohoWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public ZohoWebhookEvent $event) {}

    public function handle(): void
    {
        try {
            $data = $this->event->payload['data'] ?? [];

            match ($this->event->event) {
                'invoice.status' => $this->handleInvoiceStatus($data),
                default => null,
            };

            $this->event->update(['status' => 'processed']);
        } catch (Throwable $e) {
            $this->event->update(['status' => 'failed', 'error' => Str::limit($e->getMessage(), 500)]);
            throw $e;
        }
    }

    /** When Zoho reports an invoice as paid, mirror it locally as an online payment. */
    private function handleInvoiceStatus(array $data): void
    {
        if (($data['status'] ?? '') !== 'paid') {
            return;
        }

        $invoice = Invoice::query()->where('zoho_id', $data['invoice_id'] ?? null)->first();

        if (! $invoice || (float) $invoice->balance_due <= 0) {
            return;
        }

        app(CreatePaymentAction::class)->execute(new PaymentDTO(
            customer_id: $invoice->customer_id,
            amount: (float) $invoice->balance_due,
            payment_date: now()->toDateString(),
            method: 'online',
            reference_number: 'ZOHO-'.$this->event->id,
            notes: 'Auto-applied from Zoho Books webhook (paid).',
            allocations: [$invoice->id => (float) $invoice->balance_due],
        ), null);
    }
}