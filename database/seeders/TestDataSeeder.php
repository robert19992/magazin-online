<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creăm organizații
        $autoPartsOrg = Organization::create([
            'name' => 'AutoParts SRL',
            'tax_id' => 'RO12345678',
            'address' => 'Str. Principală nr. 1',
            'city' => 'București',
            'county' => 'București',
            'country' => 'RO',
            'phone' => '0722123456',
            'email' => 'contact@autoparts.test',
        ]);

        $carServiceOrg = Organization::create([
            'name' => 'Car Service SRL',
            'tax_id' => 'RO87654321',
            'address' => 'Str. Secundară nr. 2',
            'city' => 'Cluj-Napoca',
            'county' => 'Cluj',
            'country' => 'RO',
            'phone' => '0733123456',
            'email' => 'contact@carservice.test',
        ]);

        // Creăm utilizatori
        $supplier = User::create([
            'name' => 'Furnizor Test',
            'email' => 'furnizor@test.com',
            'password' => Hash::make('password'),
            'account_type' => 'supplier',
            'organization_id' => $autoPartsOrg->id,
            'email_verified_at' => now(),
        ]);

        $customer = User::create([
            'name' => 'Client Test',
            'email' => 'client@test.com',
            'password' => Hash::make('password'),
            'account_type' => 'customer',
            'organization_id' => $carServiceOrg->id,
            'email_verified_at' => now(),
        ]);

        // Creăm produse pentru furnizor
        $products = [
            [
                'code' => 'FLT001',
                'description' => 'Filtru ulei pentru motoare 1.6-2.0',
                'price' => 35.99,
            ],
            [
                'code' => 'BRK002',
                'description' => 'Set plăcuțe frână față',
                'price' => 149.99,
            ],
            [
                'code' => 'OIL003',
                'description' => 'Ulei motor 5W40 5L',
                'price' => 189.99,
            ],
            [
                'code' => 'FLT004',
                'description' => 'Filtru aer motor',
                'price' => 45.99,
            ],
            [
                'code' => 'BLT005',
                'description' => 'Set bujii',
                'price' => 89.99,
            ],
        ];

        foreach ($products as $productData) {
            Product::create([
                'supplier_id' => $supplier->id,
                'code' => $productData['code'],
                'description' => $productData['description'],
                'price' => $productData['price'],
            ]);
        }

        $this->command->info('Date de test create cu succes!');
        $this->command->info('Furnizor: furnizor@test.com / password');
        $this->command->info('Client: client@test.com / password');
    }
}
