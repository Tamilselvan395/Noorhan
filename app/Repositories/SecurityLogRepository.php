<?php

namespace App\Repositories;

use App\Enums\SecurityEvent;
use App\Models\SecurityLog;
use App\Models\User;

class SecurityLogRepository extends BaseRepository
{
    protected function model(): string
    {
        return SecurityLog::class;
    }

    public function log(SecurityEvent $event, ?User $user = null, array $metadata = []): SecurityLog
    {
        return $this->model->query()->create([
            'user_id'    => $user?->id,
            'event'      => $event->value,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata'   => $metadata ?: null,
        ]);
    }
}