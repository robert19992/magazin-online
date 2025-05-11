<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the suppliers.
     */
    public function index()
    {
        $suppliers = User::where('account_type', 'supplier')
            ->with('organization')
            ->paginate(12);

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Display the specified supplier's products.
     */
    public function products(User $supplier)
    {
        if ($supplier->account_type !== 'supplier') {
            abort(404);
        }

        $products = $supplier->products()
            ->orderBy('code')
            ->paginate(20);

        return view('suppliers.products', compact('supplier', 'products'));
    }
} 