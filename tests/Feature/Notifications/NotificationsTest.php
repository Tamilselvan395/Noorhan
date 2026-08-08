<?php

namespace Tests\Feature\Notifications;

use App\Actions\Quotations\ApproveQuotationAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\Quotations\SubmitForApprovalAction;
use App\Livewire\Notifications\NotificationBell;
use App\Livewire\Notifications\PreferencesForm;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\Sales\ApprovalRequiredNotification;
use App\Notifications\Sales\QuotationApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->manager = User::factory()->create();
    }

    private function makeQuotation()
    {
        return app(CreateQuotationAction::class)->execute(
            ['division' => 'automotive', 'status' => 'draft', 'tax_rate' => 5],
            [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0]],
            $this->user,
        );
    }

    public function test_approval_submission_notifies_everyone_except_author(): void
    {
        Notification::fake();

        $quotation = $this->makeQuotation();
        app(SubmitForApprovalAction::class)->execute($quotation);

        Notification::assertSentTo($this->manager, ApprovalRequiredNotification::class);
        Notification::assertNotSentTo($this->user, ApprovalRequiredNotification::class);
    }

    public function test_approval_notifies_creator(): void
    {
        Notification::fake();

        $quotation = $this->makeQuotation();
        app(SubmitForApprovalAction::class)->execute($quotation);
        app(ApproveQuotationAction::class)->execute($quotation->fresh(), $this->manager);

        Notification::assertSentTo($this->user, QuotationApprovedNotification::class);
    }

    public function test_preferences_control_channels(): void
    {
        Notification::fake();

        // User disables mail for sales category
        $this->user->updatePreferences(['sales' => ['database' => true, 'mail' => false]]);

        $quotation = $this->makeQuotation();
        app(SubmitForApprovalAction::class)->execute($quotation);
        app(ApproveQuotationAction::class)->execute($quotation->fresh(), $this->manager);

        Notification::assertSentTo($this->user, QuotationApprovedNotification::class, function ($notification, $channels) {
            return $channels === ['database'];
        });
    }

    public function test_preferences_form_saves(): void
    {
        Livewire::actingAs($this->user)
            ->test(PreferencesForm::class)
            ->set('prefs.finance.mail', false)
            ->call('save');

        $this->assertFalse($this->user->fresh()->prefersChannel('finance', 'mail'));
        $this->assertTrue($this->user->fresh()->prefersChannel('finance', 'database'));
    }

    public function test_bell_shows_unread_and_marks_all_read(): void
    {
        $this->user->notify(new ApprovalRequiredNotification($this->makeQuotation()));
        $this->assertSame(1, $this->user->unreadNotifications()->count());

        Livewire::actingAs($this->user)
            ->test(NotificationBell::class)
            ->call('markAllRead');

        $this->assertSame(0, $this->user->unreadNotifications()->count());
    }

    public function test_digest_notifies_follow_ups_and_dedupes(): void
    {
        Lead::factory()->create([
            'assigned_to' => $this->user->id,
            'next_follow_up_at' => now()->subHour(),
        ]);

        $this->artisan('notifications:digest')->assertSuccessful();
        $this->assertSame(1, $this->user->unreadNotifications()->count());

        // Second run same day: no duplicate
        $this->artisan('notifications:digest')->assertSuccessful();
        $this->assertSame(1, $this->user->unreadNotifications()->count());
    }
}