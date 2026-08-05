<?php

namespace App\Services\Capture;

use App\Actions\Capture\CaptureLeadAction;
use App\Enums\LeadSource;
use App\Jobs\Capture\ProcessLeadCaptureJob;
use App\Models\Lead;
use App\Models\LeadCaptureEvent;

class LeadCaptureService
{
    public function __construct(
        private CaptureNormalizerRegistry $registry,
        private CaptureLeadAction $capture,
    ) {}

    /**
     * Ingest a raw channel payload. Returns the audit event.
     * Processing is async by default (webhooks respond 202 instantly).
     */
    public function ingest(LeadSource $source, array $payload, ?bool $async = null): LeadCaptureEvent
    {
        $event = LeadCaptureEvent::create([
            'source' => $source->value,
            'payload' => $payload,
            'status' => 'received',
        ]);

        $async ??= (bool) config('noorhan.capture.async_processing', true);

        $async
            ? ProcessLeadCaptureJob::dispatch($event)
            : $this->process($event);

        return $event;
    }

    public function process(LeadCaptureEvent $event): Lead
    {
        $normalizer = $this->registry->forSource(LeadSource::from($event->source));

        $lead = $this->capture->execute($normalizer->normalize($event->payload));

        $event->update(['status' => 'processed', 'lead_id' => $lead->id]);

        return $lead;
    }
}