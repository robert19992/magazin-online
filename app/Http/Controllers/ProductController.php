<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Writer;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('supplier')
            ->when(Auth::user()->isSupplier(), function ($query) {
                return $query->where('supplier_id', Auth::id());
            })
            ->paginate(10);

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'sku' => ['required', 'string', 'max:50', 'unique:products'],
            'is_active' => ['boolean'],
            'market_date' => ['nullable', 'date'],
        ]);

        $validated['supplier_id'] = Auth::id();
        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produs creat cu succes.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'category' => ['required', 'string', 'max:100'],
            'specifications' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produs actualizat cu succes.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produs șters cu succes.');
    }

    public function import()
    {
        return view('products.import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'delimiter' => 'required|in:,,;,\t',
            'has_header' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        try {
            $csv = Reader::createFromPath($request->file('csv_file')->getPathname());
            $csv->setDelimiter($request->delimiter);
            $csv->setHeaderOffset($request->has_header ? 0 : null);

            $records = $csv->getRecords();
            $imported = 0;
            $updated = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($records as $index => $record) {
                $validator = Validator::make($record, [
                    'cod_produs' => 'required|string',
                    'descriere' => 'required|string|max:255',
                    'producator_masina' => 'nullable|string|max:100',
                    'greutate' => 'nullable|numeric|min:0',
                    'pret' => 'required|numeric|min:0',
                    'stoc' => 'required|integer|min:0',
                    'unitate_masura' => 'nullable|string|max:10',
                    'specificatii_tehnice' => 'nullable|string',
                    'categorie' => 'nullable|string|max:50'
                ]);

                if ($validator->fails()) {
                    $errors[] = "Linia " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                $data = $validator->validated();
                $data['supplier_id'] = auth()->id();

                if ($request->update_existing) {
                    $product = Product::where('cod_produs', $data['cod_produs'])
                        ->where('supplier_id', auth()->id())
                        ->first();

                    if ($product) {
                        $product->update($data);
                        $updated++;
                    } else {
                        Product::create($data);
                        $imported++;
                    }
                } else {
                    Product::create($data);
                    $imported++;
                }
            }

            DB::commit();

            $message = "Import finalizat: $imported produse importate";
            if ($request->update_existing) {
                $message .= ", $updated produse actualizate";
            }
            if (!empty($errors)) {
                $message .= ". Erori: " . implode('; ', $errors);
            }

            return redirect()->route('products.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Eroare la import: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate()
    {
        $csv = Writer::createFromString('');
        $csv->setDelimiter(',');

        // Adăugăm antetul
        $csv->insertOne([
            'cod_produs',
            'descriere',
            'producator_masina',
            'greutate',
            'pret',
            'stoc',
            'unitate_masura',
            'specificatii_tehnice',
            'categorie'
        ]);

        // Adăugăm un exemplu
        $csv->insertOne([
            'PROD001',
            'Exemplu produs',
            'Producator Exemplu',
            '1.5',
            '100.00',
            '10',
            'buc',
            'Specificații tehnice exemplu',
            'Categorie Exemplu'
        ]);

        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_produse.csv"'
        ]);
    }

    public function updateStock(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'quantity' => ['required', 'integer'],
        ]);

        if ($product->updateStock($validated['quantity'])) {
            return redirect()->route('products.show', $product)
                ->with('success', 'Stoc actualizat cu succes.');
        }

        return back()->with('error', 'Nu s-a putut actualiza stocul.');
    }
}
