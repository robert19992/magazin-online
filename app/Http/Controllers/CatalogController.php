<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CatalogController extends Controller
{
    public function index()
    {
        $products = Product::with('supplier')->paginate(10);
        return view('catalog.index', compact('products'));
    }

    public function create()
    {
        return view('catalog.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:products,code',
            'description' => 'required|string',
            'manufacturer' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            ...$validated,
            'supplier_id' => auth()->id(),
            'introduction_date' => Carbon::now(),
        ]);

        return redirect()->route('catalog.index')
            ->with('success', 'Produsul a fost adăugat cu succes.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        return view('catalog.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'description' => 'required|string',
            'manufacturer' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('catalog.index')
            ->with('success', 'Produsul a fost actualizat cu succes.');
    }
}
