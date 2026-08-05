<?php

namespace App\Repositories;

use App\Enums\LoginHistoryType;
use App\Helpers\AgentHelper;
use App\Models\LoginHistory;
use App\Models\User;

class LoginHistoryRepository extends BaseRepository
{
    protected function model(): string
    {
        return LoginHistory::class;
    }

    public function record(?User $user, LoginHistoryType $type, bool $successful, string $ip, ?string $userAgent): LoginHistory
    {
        $agent = AgentHelper::parse($userAgent);

        return $this->model->query()->create([
            'user_id'    => $user?->id,
            'type'       => $type->value,
            'successful' => $successful,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'browser'    => $agent['browser'],
            'platform'   => $agent['platform'],
            'device'     => $agent['device'],
        ]);
    }

    public function isNewDevice(User $user, string $ip, ?string $userAgent): bool
    {
        return ! $this->model->query()
            ->where('user_id', $user->id)
            ->where('type', LoginHistoryType::Login->value)
            ->where('successful', true)
            ->where('ip_address', $ip)
            ->where('user_agent', $userAgent)
            ->exists();
    }
}