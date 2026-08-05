<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private string $validPassword = 'Password@123';

    private function createUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'password'          => Hash::make($this->validPassword),
            'email_verified_at' => now(),
            'remember_token'    => null,
        ], $attrs));
    }

    public function test_users_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser();

        $this->post('/login', ['email' => $user->email, 'password' => $this->validPassword])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'type' => 'login', 'successful' => true]);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_users_cannot_login_with_invalid_credentials(): void
    {
        $user = $this->createUser();

        $this->post('/login', ['email' => $user->email, 'password' => 'WrongPass@1'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_account_locks_after_max_failed_attempts(): void
    {
        $user = $this->createUser();

        foreach (range(1, config('noorhan.auth.max_failed_attempts')) as $i) {
            $this->post('/login', ['email' => $user->email, 'password' => 'WrongPass@1']);
        }

        $this->assertNotNull($user->fresh()->locked_until);

        // Even correct credentials are rejected while locked.
        $this->post('/login', ['email' => $user->email, 'password' => $this->validPassword])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_rate_limiting_applies(): void
    {
        config(['noorhan.auth.rate_limit_max' => 3]);
        $user = $this->createUser();

        foreach (range(1, 3) as $i) {
            $this->post('/login', ['email' => $user->email, 'password' => 'WrongPass@1']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => $this->validPassword])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_remember_me_sets_remember_token(): void
    {
        $user = $this->createUser();

        $this->post('/login', ['email' => $user->email, 'password' => $this->validPassword, 'remember' => true]);

        $this->assertNotEmpty($user->fresh()->remember_token);
    }

    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user = $this->createUser(['email_verified_at' => null]);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('verification.notice'));
    }
}