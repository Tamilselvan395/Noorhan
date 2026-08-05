<?php

namespace App\Services\Capture;

use App\Contracts\CaptureNormalizerInterface;
use App\Enums\LeadSource;
use InvalidArgumentException;

class CaptureNormalizerRegistry
{
    /** @var array<string, class-string<CaptureNormalizerInterface>> */
    private array $normalizers = [];

    public function register(string $class): self
    {
        if (! is_subclass_of($class, CaptureNormalizerInterface::class)) {
            throw new InvalidArgumentException("{$class} must implement CaptureNormalizerInterface.");
        }

        $this->normalizers[$class::source()->value] = $class;

        return $this;
    }

    public function forSource(LeadSource $source): CaptureNormalizerInterface
    {
        $class = $this->normalizers[$source->value] ?? $this->normalizers[LeadSource::Manual->value];

        return app($class);
    }
}