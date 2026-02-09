<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ?Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ?Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $sales = Sale::with('items.product')->whereBetween('created_at', [$startDate, $endDate])->get();
        $purchases = Purchase::whereBetween('created_at', [$startDate, $endDate])->get();

        $total_sales = $sales->sum('total_ttc');
        $total_purchases = $purchases->sum('total_ttc');

        // Calculate profit: Sale Price - Cost Price
        $total_cost = $sales->flatMap(fn($sale) => $sale->items)->reduce(function ($carry, $item) {
            return $carry + ($item->product->cost_price * $item->quantity);
        }, 0);

        $reports = [
            'total_sales' => $total_sales,
            'total_purchases' => $total_purchases,
            'profit' => $total_sales - $total_cost,
            'sales_count' => $sales->count(),
            'purchases_count' => $purchases->count(),
        ];

        return Inertia::render('Reports/Index', [
            'reports' => $reports,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    public function inventory()
    {
        $products = Product::with('category')->get();

        $stock_stats = [
            'total_items' => $products->sum('stock'),
            'total_value_cost' => $products->reduce(fn($carry, $p) => $carry + ($p->cost_price * $p->stock), 0),
            'total_value_retail' => $products->reduce(fn($carry, $p) => $carry + ($p->price_ttc * $p->stock), 0),
            'low_stock_count' => $products->where('stock', '<', 10)->count(),
        ];

        return Inertia::render('Reports/Inventory', [
            'products' => $products,
            'stats' => $stock_stats,
        ]);
    }
}