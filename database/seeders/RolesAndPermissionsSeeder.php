<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = ['leads','customers','companies','products','suppliers','enquiries',
            'quotations','orders','invoices','payments','marketing','whatsapp','reports',
            'system','users','settings'];

        $actions = ['view','create','update','delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$module}.{$action}");
            }
        }

        Permission::findOrCreate('quotations.approve');
        Permission::findOrCreate('invoices.record');
        Permission::findOrCreate('system.export');

        // Flush cache so syncPermissions resolves fresh from DB, not stale cache.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all     = Permission::all()->pluck('name')->all();
        $allObjs = Permission::all(); // collection for syncPermissions

        $viewAll = collect($modules)->map(fn ($m) => "{$m}.view")->all();

        $salesCore = ['leads.view','leads.create','leads.update','customers.view','customers.create','customers.update',
            'enquiries.view','enquiries.create','enquiries.update','quotations.view','quotations.create','quotations.update',
            'orders.view','orders.create','orders.update','suppliers.view','products.view'];

        $managerCore = array_merge($salesCore, ['companies.view','companies.create','companies.update','suppliers.create',
            'suppliers.update','reports.view','payments.view','invoices.view','orders.delete']);

        $roles = [
            'Super Admin' => $all,
            'CEO' => array_merge($viewAll, ['reports.view','quotations.approve','system.view','system.export']),
            'Sales Manager' => array_merge($managerCore, ['quotations.approve','leads.delete','customers.delete']),
            'Sales Executive' => $salesCore,
            'Brand Specialist' => ['products.view','products.create','products.update','marketing.view','marketing.create',
                'marketing.update','whatsapp.view','whatsapp.create','suppliers.view','leads.view','customers.view'],
            'Distributor Manager' => $managerCore,
            'Dealer Manager' => $managerCore,
            'Garage Manager' => $managerCore,
            'Marketing' => ['marketing.view','marketing.create','marketing.update','marketing.delete','whatsapp.view',
                'whatsapp.create','whatsapp.update','leads.view','customers.view','reports.view'],
            'Finance' => ['invoices.view','invoices.create','invoices.update','invoices.record','payments.view',
                'payments.create','payments.update','payments.delete','customers.view','orders.view','reports.view','system.export'],
            'HR' => ['users.view','users.create','users.update','system.view','reports.view'],
            'Support' => ['customers.view','customers.update','leads.view','enquiries.view'],
            'Reception' => ['leads.view','leads.create','customers.view','customers.create'],
            'Customer' => [], // portal scoping arrives with the Customer Portal module
        ];

        foreach ($roles as $name => $permissions) {
            // Use Permission model collection to avoid cache-miss PermissionDoesNotExist.
            $permCollection = $allObjs->whereIn('name', $permissions);
            Role::findOrCreate($name)->syncPermissions($permCollection);
        }

        // Bootstrap: first user becomes Super Admin
        User::query()->orderBy('id')->first()?->syncRoles('Super Admin');
    }
}