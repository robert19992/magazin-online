<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Product([
            'code' => $row['cod_produs'],
            'description' => $row['descriere'],
            'car_manufacturer' => $row['producator_masina'],
            'weight' => $row['greutate'],
            'price' => $row['pret'],
            'stock' => $row['stoc'],
            'supplier_id' => Auth::id(),
            'created_at' => now(),
        ]);
    }
} 