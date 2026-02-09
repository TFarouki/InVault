<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function terminal()
    {
        $categories = Category::with(['products' => function ($query) {
            $query->where('active', true)->where('stock', '>', 0);
        }])->get();

        // Also get all active products for the "All" category or search
        $allProducts = Product::where('active', true)->where('stock', '>', 0)->get();

        return Inertia::render('Pos/Terminal', [
            'categories' => $categories,
            'allProducts' => $allProducts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card',
            'status' => 'nullable|in:completed,held',
            'received_amount' => 'nullable|numeric|min:0',
        ]);

        $status = $validated['status'] ?? 'completed';

        // Get Tax Percentage
        $taxPercentage = \App\Models\Setting::where('key', 'tax_percentage')->value('value') ?? 0;

        try {
            DB::transaction(function () use ($validated, $request, $status, $taxPercentage) {
                // Calculate tax and totals
                $totalTtc = $validated['total_amount'];
                $taxAmount = $totalTtc - ($totalTtc / (1 + ($taxPercentage / 100)));
                $totalHt = $totalTtc - $taxAmount;

                // Create Sale
                $sale = Sale::create([
                    'user_id' => $request->user()->id,
                    'reference' => ($status === 'held' ? 'HOLD-' : 'INV-') . time(),
                    'total_ht' => $totalHt,
                    'total_ttc' => $totalTtc,
                    'tax_amount' => $taxAmount,
                    'discount' => 0,
                    'payment_method' => $validated['payment_method'],
                    'status' => $status,
                ]);

                foreach ($validated['items'] as $item) {
                    $product = Product::find($item['id']);

                    $itemTotal = $item['quantity'] * $item['price'];
                    $itemTax = $itemTotal - ($itemTotal / (1 + ($taxPercentage / 100)));

                    // Create Sale Item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price_unit' => $item['price'],
                        'tax_amount' => $itemTax,
                        'total' => $itemTotal,
                    ]);

                    // Update Stock & Log Movement ONLY if completed
                    if ($status === 'completed') {
                        $product->decrement('stock', $item['quantity']);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => $request->user()->id,
                            'type' => 'sale',
                            'quantity' => -$item['quantity'],
                            'reason' => 'POS Sale #' . $sale->reference,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', $status === 'held' ? 'Sale held successfully!' : 'Sale completed successfully!');

        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing sale: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $heldSales = Sale::with(['items.product', 'user'])
            ->where('status', '=', 'held')
            ->latest()
            ->get();

        $completedSales = Sale::with(['items.product', 'user'])
            ->where('status', '=', 'completed')
            ->latest()
            ->take(50)
            ->get();

        return response()->json($heldSales->merge($completedSales));
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {
                if ($sale->status === 'completed') {
                    foreach ($sale->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $product->increment('stock', $item->quantity);

                            StockMovement::create([
                                'product_id' => $product->id,
                                'user_id' => auth()->id(),
                                'type' => 'adjustment',
                                'quantity' => $item->quantity,
                                'reason' => 'Void Sale #' . $sale->invoice_number,
                            ]);
                        }
                    }
                }

                $sale->items()->delete();
                $sale->delete();
            });

            return redirect()->back()->with('success', 'Sale removed successfully.');
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error removing sale: ' . $e->getMessage());
        }
    }
}