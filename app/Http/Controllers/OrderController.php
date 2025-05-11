<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Organization;
use App\Services\IdocService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $idocService;

    public function __construct(IdocService $idocService)
    {
        $this->idocService = $idocService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['client', 'supplier', 'items.product'])
            ->when(Auth::user()->isSupplier(), function ($query) {
                return $query->where('supplier_id', Auth::id());
            })
            ->when(Auth::user()->isClient(), function ($query) {
                return $query->where('client_id', Auth::id());
            })
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::active()
            ->when(Auth::user()->isClient(), function ($query) {
                return $query->whereHas('supplier', function ($q) {
                    $q->whereHas('supplierConnections', function ($q) {
                        $q->where('client_id', Auth::id())
                            ->where('status', 'active');
                    });
                });
            })
            ->get();

        return view('orders.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'client_id' => Auth::id(),
                'supplier_id' => $validated['supplier_id'],
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if (!$product->hasStock($item['quantity'])) {
                    throw new \Exception("Produsul {$product->name} nu are stoc suficient.");
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $item['quantity'],
                ]);

                $product->updateStock(-$item['quantity']);
                $total += $product->price * $item['quantity'];
            }

            $order->update(['total_amount' => $total]);

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Comandă creată cu succes.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['client', 'supplier', 'items.product']);
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        if (!$order->canBeCancelled()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Această comandă nu mai poate fi editată.');
        }

        $suppliers = Organization::suppliers()->active()->get();
        $customers = Organization::customers()->active()->get();
        return view('orders.edit', compact('order', 'suppliers', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        if (!$order->canBeCancelled()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Această comandă nu mai poate fi actualizată.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'requested_delivery_date' => 'nullable|date',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'required|string|size:2',
        ]);

        $order->update($validated);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Comanda a fost actualizată cu succes.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        if (!$order->canBeCancelled()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Această comandă nu mai poate fi ștearsă.');
        }

        try {
            DB::beginTransaction();

            // Restaurăm stocul pentru fiecare produs
            foreach ($order->items as $item) {
                $item->product->updateStock($item->quantity);
            }

            // Ștergem comanda
            $order->delete();

            DB::commit();

            return redirect()
                ->route('orders.index')
                ->with('success', 'Comanda a fost ștearsă cu succes.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Eroare la ștergerea comenzii: ' . $e->getMessage());
        }
    }

    /**
     * Confirmă comanda (doar pentru furnizori).
     */
    public function confirm(Order $order)
    {
        if (!auth()->user()->isSupplier() || $order->furnizor_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Doar comenzile în așteptare pot fi confirmate.');
        }

        try {
            DB::beginTransaction();

            // Simulăm un răspuns IDOC de la ERP
            $responseContent = [
                'E1EDK01' => ['STATUS' => 'CONFIRMED'],
                'E1EDP01' => []
            ];

            // Adăugăm confirmarea pentru fiecare articol
            foreach ($order->items as $item) {
                $responseContent['E1EDP01'][] = [
                    'POSEX' => $item->id,
                    'STATUS' => 'confirmed',
                    'MENGE' => $item->quantity
                ];
            }

            // Procesăm răspunsul
            $this->idocService->processSupplierResponse($responseContent, $order->idocMessages->first()->correlation_id);

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Comanda a fost confirmată cu succes.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Eroare la confirmarea comenzii: ' . $e->getMessage());
        }
    }

    /**
     * Anulează comanda (doar pentru clienți și doar pentru comenzile în așteptare).
     */
    public function cancel(Order $order)
    {
        // Verificăm dacă utilizatorul are dreptul să anuleze comanda
        if (auth()->user()->isClient() && $order->client_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'Această comandă nu mai poate fi anulată.');
        }

        try {
            DB::beginTransaction();

            $order->update(['status' => 'cancelled']);

            // Simulăm un IDOC de anulare
            $cancelContent = [
                'E1EDK01' => [
                    'ACTION' => '003',
                    'STATUS' => 'CANCELLED',
                    'BELNR' => $order->order_number
                ]
            ];

            // Trimitem IDOC-ul de anulare
            $this->idocService->generateOrderIdoc($order);

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Comanda a fost anulată cu succes.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Eroare la anularea comenzii: ' . $e->getMessage());
        }
    }

    /**
     * Generează un număr unic de comandă
     */
    private function generateOrderNumber()
    {
        $prefix = auth()->user()->isClient() ? 'ORD' : 'RFQ';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:processing,completed,cancelled'],
        ]);

        try {
            DB::beginTransaction();

            switch ($validated['status']) {
                case 'processing':
                    if (!$order->process()) {
                        throw new \Exception('Comanda nu poate fi procesată.');
                    }
                    break;
                case 'completed':
                    if (!$order->complete()) {
                        throw new \Exception('Comanda nu poate fi finalizată.');
                    }
                    break;
                case 'cancelled':
                    if (!$order->cancel()) {
                        throw new \Exception('Comanda nu poate fi anulată.');
                    }
                    // Returnăm produsele în stoc
                    foreach ($order->items as $item) {
                        $item->product->updateStock($item->quantity);
                    }
                    break;
            }

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Statusul comenzii a fost actualizat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $orders = Order::forSupplier(auth()->id())
            ->with(['customer', 'items.product'])
            ->when($request->status, function ($query, $status) {
                return $query->byStatus($status);
            })
            ->when($request->date_from, function ($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->get();

        // Logica pentru export CSV sau PDF
        // ...

        return back()->with('success', 'Comenzile au fost exportate cu succes.');
    }

    public function markAsDelivered(Order $order)
    {
        $this->authorize('update', $order);

        if ($order->status !== 'activa') {
            return back()->withErrors(['error' => 'Doar comenzile active pot fi marcate ca livrate.']);
        }

        $order->markAsDelivered();

        return redirect()->route('orders.show', $order)
            ->with('success', 'Comanda a fost marcată ca livrată.');
    }
}
