<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleManager extends Component
{
    public bool $formOpen = false;
    public ?int $editingId = null;

    public string $name = '';
    public array $permissions = [];

    #[On('open-role-form')]
    public function openForm(?int $roleId = null): void
    {
        Gate::authorize('create', Role::class);

        $this->editingId = $roleId;
        $this->permissions = [];

        if ($roleId) {
            $role = Role::findOrFail($roleId);
            $this->name = $role->name;
            $this->permissions = $role->permissions->pluck('name')->all();
        } else {
            $this->name = '';
        }

        $this->formOpen = true;
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|string|max:60|unique:roles,name,'.($this->editingId ?? 'null')]);

        $role = $this->editingId ? Role::findOrFail($this->editingId) : Role::create(['name' => $this->name, 'guard_name' => 'web']);

        if ($this->editingId && $role->name !== $this->name && $role->name === 'Super Admin') {
            abort(422, 'The Super Admin role cannot be renamed.');
        }

        $role->syncPermissions($this->permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->formOpen = false;
        $this->dispatch('notify', message: 'Role saved.', type: 'success');
    }

    public function delete(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        Gate::authorize('delete', $role);

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->dispatch('notify', message: 'Role deleted.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.admin.role-manager', [
            'roles' => Role::withCount(['permissions', 'users'])->orderBy('name')->get(),
            'groupedPermissions' => Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0]),
        ]);
    }
}