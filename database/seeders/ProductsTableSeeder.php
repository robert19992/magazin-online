<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'supplier_id' => 1,
                'cod_produs' => 'FLT001',
                'descriere' => 'Filtru ulei pentru motoare 1.6-2.0',
                'producator_masina' => 'MANN',
                'categorie' => 'Filtre',
                'greutate' => 0.5,
                'pret' => 45.99,
                'stoc' => 100,
                'unitate_masura' => 'buc',
                'specificatii_tehnice' => json_encode([
                    'tip_motor' => '1.6-2.0',
                    'dimensiuni' => '76x76x123mm'
                ])
            ],
            [
                'supplier_id' => 1,
                'cod_produs' => 'PLK002',
                'descriere' => 'Plăcuțe frână față pentru VW Golf 7',
                'producator_masina' => 'TRW',
                'categorie' => 'Frânare',
                'greutate' => 1.2,
                'pret' => 189.99,
                'stoc' => 50,
                'unitate_masura' => 'buc',
                'specificatii_tehnice' => json_encode([
                    'model_masina' => 'VW Golf 7',
                    'pozitie' => 'față'
                ])
            ],
            [
                'supplier_id' => 1,
                'cod_produs' => 'ULI003',
                'descriere' => 'Ulei motor 5W-40 sintetic',
                'producator_masina' => 'Castrol',
                'categorie' => 'Uleiuri',
                'greutate' => 4.5,
                'pret' => 159.99,
                'stoc' => 200,
                'unitate_masura' => 'L',
                'specificatii_tehnice' => json_encode([
                    'vascozitate' => '5W-40',
                    'tip' => 'sintetic',
                    'specificatii' => 'API SN, ACEA A3/B4'
                ])
            ],
            [
                'supplier_id' => 1,
                'cod_produs' => 'AMT004',
                'descriere' => 'Amortizor spate pentru Dacia Logan',
                'producator_masina' => 'Monroe',
                'categorie' => 'Suspensie',
                'greutate' => 3.8,
                'pret' => 245.99,
                'stoc' => 30,
                'unitate_masura' => 'buc',
                'specificatii_tehnice' => json_encode([
                    'model_masina' => 'Dacia Logan',
                    'pozitie' => 'spate',
                    'tip' => 'gaz'
                ])
            ],
            [
                'supplier_id' => 1,
                'cod_produs' => 'BUJ005',
                'descriere' => 'Set bujii pentru motoare pe benzină',
                'producator_masina' => 'Bosch',
                'categorie' => 'Aprindere',
                'greutate' => 0.2,
                'pret' => 89.99,
                'stoc' => 150,
                'unitate_masura' => 'buc',
                'specificatii_tehnice' => json_encode([
                    'tip_motor' => 'benzină',
                    'cantitate_set' => '4 buc'
                ])
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 