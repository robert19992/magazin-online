<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Creăm rolurile de bază
        Role::create(['name' => 'supplier']);
        Role::create(['name' => 'customer']);
    }
} 