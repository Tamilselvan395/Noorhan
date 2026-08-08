<?php

namespace App\Livewire\Reports;

use App\Services\Reports\ReportRegistry;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class ReportCenter extends Component
{
    public string $reportKey = 'sales';
    public string $from = '';
    public string $to = '';

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
    }

    public function selectReport(string $key): void
    {
        $this->reportKey = $key;
    }

    public function range(): array
    {
        return [
            Carbon::parse($this->from)->startOfDay(),
            Carbon::parse($this->to)->endOfDay(),
        ];
    }

    public function render(ReportRegistry $registry): View
    {
        [$from, $to] = $this->range();

        $report = $registry->resolve($this->reportKey) ?? $registry->resolve('sales');

        return view('livewire.reports.report-center', [
            'grouped' => $registry->grouped(),
            'report' => $report,
            'columns' => $report->columns(),
            'rows' => $report->rows($from, $to),
            'summary' => $report->summary($from, $to),
        ]);
    }
}