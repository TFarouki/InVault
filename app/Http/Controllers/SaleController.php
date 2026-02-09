<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['customer', 'user'])->latest()->paginate(15);
        return Inertia::render('Sales/Index', [
            'sales' => $sales
        ]);
    }

    public function create(Request $request)
    {
        $products = Product::where('active', true)->where('stock', '>', 0)->get();
        $customers = Customer::all();

        return Inertia::render('Sales/Create', [
            'products' => $products,
            'customers' => $customers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'discount' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $total_ht = 0;
            $total_ttc = 0;
            $total_tax = 0;

            $sale = Sale::create([
                'reference' => 'SALE-' . strtoupper(Str::random(10)),
                'user_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'total_ht' => 0,
                'total_ttc' => 0,
                'tax_amount' => 0,
                'discount' => $request->discount ?? 0,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock insuffisant pour le produit: {$product->name}");
                }

                $item_total_ht = $product->price_ht * $item['quantity'];
                $item_tax = $item_total_ht * ($product->tax_percentage / 100);
                $item_total_ttc = $item_total_ht + $item_tax;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_unit' => $product->price_ht,
                    'tax_amount' => $item_tax,
                    'total' => $item_total_ttc,
                ]);

                $product->decrement('stock', $item['quantity']);

                $total_ht += $item_total_ht;
                $total_tax += $item_tax;
                $total_ttc += $item_total_ttc;
            }

            $sale->update([
                'total_ht' => $total_ht,
                'total_ttc' => $total_ttc - ($request->discount ?? 0),
                'tax_amount' => $total_tax,
            ]);

            return redirect()->route('sales.show', $sale)->with('success', __('Sale completed successfully.'));
        });
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);
        $settings = \App\Models\Setting::all()->pluck('value', 'key');

        return Inertia::render('Sales/Show', [
            'sale' => $sale,
            'settings' => $settings
        ]);
    }
}