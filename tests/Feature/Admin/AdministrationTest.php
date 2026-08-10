<?php

namespace Tests\Feature\Admin;

use App\Actions\Quotations\CreateQuotationAction;
use App\Livewire\Admin\RoleManager;
use App\Livewire\Admin\UserManager;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeQuotation(User $author): Quotation
    {
        return app(CreateQuotationAction::class)->execute(
            ['division' => 'automotive', 'status' => 'pending_approval', 'tax_rate' => 5],
            [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0]],
            $author,
        );
    }

    public function test_seeder_creates_all_fourteen_roles(): void
    {
        $this->seedRoles();

        foreach (['Super Admin','CEO','Sales Manager','Sales Executive','Brand Specialist','Distributor Manager',
            'Dealer Manager','Garage Manager','Marketing','Finance','HR','Support','Reception','Customer'] as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role]);
        }

        $this->assertTrue(\Spatie\Permission\Models\Role::findByName('Super Admin')->hasPermissionTo('leads.delete'));
        $this->assertTrue(\Spatie\Permission\Models\Role::findByName('Sales Manager')->hasPermissionTo('quotations.approve'));
        $this->assertFalse(\Spatie\Permission\Models\Role::findByName('Sales Executive')->hasPermissionTo('quotations.approve'));
    }

    public function test_role_based_quotation_approval(): void
    {
        $this->seedRoles();

        $author = User::factory()->create()->syncRoles('Sales Executive');
        $manager = User::factory()->create()->syncRoles('Sales Manager');
        $exec = User::factory()->create()->syncRoles('Sales Executive');

        $quotation = $this->makeQuotation($author);

        $this->assertTrue($manager->can('approve', $quotation));   // has quotations.approve
        $this->assertFalse($exec->can('approve', $quotation));    // lacks permission
        $this->assertFalse($author->can('approve', $quotation));  // self-approval never
    }

    public function test_legacy_fallback_for_roleless_users(): void
    {
        $this->seedRoles();

        $author = User::factory()->create();      // no role
        $other = User::factory()->create();       // no role

        $quotation = $this->makeQuotation($author);

        // Legacy separation-of-duties still applies
        $this->assertTrue($other->can('approve', $quotation));
        $this->assertFalse($author->can('approve', $quotation));
    }

    public function test_super_admin_bypasses_everything(): void
    {
        $this->seedRoles();

        $admin = User::factory()->create()->syncRoles('Super Admin');
        $author = User::factory()->create();

        $this->assertTrue($admin->can('approve', $this->makeQuotation($author)));
        $this->assertTrue($admin->can('delete', \App\Models\Lead::factory()->create()));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password@123'),
            'email_verified_at' => now(),
            'is_active' => false,
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password@123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_pages_are_role_gated(): void
    {
        $this->seedRoles();

        $plain = User::factory()->create();
        $admin = User::factory()->create()->syncRoles('Super Admin');

        $this->actingAs($plain)->get(route('admin.users'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users'))->assertOk();
        $this->actingAs($admin)->get(route('admin.roles'))->assertOk();
    }

    public function test_user_manager_creates_user_with_role_and_toggles(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create()->syncRoles('Super Admin');

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->call('openForm')
            ->set('name', 'New Exec')
            ->set('email', 'exec@noorhan.com')
            ->set('password', 'Password@123')
            ->set('roles', ['Sales Executive'])
            ->call('save')
            ->assertHasNoErrors();

        $created = User::where('email', 'exec@noorhan.com')->first();
        $this->assertTrue($created->hasRole('Sales Executive'));

        Livewire::actingAs($admin)->test(UserManager::class)->call('toggleActive', $created->id);
        $this->assertFalse($created->fresh()->is_active);
    }

    public function test_role_manager_creates_custom_role(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create()->syncRoles('Super Admin');

        Livewire::actingAs($admin)
            ->test(RoleManager::class)
            ->call('openForm')
            ->set('name', 'Intern')
            ->set('permissions', ['leads.view', 'products.view'])
            ->call('save')
            ->assertHasNoErrors();

        $role = \Spatie\Permission\Models\Role::findByName('Intern');
        $this->assertTrue($role->hasPermissionTo('leads.view'));
        $this->assertFalse($role->hasPermissionTo('leads.delete'));
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create()->syncRoles('Super Admin');

        $superId = \Spatie\Permission\Models\Role::findByName('Super Admin')->id;

        Livewire::actingAs($admin)->test(RoleManager::class)->call('delete', $superId)->assertForbidden();
    }
}