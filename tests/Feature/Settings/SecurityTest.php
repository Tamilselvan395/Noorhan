<?php

namespace Tests\Feature\Settings;

use App\Livewire\Security\SessionManager;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeSession(User $user): UserSession
    {
        return UserSession::create([
            'id' => Str::random(40), 'user_id' => $user->id,
            'ip_address' => '10.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0',
            'payload' => 'x', 'last_activity' => now()->toUnixTimestamp(),
        ]);
    }

    public function test_sessions_are_listed(): void
    {
        $user = User::factory()->create();
        $session = $this->makeSession($user);

        Livewire::actingAs($user)
            ->test(SessionManager::class)
            ->assertSee($session->ip_address);
    }

    public function test_session_can_be_revoked(): void
    {
        $user = User::factory()->create();
        $session = $this->makeSession($user);

        Livewire::actingAs($user)
            ->test(SessionManager::class)
            ->call('revoke', $session->id);

        $this->assertDatabaseMissing('sessions', ['id' => $session->id]);
    }

    public function test_user_cannot_revoke_another_users_session(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $session = $this->makeSession($other);

        $this->expectException(AuthorizationException::class);

        Livewire::actingAs($user)
            ->test(SessionManager::class)
            ->call('revoke', $session->id);
    }
}