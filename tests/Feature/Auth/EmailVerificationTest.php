<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $url = route('verification.verify', [
            $user->id,
            sha1($user->getEmailForVerification()),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.verify', [$user->id, 'invalid-hash']))
            ->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}