<?php
// database/seeders/AdminScoreEntryPermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminScoreEntryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Admin score entry permissions
        $permissions = [
            'View admin-score-entry',
            'Create admin-score-entry',
            'Update admin-score-entry',
            'Delete admin-score-entry',
            'View teacher-subject-list',
        ];

        $titles = [
            'View admin-score-entry' => 'Admin Score Entry - View',
            'Create admin-score-entry' => 'Admin Score Entry - Create',
            'Update admin-score-entry' => 'Admin Score Entry - Update',
            'Delete admin-score-entry' => 'Admin Score Entry - Delete',
            'View teacher-subject-list' => 'View Teacher Subject Assignments',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['title' => $titles[$permission] ?? 'Admin Score Entry Management']
            );
        }

        // Assign to admin role (assuming 'admin' role exists)
        $adminRole = Role::firstWhere('name', 'admin');
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }
}
