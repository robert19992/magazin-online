<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creăm un furnizor de test
        User::create([
            'name' => 'Furnizor Test',
            'email' => 'furnizor@test.com',
            'password' => Hash::make('password'),
            'organization_id' => 1,
            'email_verified_at' => now()->addYear(),
        ]);

        // Creăm un client de test
        User::create([
            'name' => 'Client Test',
            'email' => 'client@test.com',
            'password' => Hash::make('password'),
            'organization_id' => 2,
            'email_verified_at' => now()->addYear(),
        ]);

        // Creăm un admin de test
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'organization_id' => 1,
            'email_verified_at' => Carbon::now() // Marcăm contul ca verificat
        ]);
    }
} 