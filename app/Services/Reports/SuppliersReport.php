<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\Supplier;
use Illuminate\Support\Carbon;

class SuppliersReport implements ReportInterface
{
    public function key(): string { return 'suppliers'; }
    public function label(): string { return 'Suppliers'; }
    public function group(): string { return 'Operations'; }

    public function columns(): array
    {
        return ['Supplier', 'Division', 'Rating', 'Enquiries', 'Responded', 'Avg Response (h)'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return Supplier::query()->withCount(['ratings', 'priceLists'])->get()->map(function (Supplier $s) {
            return [
                $s->name, $s->division()->label(), $s->overallRating() ?? '—',
                $s->enquiries()->count(),
                $s->enquiries()->whereNotNull('responded_at')->count(),
                $s->enquiries()->whereNotNull('responded_at')->get()->avg(fn ($e) => $e->responseTimeHours()) ?? '—',
            ];
        })->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        return ['Suppliers' => number_format(Supplier::count())];
    }
}