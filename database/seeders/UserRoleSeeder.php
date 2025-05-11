<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Atribuim rolul de furnizor primului utilizator
        $supplierRole = Role::where('name', 'supplier')->first();
        $user = User::first();
        if ($user && $supplierRole) {
            $user->roles()->attach($supplierRole->id);
        }

        // Atribuim rolul de client celorlalți utilizatori
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            User::whereDoesntHave('roles')->get()->each(function ($user) use ($customerRole) {
                $user->roles()->attach($customerRole->id);
            });
        }
    }
} 