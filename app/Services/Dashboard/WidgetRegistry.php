<?php

namespace App\Services\Dashboard;

use App\Contracts\DashboardWidgetInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class WidgetRegistry
{
    /** @var array<class-string<DashboardWidgetInterface>, class-string<DashboardWidgetInterface>> */
    private array $widgets = [];

    public function register(string $widgetClass): self
    {
        if (! is_subclass_of($widgetClass, DashboardWidgetInterface::class)) {
            throw new InvalidArgumentException("{$widgetClass} must implement DashboardWidgetInterface.");
        }

        $this->widgets[$widgetClass] = $widgetClass;

        return $this;
    }

    /** @return Collection<int, DashboardWidgetInterface> */
    public function widgets(): Collection
    {
        return collect(array_values($this->widgets))
            ->map(fn (string $class) => app($class))
            ->sortBy(fn (DashboardWidgetInterface $widget) => $widget->sortOrder())
            ->values();
    }
}