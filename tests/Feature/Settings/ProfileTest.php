<?php

namespace Tests\Feature\Settings;

use App\Livewire\Profile\UpdatePasswordForm;
use App\Livewire\Profile\UpdateProfileForm;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['password' => Hash::make('Password@123')]);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(UpdateProfileForm::class)
            ->set('name', 'Updated Name')
            ->set('email', 'updated@noorhan.com')
            ->call('save')
            ->assertDispatched('notify');

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('updated@noorhan.com', $user->fresh()->email);
    }

    public function test_changing_email_requires_reverification(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(UpdateProfileForm::class)
            ->set('email', 'new@noorhan.com')
            ->call('save');

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_password_can_be_changed_and_other_sessions_revoked(): void
    {
        $user = $this->user();

        UserSession::create([
            'id' => Str::random(40), 'user_id' => $user->id,
            'ip_address' => '1.2.3.4', 'user_agent' => 'Test', 'payload' => 'x', 'last_activity' => now()->toUnixTimestamp(),
        ]);

        Livewire::actingAs($user)
            ->test(UpdatePasswordForm::class)
            ->set('current_password', 'Password@123')
            ->set('password', 'NewPassword@123')
            ->set('password_confirmation', 'NewPassword@123')
            ->call('save')
            ->assertDispatched('notify');

        $this->assertTrue(Hash::check('NewPassword@123', $user->fresh()->password));
        $this->assertDatabaseCount('sessions', 0);
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = $this->user();

        Livewire::actingAs($user)
            ->test(UpdatePasswordForm::class)
            ->set('current_password', 'WrongPass@1')
            ->set('password', 'NewPassword@123')
            ->set('password_confirmation', 'NewPassword@123')
            ->call('save')
            ->assertHasErrors('current_password');
    }
}