<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Overview;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_dashboard(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Sign-in Activity')
            ->assertSee('Divisional Dashboards');
    }

    public function test_period_can_be_switched(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Overview::class)
            ->call('setPeriod', '7d')
            ->assertSet('period', '7d')
            ->assertDispatched('dashboard:charts');
    }

    public function test_invalid_period_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Overview::class)
            ->call('setPeriod', 'invalid')
            ->assertStatus(422);
    }

    public function test_chart_payload_reflects_login_data(): void
    {
        $user = User::factory()->create();

        LoginHistory::create([
            'user_id' => $user->id,
            'type' => 'login',
            'successful' => true,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'device' => 'Desktop',
        ]);

        $component = Livewire::actingAs($user)->test(Overview::class);

        $payload = $component->instance()->chartPayload();

        $this->assertSame(1, array_sum($payload['activity']['success']));
        $this->assertSame('Windows', $payload['platforms'][0]['name']);
    }
}