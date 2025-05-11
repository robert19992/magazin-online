<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use Excel;
use League\Csv\Writer;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:supplier']);
    }

    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());
        $category = $request->input('category');

        // Statistici generale
        $generalStats = Order::where('supplier_id', auth()->id())
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($category, function($query) use ($category) {
                return $query->whereHas('items.product', function($q) use ($category) {
                    $q->where('categorie', $category);
                });
            })
            ->select([
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_value) as total_value'),
                DB::raw('AVG(total_value) as average_order_value')
            ])
            ->first();

        // Top produse vândute
        $topProducts = Product::where('supplier_id', auth()->id())
            ->whereHas('orderItems.order', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when($category, function($query) use ($category) {
                return $query->where('categorie', $category);
            })
            ->withCount(['orderItems as total_quantity' => function($query) use ($startDate, $endDate) {
                $query->whereHas('order', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                });
            }])
            ->withSum(['orderItems as total_value' => function($query) use ($startDate, $endDate) {
                $query->whereHas('order', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                });
            }], DB::raw('quantity * price'))
            ->orderByDesc('total_value')
            ->take(10)
            ->get();

        // Vânzări pe categorii
        $categorySales = Product::where('supplier_id', auth()->id())
            ->whereHas('orderItems.order', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when($category, function($query) use ($category) {
                return $query->where('categorie', $category);
            })
            ->select('categorie', DB::raw('COUNT(DISTINCT order_items.order_id) as total_orders'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('categorie')
            ->get();

        // Vânzări zilnice
        $dailySales = Order::where('supplier_id', auth()->id())
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($category, function($query) use ($category) {
                return $query->whereHas('items.product', function($q) use ($category) {
                    $q->where('categorie', $category);
                });
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_value) as total_value')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Produse cu stoc redus
        $lowStockProducts = Product::where('supplier_id', auth()->id())
            ->where('stoc', '<', 10)
            ->when($category, function($query) use ($category) {
                return $query->where('categorie', $category);
            })
            ->get();

        // Facturi restante
        $overdueInvoices = Invoice::where('supplier_id', auth()->id())
            ->where('status', 'emisa')
            ->where('data_scadenta', '<', now())
            ->with(['order.customer'])
            ->get();

        return view('reports.sales', compact(
            'generalStats',
            'topProducts',
            'categorySales',
            'dailySales',
            'lowStockProducts',
            'overdueInvoices',
            'startDate',
            'endDate',
            'category'
        ));
    }

    public function exportSales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());
        $category = $request->input('category');

        $orders = Order::where('supplier_id', auth()->id())
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($category, function($query) use ($category) {
                return $query->whereHas('items.product', function($q) use ($category) {
                    $q->where('categorie', $category);
                });
            })
            ->with(['customer', 'items.product'])
            ->get();

        $csv = Writer::createFromString('');
        
        // Adăugăm header-ul
        $csv->insertOne([
            'Data Comandă',
            'Număr Comandă',
            'Client',
            'Produs',
            'Cantitate',
            'Preț Unitar',
            'Valoare Totală',
            'Status Comandă',
            'Status Factură'
        ]);

        // Adăugăm datele
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $csv->insertOne([
                    $order->created_at->format('d.m.Y'),
                    $order->order_number,
                    $order->customer->name,
                    $item->product->descriere,
                    $item->quantity,
                    number_format($item->price, 2),
                    number_format($item->quantity * $item->price, 2),
                    $order->status,
                    $order->invoice ? $order->invoice->status : 'N/A'
                ]);
            }
        }

        $filename = 'raport_vanzari_' . now()->format('Y-m-d') . '.csv';
        
        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function inventoryReport()
    {
        $products = Product::where('supplier_id', auth()->id())
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                });
            }])
            ->withSum(['orderItems as total_value' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                });
            }], DB::raw('quantity * price'))
            ->orderBy('stoc')
            ->paginate(15);

        $categories = Product::where('supplier_id', auth()->id())
            ->select('categorie', DB::raw('COUNT(*) as total_products'), DB::raw('SUM(stoc) as total_stock'))
            ->groupBy('categorie')
            ->get();

        return view('reports.inventory', compact('products', 'categories'));
    }

    public function financialReport()
    {
        $startDate = request('start_date', now()->startOfYear());
        $endDate = request('end_date', now());

        // Statistici generale
        $generalStats = Invoice::where('supplier_id', auth()->id())
            ->whereBetween('data_emitere', [$startDate, $endDate])
            ->select([
                DB::raw('COUNT(*) as total_invoices'),
                DB::raw('SUM(total) as total_value'),
                DB::raw('SUM(CASE WHEN status = "platita" THEN total ELSE 0 END) as total_paid'),
                DB::raw('SUM(CASE WHEN status = "emisa" THEN total ELSE 0 END) as total_unpaid')
            ])
            ->first();

        // Facturi pe luni
        $monthlyInvoices = Invoice::where('supplier_id', auth()->id())
            ->whereBetween('data_emitere', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(data_emitere, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_invoices'),
                DB::raw('SUM(total) as total_value'),
                DB::raw('SUM(CASE WHEN status = "platita" THEN total ELSE 0 END) as total_paid')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Facturi restante
        $overdueInvoices = Invoice::where('supplier_id', auth()->id())
            ->where('status', 'emisa')
            ->where('data_scadenta', '<', now())
            ->with(['order.customer'])
            ->get();

        return view('reports.financial', compact(
            'generalStats',
            'monthlyInvoices',
            'overdueInvoices',
            'startDate',
            'endDate'
        ));
    }

    public function exportFinancial(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear());
        $endDate = $request->input('end_date', now());

        $invoices = Invoice::where('supplier_id', auth()->id())
            ->whereBetween('data_emitere', [$startDate, $endDate])
            ->with(['order.customer'])
            ->get();

        $csv = Writer::createFromString('');
        
        // Adăugăm header-ul
        $csv->insertOne([
            'Număr Factură',
            'Data Emiterii',
            'Data Scadenței',
            'Client',
            'Valoare Totală',
            'Status',
            'Data Plății',
            'Zile Restante'
        ]);

        // Adăugăm datele
        foreach ($invoices as $invoice) {
            $csv->insertOne([
                $invoice->numar_factura,
                $invoice->data_emitere->format('d.m.Y'),
                $invoice->data_scadenta->format('d.m.Y'),
                $invoice->order->customer->name,
                number_format($invoice->total, 2),
                $invoice->status,
                $invoice->data_plata ? $invoice->data_plata->format('d.m.Y') : 'N/A',
                $invoice->status === 'emisa' ? $invoice->data_scadenta->diffInDays(now()) : 'N/A'
            ]);
        }

        $filename = 'raport_financiar_' . now()->format('Y-m-d') . '.csv';
        
        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportInventory(Request $request)
    {
        $products = Product::where('supplier_id', auth()->id())
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                });
            }])
            ->withSum(['orderItems as total_value' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                });
            }], DB::raw('quantity * price'))
            ->get();

        $csv = Writer::createFromString('');
        
        // Adăugăm header-ul
        $csv->insertOne([
            'Cod Produs',
            'Descriere',
            'Categorie',
            'Stoc Actual',
            'Unitate Măsură',
            'Preț',
            'Valoare Stoc',
            'Vânzări (3 luni)',
            'Valoare Vânzări (3 luni)',
            'Status Stoc'
        ]);

        // Adăugăm datele
        foreach ($products as $product) {
            $csv->insertOne([
                $product->cod_produs,
                $product->descriere,
                $product->categorie,
                $product->stoc,
                $product->unitate_masura,
                number_format($product->pret, 2),
                number_format($product->stoc * $product->pret, 2),
                $product->total_sold,
                number_format($product->total_value, 2),
                $product->stoc < 10 ? 'Stoc Redus' : 'OK'
            ]);
        }

        $filename = 'raport_inventar_' . now()->format('Y-m-d') . '.csv';
        
        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function orders()
    {
        $user = auth()->user();
        
        $orders = Order::where('customer_id', $user->id)
            ->with(['supplier', 'items'])
            ->latest()
            ->paginate(10);

        $statistics = [
            'total_orders' => Order::where('customer_id', $user->id)->count(),
            'total_spent' => Order::where('customer_id', $user->id)
                ->where('status', 'completed')
                ->sum('total'),
            'completed_orders' => Order::where('customer_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'pending_orders' => Order::where('customer_id', $user->id)
                ->whereNotIn('status', ['completed', 'anulata'])
                ->count(),
        ];

        return view('reports.orders', compact('orders', 'statistics'));
    }

    public function exportOrders()
    {
        $user = auth()->user();
        
        $orders = Order::where('customer_id', $user->id)
            ->with(['supplier', 'items'])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders.csv"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Număr Comandă', 'Furnizor', 'Total', 'Status', 'Data']);

            // Data rows
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->numar_comanda,
                    $order->supplier->name,
                    $order->total,
                    $order->status,
                    $order->created_at->format('d.m.Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

 