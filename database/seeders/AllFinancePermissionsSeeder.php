<?php
// database/seeders/AllFinancePermissionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AllFinancePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ScholarshipPermissionSeeder::class,
            FinancePermissionSeeder::class,
            SiblingGroupPermissionSeeder::class,
        ]);

        $this->command->info('All finance permissions seeded successfully!');
    }
}
