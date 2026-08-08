<?php

namespace Tests\Feature\Auth;

use App\Services\Auth\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private TotpService $totp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->totp = app(TotpService::class);
        config(['noorhan.auth.two_factor.enabled' => true]);
    }

    public function test_totp_verify_accepts_generated_code(): void
    {
        $secret = $this->totp->generateSecret();

        $this->assertTrue($this->totp->verify($secret, $this->totp->code($secret)));
        $this->assertFalse($this->totp->verify($secret, '000000') && $this->totp->code($secret) !== '000000');
    }

    public function test_enable_and_confirm_flow_activates_2fa(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.enable'));

        $secret = Crypt::decryptString($user->fresh()->two_factor_secret);
        $this->assertNotNull($secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);

        $this->actingAs($user)
            ->from(route('two-factor.settings'))
            ->post(route('two-factor.confirm'), ['code' => $this->totp->code($secret)])
            ->assertRedirect(route('two-factor.settings'));

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->two_factor_confirmed_at);
        $this->assertCount(8, json_decode(Crypt::decryptString($fresh->two_factor_recovery_codes), true));
    }

    public function test_wrong_code_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.enable'));
        $secret = Crypt::decryptString($user->fresh()->two_factor_secret);

        $this->actingAs($user)
            ->from(route('two-factor.settings'))
            ->post(route('two-factor.confirm'), ['code' => '999999'])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_enrolled_user_is_redirected_to_challenge(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => Crypt::encryptString($this->totp->generateSecret()),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('two-factor.challenge'));
    }

    public function test_challenge_verify_unlocks_session(): void
    {
        $secret = $this->totp->generateSecret();

        $user = User::factory()->create([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('two-factor.verify'), ['code' => $this->totp->code($secret)])
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_recovery_code_is_single_use(): void
    {
        $codes = ['ABCD-1234', 'EFGH-5678'];

        $user = User::factory()->create([
            'two_factor_secret' => Crypt::encryptString($this->totp->generateSecret()),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($codes)),
            'two_factor_confirmed_at' => now(),
        ]);

        // First use succeeds
        $this->actingAs($user)->post(route('two-factor.verify'), ['code' => '', 'recovery' => 'ABCD-1234'])
            ->assertRedirect(route('dashboard'));

        $remaining = json_decode(Crypt::decryptString($user->fresh()->two_factor_recovery_codes), true);
        $this->assertSame(['EFGH-5678'], $remaining);
    }

    public function test_trusted_device_cookie_skips_challenge(): void
    {
        $secret = $this->totp->generateSecret();

        $user = User::factory()->create([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('two-factor.verify'), [
            'code' => $this->totp->code($secret), 'remember' => 1,
        ]);

        $response->assertCookie('noorhan_2fa_device');
    }
}