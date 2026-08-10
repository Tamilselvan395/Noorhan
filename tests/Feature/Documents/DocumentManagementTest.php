<?php

namespace Tests\Feature\Documents;

use App\Actions\Documents\DeleteDocumentAction;
use App\Actions\Documents\UploadDocumentAction;
use App\Livewire\Documents\DocumentCenter;
use App\Livewire\Documents\DocumentUploader;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    public function test_generic_upload_attaches_to_any_entity(): void
    {
        $lead = Lead::factory()->create();
        $customer = Customer::factory()->create();

        $action = app(UploadDocumentAction::class);

        $action->execute($lead, UploadedFile::fake()->create('spec.pdf', 100, 'application/pdf'), $this->user, 'contract');
        $action->execute($customer, UploadedFile::fake()->create('license.pdf', 100, 'application/pdf'), $this->user, 'license', now()->addYear()->toDateString());

        $this->assertDatabaseHas('documents', ['documentable_type' => Lead::class, 'category' => 'contract']);
        $this->assertDatabaseHas('documents', ['documentable_type' => Customer::class, 'category' => 'license']);
    }

    public function test_uploader_component_uploads_with_category(): void
    {
        $lead = Lead::factory()->create();

        Livewire::actingAs($this->user)
            ->test(DocumentUploader::class, ['entity' => $lead])
            ->set('file', UploadedFile::fake()->create('drawing.pdf', 50))
            ->set('category', 'drawing')
            ->call('upload')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documents', ['documentable_id' => $lead->id, 'category' => 'drawing']);
    }

    public function test_document_center_lists_across_entities_and_filters(): void
    {
        $lead = Lead::factory()->create();
        $customer = Customer::factory()->create();

        app(UploadDocumentAction::class)->execute($lead, UploadedFile::fake()->create('lead-file.pdf', 10), $this->user, 'contract');
        app(UploadDocumentAction::class)->execute($customer, UploadedFile::fake()->create('cust-file.pdf', 10), $this->user, 'license');

        Livewire::actingAs($this->user)
            ->test(DocumentCenter::class)
            ->assertSee('lead-file.pdf')
            ->assertSee('cust-file.pdf')
            ->set('category', 'license')
            ->assertDontSee('lead-file.pdf')
            ->assertSee('cust-file.pdf');
    }

    public function test_download_requires_auth_and_streams_file(): void
    {
        $lead = Lead::factory()->create();
        $doc = app(UploadDocumentAction::class)->execute($lead, UploadedFile::fake()->create('file.pdf', 10), $this->user);

        $this->get(route('documents.download', $doc))->assertRedirect(); // guest → login

        $this->actingAs($this->user)->get(route('documents.download', $doc))->assertDownload('file.pdf');
    }

    public function test_delete_removes_row_and_physical_file(): void
    {
        $lead = Lead::factory()->create();
        $doc = app(UploadDocumentAction::class)->execute($lead, UploadedFile::fake()->create('gone.pdf', 10), $this->user);

        Storage::disk('public')->assertExists($doc->path);

        app(DeleteDocumentAction::class)->execute($doc);

        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
        Storage::disk('public')->assertMissing($doc->path);
    }

    public function test_delete_is_permission_gated(): void
    {
        $lead = Lead::factory()->create();
        $doc = app(UploadDocumentAction::class)->execute($lead, UploadedFile::fake()->create('mine.pdf', 10), $this->user);

        $other = User::factory()->create();

        Livewire::actingAs($other)
            ->test(DocumentCenter::class)
            ->call('delete', $doc->id)
            ->assertForbidden();
    }

    public function test_expiry_alerts_notify_uploader_once(): void
    {
        $lead = Lead::factory()->create();
        $doc = app(UploadDocumentAction::class)->execute(
            $lead, UploadedFile::fake()->create('license.pdf', 10), $this->user, 'license', now()->addDays(5)->toDateString(),
        );

        $this->artisan('documents:expiry-alerts')->assertSuccessful();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->user->id]);

        // Second run same day: deduped
        $this->artisan('documents:expiry-alerts')->assertSuccessful();
        $this->assertSame(1, $this->user->notifications()->count());
    }

    public function test_expiring_scope_and_flags(): void
    {
        $lead = Lead::factory()->create();

        $expiring = app(UploadDocumentAction::class)->execute($lead, UploadedFile::fake()->create('a.pdf', 1), $this->user, 'license', now()->addDays(3)->toDateString());
        $fine = app(UploadDocumentAction::class)->execute($lead, UploadedFile::fake()->create('b.pdf', 1), $this->user, 'license', now()->addYear()->toDateString());

        $this->assertTrue($expiring->isExpiringSoon());
        $this->assertFalse($fine->isExpiringSoon());
        $this->assertSame(1, Document::expiringSoon(14)->count());
    }
}