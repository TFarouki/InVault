<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_sales' => Sale::sum('total_ttc'),
            'transactions_count' => Sale::count(),
            'customers_count' => Customer::count(),
            'low_stock_count' => Product::where('stock', '<', 10)->count(),
        ];

        $recent_sales = Sale::with('customer')->latest()->take(5)->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent_sales' => $recent_sales,
        ]);
    }
}