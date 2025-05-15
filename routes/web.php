<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ConnectionController;
use App\Http\Middleware\SupplierMiddleware;
use App\Http\Middleware\ClientMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Rute pentru gestionarea produselor - accesibile tuturor utilizatorilor autentificați
    Route::middleware([SupplierMiddleware::class])->group(function () {
        // Rute pentru gestionarea produselor - doar pentru furnizori
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        
        Route::get('/products/import', [ProductController::class, 'import'])->name('products.import');
        Route::post('/products/import', [ProductController::class, 'processImport'])->name('products.process-import');
        Route::get('/products/template/download', [ProductController::class, 'downloadTemplate'])->name('products.template.download');
        Route::post('/products/{product}/stock', [ProductController::class, 'updateStock'])->name('products.stock.update');
    });
    
    // Rute pentru vizualizarea produselor - accesibile tuturor utilizatorilor autentificați
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Rute pentru raport comenzi - accesibile doar clienților
    Route::middleware([ClientMiddleware::class])->group(function () {
        Route::get('/orders/report', [OrderController::class, 'report'])->name('orders.report');
        Route::post('orders/{order}/deliver', [OrderController::class, 'markAsDelivered'])->name('orders.deliver');
    });

    Route::resource('orders', OrderController::class);
    Route::middleware([SupplierMiddleware::class])->group(function () {
        // Folosim doar ruta PATCH pentru actualizarea statusului comenzii
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
    });

    Route::resource('connections', ConnectionController::class);
    Route::patch('connections/{connection}/status', [ConnectionController::class, 'updateStatus'])->name('connections.update-status');

    // Rută de test pentru produsele unui furnizor
    Route::get('/test-products/{supplier}', function ($supplier) {
        $products = \App\Models\Product::where('supplier_id', $supplier)
            ->where('stock', '>', 0)
            ->get();
        
        return response()->json([
            'count' => $products->count(),
            'products' => $products
        ]);
    });
});

require __DIR__.'/auth.php';
