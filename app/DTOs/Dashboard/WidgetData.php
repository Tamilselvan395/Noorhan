<?php

namespace App\DTOs\Dashboard;

readonly class WidgetData
{
    public function __construct(
        public string $key,
        public string $label,
        public string $value,
        public ?float $delta = null,
        public ?string $hint = null,
        public string $icon = 'chart',
        public string $accent = 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
    ) {}
}