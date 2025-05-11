<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        if ($user->isSupplier()) {
            return $this->supplierDashboard($user, $thisMonth, $lastMonth);
        } else {
            return $this->customerDashboard($user, $thisMonth, $lastMonth);
        }
    }

    private function supplierDashboard($user, $thisMonth, $lastMonth)
    {
        // Statistici generale
        $stats = [
            'total_orders' => Order::where('supplier_id', $user->id)->count(),
            'total_products' => Product::where('supplier_id', $user->id)->count(),
            'total_customers' => Connection::where('supplier_id', $user->id)
                ->where('status', 'connected')
                ->count(),
            'total_revenue' => Order::where('supplier_id', $user->id)
                ->where('status', 'completed')
                ->sum('total'),
        ];

        // Statistici pentru luna curentă
        $currentMonthStats = [
            'orders' => Order::where('supplier_id', $user->id)
                ->whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->count(),
            'revenue' => Order::where('supplier_id', $user->id)
                ->whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->sum('total'),
            'new_customers' => Connection::where('supplier_id', $user->id)
                ->where('status', 'connected')
                ->whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->count()
        ];

        // Statistici pentru luna trecută
        $lastMonthStats = [
            'orders' => Order::where('supplier_id', $user->id)
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count(),
            'revenue' => Order::where('supplier_id', $user->id)
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->sum('total'),
            'new_customers' => Connection::where('supplier_id', $user->id)
                ->where('status', 'connected')
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count()
        ];

        // Calculăm procentele de creștere
        $growthRates = [
            'orders' => $this->calculateGrowthRate($currentMonthStats['orders'], $lastMonthStats['orders']),
            'revenue' => $this->calculateGrowthRate($currentMonthStats['revenue'], $lastMonthStats['revenue']),
            'customers' => $this->calculateGrowthRate($currentMonthStats['new_customers'], $lastMonthStats['new_customers'])
        ];

        // Comenzi recente
        $recentOrders = Order::where('supplier_id', $user->id)
            ->with(['customer'])
            ->latest()
            ->take(5)
            ->get();

        // Produse cu stoc redus
        $lowStockProducts = Product::where('supplier_id', $user->id)
            ->where('stoc', '<', 10)
            ->take(5)
            ->get();

        // Facturi restante
        $overdueInvoices = Invoice::where('supplier_id', $user->id)
            ->where('status', 'emisa')
            ->where('data_scadenta', '<', now())
            ->with(['order.customer'])
            ->take(5)
            ->get();

        // Vânzări pe ultimele 7 zile
        $dailySales = Order::where('supplier_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_value')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top produse vândute
        $topProducts = Product::where('supplier_id', $user->id)
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                });
            }])
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return view('dashboard.supplier', compact(
            'stats',
            'currentMonthStats',
            'growthRates',
            'recentOrders',
            'lowStockProducts',
            'overdueInvoices',
            'dailySales',
            'topProducts'
        ));
    }

    private function customerDashboard($user, $thisMonth, $lastMonth)
    {
        // Statistici generale
        $stats = [
            'total_orders' => Order::where('customer_id', $user->id)->count(),
            'total_spent' => Order::where('customer_id', $user->id)
                ->where('status', 'completed')
                ->sum('total'),
            'total_suppliers' => Connection::where('customer_id', $user->id)
                ->where('status', 'connected')
                ->count(),
            'active_orders' => Order::where('customer_id', $user->id)
                ->whereNotIn('status', ['completed', 'anulata'])
                ->count()
        ];

        // Statistici pentru luna curentă
        $currentMonthStats = [
            'orders' => Order::where('customer_id', $user->id)
                ->whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->count(),
            'spent' => Order::where('customer_id', $user->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->sum('total'),
            'new_suppliers' => Connection::where('customer_id', $user->id)
                ->where('status', 'connected')
                ->whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->count()
        ];

        // Statistici pentru luna trecută
        $lastMonthStats = [
            'orders' => Order::where('customer_id', $user->id)
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count(),
            'spent' => Order::where('customer_id', $user->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->sum('total'),
            'new_suppliers' => Connection::where('customer_id', $user->id)
                ->where('status', 'connected')
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count()
        ];

        // Calculăm procentele de creștere
        $growthRates = [
            'orders' => $this->calculateGrowthRate($currentMonthStats['orders'], $lastMonthStats['orders']),
            'spent' => $this->calculateGrowthRate($currentMonthStats['spent'], $lastMonthStats['spent']),
            'suppliers' => $this->calculateGrowthRate($currentMonthStats['new_suppliers'], $lastMonthStats['new_suppliers'])
        ];

        // Comenzi recente
        $recentOrders = Order::where('customer_id', $user->id)
            ->with(['supplier'])
            ->latest()
            ->take(5)
            ->get();

        // Facturi restante
        $overdueInvoices = Invoice::where('customer_id', $user->id)
            ->where('status', 'emisa')
            ->where('data_scadenta', '<', now())
            ->with(['order.supplier'])
            ->take(5)
            ->get();

        // Cheltuieli pe ultimele 7 zile
        $dailySpending = Order::where('customer_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_value')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top furnizori
        $topSuppliers = Connection::where('customer_id', $user->id)
            ->where('status', 'connected')
            ->withCount(['orders as total_orders' => function($query) {
                $query->where('created_at', '>=', now()->subMonths(3));
            }])
            ->orderByDesc('total_orders')
            ->take(5)
            ->get();

        return view('dashboard.customer', compact(
            'stats',
            'currentMonthStats',
            'growthRates',
            'recentOrders',
            'overdueInvoices',
            'dailySpending',
            'topSuppliers'
        ));
    }

    private function calculateGrowthRate($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }
} 