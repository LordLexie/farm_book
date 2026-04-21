<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view farms', 'manage farms',
            'view livestock', 'manage livestock',
            'view milk-productions', 'manage milk-productions',
            'view milk-sales', 'manage milk-sales',
            'view customers', 'manage customers',
            'view suppliers', 'manage suppliers',
            'view invoices', 'manage invoices',
            'view bills', 'manage bills',
            'view purchases', 'manage purchases',
            'view payments', 'manage payments',
            'view quotations', 'manage quotations',
            'view reports', 'manage users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $user = User::firstOrCreate(
            ['email' => 'muigaifrancis@gmail.com'],
            [
                'name'     => 'alexie',
                'password' => Hash::make('100%Formilano'),
            ]
        );

        $user->assignRole('admin');
    }
}
