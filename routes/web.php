<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin' ? redirect()->route('dashboard') : redirect()->route('pos.index');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [PosController::class, 'searchProducts'])->name('pos.search');
    Route::post('/pos/cart/items', [PosController::class, 'addToCart'])->name('pos.cart.add');
    Route::patch('/pos/cart/items', [PosController::class, 'updateCartItem'])->name('pos.cart.update');
    Route::delete('/pos/cart/items', [PosController::class, 'removeCartItem'])->name('pos.cart.remove');
    Route::delete('/pos/cart', [PosController::class, 'clearCart'])->name('pos.cart.clear');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/add-to-order', [PosController::class, 'addToExistingOrder'])->name('pos.add_to_order');
    Route::get('/pos/occupied-tables', [PosController::class, 'occupiedTables'])->name('pos.occupied_tables');
    Route::get('/orders/{order}/receipt', [PosController::class, 'receipt'])->name('orders.receipt');
    Route::get('/orders/{order}/kitchen-receipt', [PosController::class, 'kitchenReceipt'])->name('orders.kitchen_receipt');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    Route::get('/orders/archived', [OrderController::class, 'archived'])->name('orders.archived');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.items.add');
    Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.items.remove');
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('tables', TableController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    });
});
