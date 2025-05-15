<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Models\User;
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
        $messages = [
            'cod_produs.unique' => 'Acest cod de produs există deja în catalog.'
        ];

        $validated = $request->validate([
            'cod_produs' => ['required', 'string', 'max:255', 'unique:products,cod_produs'],
            'description' => ['required', 'string'],
            'manufacturer' => ['required', 'string', 'max:100'],
            'weight' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'market_date' => ['required', 'date'],
        ], $messages);

        // Generăm automat SKU din cod_produs 
        $validated['sku'] = $validated['cod_produs'] . '-' . time();
        $validated['supplier_id'] = Auth::id();
        
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

        $messages = [
            'cod_produs.unique' => 'Acest cod de produs există deja în catalog.'
        ];

        $validated = $request->validate([
            'cod_produs' => ['required', 'string', 'max:255', 'unique:products,cod_produs,' . $product->id],
            'description' => ['required', 'string'],
            'manufacturer' => ['required', 'string', 'max:100'],
            'weight' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'market_date' => ['required', 'date'],
        ], $messages);

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
            'has_header' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        try {
            $csv = Reader::createFromPath($request->file('csv_file')->getPathname());
            // Setăm delimitatorul fix la virgulă
            $csv->setDelimiter(',');
            
            if ($request->has_header) {
                $headers = $csv->fetchOne();
                $csv->setHeaderOffset(0);
            }
            
            // Debug - scrie în log headerele detectate
            Log::info('CSV Headers detectate:', isset($headers) ? $headers : ['No headers detected']);

            $records = $csv->getRecords();
            $imported = 0;
            $updated = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($records as $index => $record) {
                Log::info('CSV Record:', $record);
                
                // Mapăm denumirile românești la câmpurile din baza de date
                // Folosim multiple variante pentru fiecare câmp pentru a crește șansele de potrivire
                $mappedRecord = [];
                
                // Cod Produs
                if (isset($record['Cod Produs'])) {
                    $mappedRecord['cod_produs'] = $record['Cod Produs'];
                } elseif (isset($record['cod_produs'])) {
                    $mappedRecord['cod_produs'] = $record['cod_produs'];
                } elseif (isset($record['Cod produs'])) {
                    $mappedRecord['cod_produs'] = $record['Cod produs']; 
                } elseif (isset($record['cod produs'])) {
                    $mappedRecord['cod_produs'] = $record['cod produs'];
                } elseif (isset($record[0])) { 
                    $mappedRecord['cod_produs'] = $record[0]; // Prima coloană dacă nu are header
                }
                
                // Descriere
                if (isset($record['Descriere'])) {
                    $mappedRecord['description'] = $record['Descriere'];
                } elseif (isset($record['description'])) {
                    $mappedRecord['description'] = $record['description'];
                } elseif (isset($record['descriere'])) {
                    $mappedRecord['description'] = $record['descriere'];
                } elseif (isset($record[1])) {
                    $mappedRecord['description'] = $record[1]; // A doua coloană
                }
                
                // Producator Masina
                if (isset($record['Producator Masina'])) {
                    $mappedRecord['manufacturer'] = $record['Producator Masina'];
                } elseif (isset($record['manufacturer'])) {
                    $mappedRecord['manufacturer'] = $record['manufacturer'];
                } elseif (isset($record['producator_masina'])) {
                    $mappedRecord['manufacturer'] = $record['producator_masina'];
                } elseif (isset($record['Producator masina'])) {
                    $mappedRecord['manufacturer'] = $record['Producator masina'];
                } elseif (isset($record['producator masina'])) {
                    $mappedRecord['manufacturer'] = $record['producator masina'];
                } elseif (isset($record[2])) {
                    $mappedRecord['manufacturer'] = $record[2]; // A treia coloană
                }
                
                // Greutate
                if (isset($record['Greutate'])) {
                    $mappedRecord['weight'] = $record['Greutate'];
                } elseif (isset($record['weight'])) {
                    $mappedRecord['weight'] = $record['weight'];
                } elseif (isset($record['greutate'])) {
                    $mappedRecord['weight'] = $record['greutate'];
                } elseif (isset($record[3])) {
                    $mappedRecord['weight'] = $record[3]; // A patra coloană
                }
                
                // Pret
                if (isset($record['Pret'])) {
                    $mappedRecord['price'] = $record['Pret'];
                } elseif (isset($record['price'])) {
                    $mappedRecord['price'] = $record['price'];
                } elseif (isset($record['pret'])) {
                    $mappedRecord['price'] = $record['pret'];
                } elseif (isset($record[4])) {
                    $mappedRecord['price'] = $record[4]; // A cincea coloană
                }
                
                // Stoc
                if (isset($record['Stoc'])) {
                    $mappedRecord['stock'] = $record['Stoc'];
                } elseif (isset($record['stock'])) {
                    $mappedRecord['stock'] = $record['stock'];
                } elseif (isset($record['stoc'])) {
                    $mappedRecord['stock'] = $record['stoc'];
                } elseif (isset($record[5])) {
                    $mappedRecord['stock'] = $record[5]; // A șasea coloană
                }
                
                // Data Introducere pe piata
                if (isset($record['Data Introducere pe piata'])) {
                    $mappedRecord['market_date'] = $record['Data Introducere pe piata'];
                } elseif (isset($record['market_date'])) {
                    $mappedRecord['market_date'] = $record['market_date'];
                } elseif (isset($record['data introducere pe piata'])) {
                    $mappedRecord['market_date'] = $record['data introducere pe piata'];
                } elseif (isset($record['data_introducere_pe_piata'])) {
                    $mappedRecord['market_date'] = $record['data_introducere_pe_piata'];
                } elseif (isset($record['Data introducere pe piata'])) {
                    $mappedRecord['market_date'] = $record['Data introducere pe piata'];
                } elseif (isset($record[6])) {
                    $mappedRecord['market_date'] = $record[6]; // A șaptea coloană
                }
                
                Log::info('Mapped Record:', $mappedRecord);
                
                // Validăm datele din CSV cu aceleași reguli ca la adăugarea manuală
                $validator = Validator::make($mappedRecord, [
                    'cod_produs' => 'required|string|max:255',
                    'description' => 'required|string',
                    'manufacturer' => 'required|string|max:100',
                    'weight' => 'required|numeric|min:0',
                    'price' => 'required|numeric|min:0',
                    'stock' => 'required|integer|min:0',
                    'market_date' => 'required|date'
                ]);

                if ($validator->fails()) {
                    $errors[] = "Linia " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                $data = $validator->validated();
                $data['supplier_id'] = auth()->id();

                // Generăm automat SKU din cod_produs
                $data['sku'] = $data['cod_produs'] . '-' . time() . '-' . $index;

                // Verificăm dacă există deja un produs cu acest cod
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
                    // Verificare pentru unicitate cod_produs
                    if (Product::where('cod_produs', $data['cod_produs'])->exists()) {
                        $errors[] = "Linia " . ($index + 1) . ": Codul de produs '{$data['cod_produs']}' există deja în catalog.";
                        continue;
                    }
                    
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
            Log::error('Eroare la import:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Eroare la import: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate()
    {
        $csv = Writer::createFromString('');
        $csv->setDelimiter(',');

        // Adăugăm antetul cu denumirile în română
        $csv->insertOne([
            'Cod Produs',
            'Descriere',
            'Producator Masina',
            'Greutate',
            'Pret',
            'Stoc',
            'Data Introducere pe piata'
        ]);

        // Adăugăm un exemplu
        $csv->insertOne([
            'TEST001',
            'Filtru ulei motor',
            'Mercedes',
            '0.5',
            '45.99',
            '20',
            date('Y-m-d')
        ]);
        
        // Adăugăm încă un exemplu
        $csv->insertOne([
            'TEST002',
            'Filtru aer',
            'BMW',
            '0.3',
            '35.50',
            '15',
            date('Y-m-d', strtotime('-1 day'))
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
