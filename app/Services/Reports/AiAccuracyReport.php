<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Enums\RoutingOutcome;
use App\Models\LeadRoutingLog;
use Illuminate\Support\Carbon;

class AiAccuracyReport implements ReportInterface
{
    public function key(): string { return 'ai_accuracy'; }
    public function label(): string { return 'AI Accuracy'; }
    public function group(): string { return 'AI'; }

    public function columns(): array
    {
        return ['Lead', 'Outcome', 'Confidence', 'Rule', 'Assigned To', 'Date'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return LeadRoutingLog::query()->with(['lead', 'rule', 'assignee'])
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->map(fn (LeadRoutingLog $log) => [
                $log->lead?->name ?? '—',
                RoutingOutcome::from($log->outcome)->label(),
                isset($log->classification['confidence']) ? round($log->classification['confidence'] * 100).'% ' : '—',
                $log->rule?->describe() ?? '—',
                $log->assignee?->name ?? 'Triage',
                $log->created_at->format('Y-m-d'),
            ])->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $logs = LeadRoutingLog::query()->whereBetween('created_at', [$from, $to])->get();

        return [
            'Routed Decisions' => number_format($logs->count()),
            'AI Recommendations' => number_format($logs->where('outcome', RoutingOutcome::AiRecommendation->value)->count()),
            'Rule Matches' => number_format($logs->where('outcome', RoutingOutcome::RuleMatch->value)->count()),
            'Triage' => number_format($logs->where('outcome', RoutingOutcome::Triage->value)->count()),
        ];
    }
}