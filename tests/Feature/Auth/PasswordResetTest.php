<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_can_be_requested(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('security_logs', ['event' => 'password_reset_requested']);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewPassword@123', $user->fresh()->password));
        $this->assertNotNull($user->fresh()->password_changed_at);
    }

    public function test_password_policy_is_enforced(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors('password');
    }
}