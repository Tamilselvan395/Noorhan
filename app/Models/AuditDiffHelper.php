<?php

namespace App\Helpers;

use App\Models\AuditLog;

class AuditDiffHelper
{
    /**
     * Field-level old → new changes for an "updated" audit event.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public static function changes(AuditLog $log): array
    {
        if ($log->event !== 'updated') {
            return [];
        }

        $changes = [];

        foreach (($log->new_values ?? []) as $field => $new) {
            $old = $log->old_values[$field] ?? null;

            if ($old !== $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        return $changes;
    }

    /** One-line summary for CSV exports. */
    public static function summarize(AuditLog $log): string
    {
        return collect(self::changes($log))
            ->map(fn ($change, $field) => "{$field}: ".var_export($change['old'], true).' → '.var_export($change['new'], true))
            ->implode('; ');
    }
}