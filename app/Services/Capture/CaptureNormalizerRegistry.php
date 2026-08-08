<?php

namespace App\Services\Capture;

use App\Contracts\CaptureNormalizerInterface;
use App\Enums\LeadSource;
use InvalidArgumentException;

class CaptureNormalizerRegistry
{
    /** @var array<string, class-string<CaptureNormalizerInterface>> */
    private array $normalizers = [];

    /**
     * Register a normalizer instance by extracting its source enum.
     */
    public function register(CaptureNormalizerInterface $normalizer): self
    {
        $source = $normalizer->source();

        if (! $source instanceof LeadSource) {
            throw new InvalidArgumentException('Normalizer must return a valid LeadSource enum.');
        }

        $this->normalizers[$source->value] = get_class($normalizer);

        return $this;
    }

    /**
     * Resolve the normalizer for a given lead source.
     */
    public function forSource(LeadSource $source): CaptureNormalizerInterface
    {
        $class = $this->normalizers[$source->value] ?? $this->normalizers[LeadSource::Manual->value];

        if (! $class) {
            throw new InvalidArgumentException("No normalizer registered for source: {$source->value}");
        }

        return app($class);
    }
}