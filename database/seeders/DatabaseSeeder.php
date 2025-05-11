<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OrganizationsTableSeeder::class,
            UsersTableSeeder::class,
            ProductsTableSeeder::class,
            RoleSeeder::class,
            UserRoleSeeder::class,
        ]);
    }
}
