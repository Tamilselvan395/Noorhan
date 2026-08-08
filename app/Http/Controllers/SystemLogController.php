<?php

namespace App\Http\Controllers;

use App\Helpers\AuditDiffHelper;
use App\Models\Activity;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemLogController extends Controller
{
    public function activity(): View
    {
        return view('system.activity');
    }

    public function audit(): View
    {
        return view('system.audit');
    }

    public function exportActivity(Request $request): StreamedResponse
    {
        return $this->stream('activity_'.now()->format('Ymd').'.csv', function ($handle) use ($request) {
            fputcsv($handle, ['ID', 'Date', 'User', 'Entity', 'Entity ID', 'Description']);

            Activity::query()->with('user')
                ->when($request->query('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from')))
                ->when($request->query('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to')))
                ->latest()->chunk(500, function ($activities) use ($handle) {
                    foreach ($activities as $activity) {
                        fputcsv($handle, [
                            $activity->id,
                            $activity->created_at->format('Y-m-d H:i'),
                            $activity->user?->name ?? 'System',
                            class_basename((string) $activity->subject_type),
                            $activity->subject_id,
                            $activity->description,
                        ]);
                    }
                });
        });
    }

    public function exportAudit(Request $request): StreamedResponse
    {
        return $this->stream('audit_'.now()->format('Ymd').'.csv', function ($handle) use ($request) {
            fputcsv($handle, ['ID', 'Date', 'User', 'Event', 'Entity', 'Entity ID', 'IP', 'Changes']);

            AuditLog::query()->with('user')
                ->when($request->query('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from')))
                ->when($request->query('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to')))
                ->latest()->chunk(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->id,
                            $log->created_at->format('Y-m-d H:i'),
                            $log->user?->name ?? 'System',
                            $log->event,
                            class_basename($log->auditable_type),
                            $log->auditable_id,
                            $log->ip_address,
                            AuditDiffHelper::summarize($log),
                        ]);
                    }
                });
        });
    }

    private function stream(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer) {
            $handle = fopen('php://output', 'w');
            $writer($handle);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}