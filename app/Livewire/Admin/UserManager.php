<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    public bool $formOpen = false;
    public ?int $editingId = null;
    public ?int $customer_id = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public array $roles = [];
    public bool $is_active = true;

    #[On('open-user-form')]
    public function openForm(?int $userId = null): void
    {
        $this->editingId = $userId;
        $this->roles = [];

        if ($userId) {
            $user = User::findOrFail($userId);

            Gate::authorize('update', $user);

            [$this->name, $this->email, $this->is_active] = [
                $user->name,
                $user->email,
                $user->is_active,
            ];

            $this->customer_id = $user->customer_id;

            $this->roles = $user->roles->pluck('name')->all();
            $this->password = '';
        } else {
            Gate::authorize('create', User::class);

            $this->reset([
                'name',
                'email',
                'password',
                'customer_id',
            ]);

            $this->roles = [];
            $this->is_active = true;
        }

        $this->formOpen = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:160',
                'unique:users,email,' . ($this->editingId ?? 'null'),
            ],
            'password' => [
                $this->editingId ? 'nullable' : 'required',
                'string',
                'min:8',
            ],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $user = $this->editingId
            ? User::findOrFail($this->editingId)
            : new User();

        $user->fill([
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'customer_id' => $this->customer_id ?: null,
        ]);

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        $user->syncRoles($this->roles);

        // Automatically assign Customer role to linked portal accounts
        // when no role has been selected.
        if ($user->customer_id && $user->roles()->count() === 0) {
            $user->syncRoles('Customer');
        }

        $this->formOpen = false;

        $this->dispatch(
            'notify',
            message: 'User saved.',
            type: 'success'
        );
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);

        Gate::authorize('update', $user);

        abort_if(
            $user->id === auth()->id(),
            422,
            'You cannot deactivate yourself.'
        );

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $user->logActivity(
            $user->is_active
                ? 'account re-activated'
                : 'account deactivated'
        );

        $this->dispatch(
            'notify',
            message: 'User status updated.',
            type: 'success'
        );
    }

    public function render(): View
    {
        return view('livewire.admin.user-manager', [
            'users' => User::with('roles')
                ->orderBy('name')
                ->get(),

            'allRoles' => Role::orderBy('name')->get(),

            'customers' => Customer::orderBy('name')->get(),
        ]);
    }
}