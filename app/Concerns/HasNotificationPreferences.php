<?php

namespace App\Concerns;

trait HasNotificationPreferences
{
    public const DEFAULT_PREFERENCES = [
        'leads' => ['database' => true, 'mail' => true],
        'sales' => ['database' => true, 'mail' => true],
        'finance' => ['database' => true, 'mail' => true],
        'system' => ['database' => true, 'mail' => true],
        'marketing' => ['database' => true, 'mail' => false],
    ];

    public function notificationPreferences(): array
    {
        return array_replace_recursive(self::DEFAULT_PREFERENCES, $this->notification_preferences ?? []);
    }

    public function prefersChannel(string $category, string $channel): bool
    {
        return (bool) ($this->notificationPreferences()[$category][$channel] ?? false);
    }

    public function updatePreferences(array $preferences): void
    {
        $this->update(['notification_preferences' => $preferences]);
        $this->refresh();
    }
}