<?php

namespace App\Console\Commands;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Console\Command;

class ExpireQuotations extends Command
{
    protected $signature = 'quotations:expire';
    protected $description = 'Mark sent/approved quotations past their validity as expired.';

    public function handle(): int
    {
        $expired = Quotation::query()
            ->whereIn('status', [QuotationStatus::Sent->value, QuotationStatus::Approved->value])
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->toDateString())
            ->get();

        $expired->each(function (Quotation $quotation) {
            $quotation->update(['status' => QuotationStatus::Expired->value]);
            $quotation->logActivity('quotation expired (validity passed)');
        });

        $this->info("Expired {$expired->count()} quotation(s).");

        return self::SUCCESS;
    }
}