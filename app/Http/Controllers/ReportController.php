<?php

namespace App\Http\Controllers;

use App\Services\Reports\ReportRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('reports.index');
    }

    public function export(Request $request, string $key, ReportRegistry $registry): StreamedResponse
    {
        $report = $registry->resolve($key);

        abort_unless($report !== null, 404);

        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->toDateString()))->endOfDay();

        $filename = $key.'_'.$from->format('Ymd').'_'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($report, $from, $to) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $report->columns());

            foreach ($report->rows($from, $to) as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}