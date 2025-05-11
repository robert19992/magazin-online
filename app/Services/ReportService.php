<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generează raport de vânzări pentru furnizor
     */
    public function generateSupplierSalesReport($supplierId, $startDate = null, $endDate = null)
    {
        // Setăm perioada implicită dacă nu este specificată
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now();

        // Obținem comenzile pentru perioada specificată
        $orders = Order::where('supplier_id', $supplierId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.product', 'customer.organization'])
            ->get();

        // Calculăm statisticile
        $totalOrders = $orders->count();
        $totalQuotes = $orders->where('type', 'quote')->count();
        $totalAmount = $orders->sum('total_amount');
        $deliveredOrders = $orders->where('status', 'delivered')->count();
        $pendingOrders = $orders->where('status', 'pending')->count();

        // Grupăm vânzările pe produse
        $productSales = $orders->flatMap->items
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'code' => $product->code,
                    'description' => $product->description,
                    'quantity' => $items->sum('quantity'),
                    'total_amount' => $items->sum('total_price')
                ];
            });

        // Grupăm vânzările pe clienți
        $customerSales = $orders->groupBy('customer_id')
            ->map(function ($customerOrders) {
                $customer = $customerOrders->first()->customer->organization;
                return [
                    'name' => $customer->name,
                    'orders_count' => $customerOrders->count(),
                    'total_amount' => $customerOrders->sum('total_amount')
                ];
            });

        // Grupăm vânzările pe luni
        $monthlySales = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m');
        })->map(function ($monthOrders) {
            return [
                'orders_count' => $monthOrders->count(),
                'total_amount' => $monthOrders->sum('total_amount')
            ];
        });

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_orders' => $totalOrders,
                'total_quotes' => $totalQuotes,
                'total_amount' => $totalAmount,
                'delivered_orders' => $deliveredOrders,
                'pending_orders' => $pendingOrders
            ],
            'product_sales' => $productSales->sortByDesc('total_amount')->values(),
            'customer_sales' => $customerSales->sortByDesc('total_amount')->values(),
            'monthly_sales' => $monthlySales
        ];
    }

    /**
     * Generează raport de comenzi pentru client
     */
    public function generateCustomerOrderReport($customerId, $startDate = null, $endDate = null)
    {
        // Setăm perioada implicită dacă nu este specificată
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now();

        // Obținem comenzile pentru perioada specificată
        $orders = Order::where('customer_id', $customerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.product', 'supplier.organization'])
            ->get();

        // Calculăm statisticile
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('total_amount');
        $deliveredOrders = $orders->where('status', 'delivered')->count();
        $pendingOrders = $orders->where('status', 'pending')->count();

        // Grupăm comenzile pe furnizori
        $supplierOrders = $orders->groupBy('supplier_id')
            ->map(function ($supplierOrders) {
                $supplier = $supplierOrders->first()->supplier->organization;
                return [
                    'name' => $supplier->name,
                    'orders_count' => $supplierOrders->count(),
                    'total_amount' => $supplierOrders->sum('total_amount'),
                    'delivered_orders' => $supplierOrders->where('status', 'delivered')->count(),
                    'pending_orders' => $supplierOrders->where('status', 'pending')->count()
                ];
            });

        // Grupăm comenzile pe tipuri de produse
        $productTypeOrders = $orders->flatMap->items
            ->groupBy(function ($item) {
                return $item->product->category ?? 'Necategorizat';
            })
            ->map(function ($items) {
                return [
                    'quantity' => $items->sum('quantity'),
                    'total_amount' => $items->sum('total_price')
                ];
            });

        // Calculăm tendințele lunare
        $monthlyTrends = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m');
        })->map(function ($monthOrders) {
            return [
                'orders_count' => $monthOrders->count(),
                'total_amount' => $monthOrders->sum('total_amount'),
                'average_order_value' => $monthOrders->avg('total_amount')
            ];
        });

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_orders' => $totalOrders,
                'total_amount' => $totalAmount,
                'delivered_orders' => $deliveredOrders,
                'pending_orders' => $pendingOrders,
                'average_order_value' => $totalOrders > 0 ? $totalAmount / $totalOrders : 0
            ],
            'supplier_orders' => $supplierOrders->sortByDesc('total_amount')->values(),
            'product_type_orders' => $productTypeOrders->sortByDesc('total_amount'),
            'monthly_trends' => $monthlyTrends
        ];
    }

    /**
     * Generează raport de stoc pentru furnizor
     */
    public function generateSupplierStockReport($supplierId)
    {
        $products = Product::where('supplier_id', $supplierId)
            ->withCount(['orderItems as ordered_quantity' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'pending');
                });
            }])
            ->get();

        $lowStockThreshold = 10; // Pragul pentru stoc redus

        $stockSummary = [
            'total_products' => $products->count(),
            'low_stock_products' => $products->where('stock', '<', $lowStockThreshold)->count(),
            'out_of_stock_products' => $products->where('stock', 0)->count(),
            'total_stock_value' => $products->sum(function ($product) {
                return $product->stock * $product->price;
            })
        ];

        $lowStockProducts = $products->where('stock', '<', $lowStockThreshold)
            ->map(function ($product) {
                return [
                    'code' => $product->code,
                    'description' => $product->description,
                    'current_stock' => $product->stock,
                    'ordered_quantity' => $product->ordered_quantity,
                    'price' => $product->price
                ];
            });

        $stockByCategory = $products->groupBy('category')
            ->map(function ($categoryProducts) {
                return [
                    'total_products' => $categoryProducts->count(),
                    'total_stock' => $categoryProducts->sum('stock'),
                    'total_value' => $categoryProducts->sum(function ($product) {
                        return $product->stock * $product->price;
                    })
                ];
            });

        return [
            'summary' => $stockSummary,
            'low_stock_products' => $lowStockProducts->values(),
            'stock_by_category' => $stockByCategory
        ];
    }
} 