<?php

namespace App\Jobs\Capture;

use App\Models\LeadCaptureEvent;
use App\Services\Capture\LeadCaptureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class ProcessLeadCaptureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public LeadCaptureEvent $event) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(LeadCaptureService $service): void
    {
        try {
            $service->process($this->event);
        } catch (Throwable $e) {
            $this->event->update([
                'status' => 'failed',
                'error' => Str::limit($e->getMessage(), 500),
            ]);

            throw $e;
        }
    }
}