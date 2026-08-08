<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use InvalidArgumentException;

class ReportRegistry
{
    /** @var array<string, class-string<ReportInterface>> */
    private array $reports = [];

    public function register(string $class): self
    {
        if (! is_subclass_of($class, ReportInterface::class)) {
            throw new InvalidArgumentException("{$class} must implement ReportInterface.");
        }

        $this->reports[app($class)->key()] = $class;

        return $this;
    }

    public function resolve(string $key): ?ReportInterface
    {
        return isset($this->reports[$key]) ? app($this->reports[$key]) : null;
    }

    /** @return array<string, array<int, ReportInterface>> grouped for the UI */
    public function grouped(): array
    {
        $grouped = [];

        foreach ($this->reports as $class) {
            $report = app($class);
            $grouped[$report->group()][] = $report;
        }

        return $grouped;
    }
}