<?php

namespace Tests\Feature\Api;

use App\Livewire\Developers\TokenManager;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_requests_get_json_401(): void
    {
        $this->getJson('/api/v1/leads')->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_read_token_cannot_write(): void
    {
        $token = $this->user->createToken('reader', ['read'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/leads')
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/leads', ['name' => 'X', 'division' => 'automotive'])
            ->assertForbidden();
    }

    public function test_write_token_creates_lead(): void
    {
        $token = $this->user->createToken('writer', ['read', 'write'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/leads', [
                'name' => 'API Lead',
                'division' => 'swiftec',
                'source' => 'manual',
                'email' => 'api@lead.com',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'API Lead');

        $this->assertDatabaseHas('leads', ['name' => 'API Lead']);
    }

    public function test_validation_errors_are_json(): void
    {
        $token = $this->user->createToken('writer', ['write'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/leads', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_filters_and_pagination_caps(): void
    {
        Lead::factory()->count(5)->create(['division' => 'automotive']);
        Lead::factory()->count(2)->create(['division' => 'wiperex']);

        $token = $this->user->createToken('reader', ['read'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/leads?division=wiperex&per_page=500')
            ->assertOk();

        $this->assertCount(2, $response->json('data'));
        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }

    public function test_products_and_stats_endpoints(): void
    {
        Product::factory()->create();

        $token = $this->user->createToken('reader', ['read'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/products')->assertOk()->assertJsonStructure(['data']);
        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/stats')->assertOk()->assertJsonStructure(['data' => ['leads', 'customers']]);
    }

    public function test_token_manager_creates_and_revokes(): void
    {
        Livewire::actingAs($this->user)
            ->test(TokenManager::class)
            ->set('name', 'Zapier')
            ->set('canWrite', true)
            ->call('createToken')
            ->assertHasNoErrors();

        $this->assertCount(1, $this->user->fresh()->tokens);

        $tokenId = $this->user->fresh()->tokens->first()->id;

        Livewire::actingAs($this->user)
            ->test(TokenManager::class)
            ->call('revoke', $tokenId);

        $this->assertCount(0, $this->user->fresh()->tokens);
    }

    public function test_developers_page_renders(): void
    {
        $this->actingAs($this->user)->get(route('developers.index'))->assertOk()->assertSee('API Reference');
    }
}