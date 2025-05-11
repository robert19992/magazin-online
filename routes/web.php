<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ConnectionController;
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

    Route::middleware(['supplier'])->group(function () {
        Route::resource('products', ProductController::class);
        Route::post('products/import', [ProductController::class, 'processImport'])->name('products.import');
        Route::get('products/template/download', [ProductController::class, 'downloadTemplate'])->name('products.template.download');
        Route::post('products/{product}/stock', [ProductController::class, 'updateStock'])->name('products.stock.update');
    });

    Route::resource('orders', OrderController::class);
    Route::middleware(['supplier'])->group(function () {
        Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
        Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
    });
    Route::middleware(['client'])->group(function () {
        Route::post('orders/{order}/deliver', [OrderController::class, 'markAsDelivered'])->name('orders.deliver');
    });

    Route::resource('connections', ConnectionController::class);
    Route::post('connections/{connection}/status', [ConnectionController::class, 'updateStatus'])->name('connections.status.update');
});

require __DIR__.'/auth.php';
