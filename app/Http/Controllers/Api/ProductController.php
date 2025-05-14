<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Returnează produsele unui furnizor
     *
     * @param int $supplier
     * @return JsonResponse
     */
    public function getSupplierProducts($supplier): JsonResponse
    {
        Log::info('Se caută produse pentru furnizorul: ' . $supplier);
        
        $products = Product::where('supplier_id', $supplier)
            ->where('stock', '>', 0)
            ->get();
        
        Log::info('Produse găsite: ' . $products->count());
        
        return response()->json($products);
    }
}