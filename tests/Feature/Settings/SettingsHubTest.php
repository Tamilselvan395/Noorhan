<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\SettingsHub;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsHubTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_setting_store_roundtrip_with_cache_invalidation(): void
    {
        Setting::set('defaults.currency', 'AED');
        $this->assertSame('AED', Setting::get('defaults.currency'));

        Setting::set('defaults.currency', 'EUR');
        $this->assertSame('EUR', Setting::get('defaults.currency'), 'Cache must be invalidated on write.');

        $this->assertSame('FALLBACK', Setting::get('missing.key', 'FALLBACK'));
    }

    public function test_settings_page_renders(): void
    {
        $this->actingAs($this->user)->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Settings Center');
    }

    public function test_general_settings_persist(): void
    {
        Livewire::actingAs($this->user)
            ->test(SettingsHub::class)
            ->set('general.company_name', 'Noorhan Group FZE')
            ->set('general.timezone', 'Asia/Dubai')
            ->call('saveGeneral')
            ->assertHasNoErrors();

        $this->assertSame('Noorhan Group FZE', Setting::get('general.company_name'));
    }

    public function test_commercial_defaults_persist_and_validate(): void
    {
        Livewire::actingAs($this->user)
            ->test(SettingsHub::class)
            ->call('switchSection', 'defaults')
            ->set('defaults.currency', 'AED')
            ->set('defaults.quotation_valid_days', '30')
            ->set('defaults.tax_rate', '5')
            ->set('defaults.invoice_due_days', '15')
            ->call('saveDefaults')
            ->assertHasNoErrors();

        $this->assertSame('AED', Setting::get('defaults.currency'));
        $this->assertSame('30', (string) Setting::get('defaults.quotation_valid_days'));

        // Invalid tax rate rejected
        Livewire::actingAs($this->user)
            ->test(SettingsHub::class)
            ->set('defaults.tax_rate', '250')
            ->call('saveDefaults')
            ->assertHasErrors(['defaults.tax_rate']);
    }

    public function test_appearance_saves_user_theme(): void
    {
        Livewire::actingAs($this->user)
            ->test(SettingsHub::class)
            ->call('switchSection', 'appearance')
            ->set('userTheme', 'dark')
            ->call('saveAppearance');

        $this->assertSame('dark', $this->user->fresh()->theme);
        $this->assertSame('dark', Setting::get('appearance.default_theme'));
    }

    public function test_theme_middleware_prefers_user_setting(): void
    {
        $this->user->update(['theme' => 'dark']);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $this->assertSame('dark', $response->viewData('currentTheme'));
    }

    public function test_maintenance_prune_runs(): void
    {
        Livewire::actingAs($this->user)
            ->test(SettingsHub::class)
            ->call('switchSection', 'maintenance')
            ->call('runPrune')
            ->assertSee('Pruned');
    }
}