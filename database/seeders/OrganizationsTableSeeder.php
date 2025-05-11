<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creăm organizația furnizorului
        Organization::create([
            'name' => 'Furnizor Auto SRL',
            'type' => 'supplier',
            'teccom_id' => 'SUP123456789',
            'address' => 'Str. Furnizorilor nr. 1',
            'city' => 'București',
            'postal_code' => '012345',
            'country' => 'RO',
            'phone' => '+40721234567',
            'email' => 'contact@furnizor-auto.ro'
        ]);

        // Creăm organizația clientului
        Organization::create([
            'name' => 'Service Auto SRL',
            'type' => 'customer',
            'teccom_id' => 'CUS987654321',
            'address' => 'Str. Service nr. 2',
            'city' => 'București',
            'postal_code' => '054321',
            'country' => 'RO',
            'phone' => '+40727654321',
            'email' => 'contact@service-auto.ro'
        ]);
    }
} 