<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CashDenominationController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\CashTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/language/{locale}', [LanguageController::class , 'switch'])->name('language.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

    // Users
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);

    // Products
    Route::resource('products', ProductController::class)->except(['show', 'create', 'edit']);

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    // Customers
    Route::resource('customers', CustomerController::class)->except(['show', 'create', 'edit']);

    // Suppliers
    Route::resource('suppliers', SupplierController::class)->except(['show', 'create', 'edit']);

    // Sales
    Route::resource('sales', SaleController::class);

    // Purchases
    Route::resource('purchases', PurchaseController::class)->except(['show', 'create', 'edit']);

    // Reports
    Route::get('/reports', [ReportController::class , 'index'])->name('reports.index');

    // Inventory
    Route::get('/inventory', [\App\Http\Controllers\InventoryController::class , 'index'])->name('inventory.index');
    Route::post('/inventory/{product}/adjust', [\App\Http\Controllers\InventoryController::class , 'adjust'])->name('inventory.adjust');
    Route::get('/inventory/history', [\App\Http\Controllers\InventoryController::class , 'history'])->name('inventory.history');

    // Settings
    Route::get('/settings', [SettingController::class , 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class , 'update'])->name('settings.update');

    // POS Terminal
    Route::get('/pos', [\App\Http\Controllers\PosController::class , 'terminal'])->name('pos.terminal');
    Route::post('/pos', [\App\Http\Controllers\PosController::class , 'store'])->name('pos.store');
    Route::get('/pos/history', [\App\Http\Controllers\PosController::class , 'history'])->name('pos.history');
    Route::delete('/pos/{sale}', [\App\Http\Controllers\PosController::class , 'destroy'])->name('pos.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');
    // Cash Denominations
    Route::get('/denominations', [CashDenominationController::class , 'index'])->name('cash-denominations.index');
    Route::post('/denominations', [CashDenominationController::class , 'store'])->name('cash-denominations.store');
    Route::put('/denominations/{denomination}', [CashDenominationController::class , 'update'])->name('cash-denominations.update');
    Route::delete('/denominations/{denomination}', [CashDenominationController::class , 'destroy'])->name('cash-denominations.destroy');
    Route::post('/denominations/{denomination}/toggle', [CashDenominationController::class , 'toggleActive'])->name('cash-denominations.toggle');

    // Shift Management
    Route::get('/shifts/status', [ShiftController::class , 'getStatus'])->name('shifts.status');
    Route::post('/shifts/open', [ShiftController::class , 'open'])->name('shifts.open');
    Route::post('/shifts/close', [ShiftController::class , 'close'])->name('shifts.close');

    // Cash Transactions
    Route::post('/cash-transactions', [CashTransactionController::class , 'store'])->name('cash-transactions.store');
});

require __DIR__ . '/auth.php';