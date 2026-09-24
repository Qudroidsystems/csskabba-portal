<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ResultAccessPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['View result-access', 'Update result-access'];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['title' => 'Result Access Control']
            );
        }

        // Give them to Super Admin straight away if that role exists.
        $role = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
