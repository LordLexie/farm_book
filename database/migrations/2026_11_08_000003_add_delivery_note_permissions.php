<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    // Delivery-note permissions mirror invoice permissions for existing roles.
    private array $map = [
        'view delivery-notes'   => 'view invoices',
        'manage delivery-notes' => 'manage invoices',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->map as $new => $existing) {
            $permission = Permission::firstOrCreate(['name' => $new, 'guard_name' => 'web']);

            Role::all()
                ->filter(fn (Role $role) => $role->hasPermissionTo($existing, 'web'))
                ->each(fn (Role $role) => $role->givePermissionTo($permission));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', array_keys($this->map))->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
