<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::query()
            ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'filters' => $request->only(['search']),
        ]);
    }

    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => 'required|in:add,subtract',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated, $product, $request) {
                $quantity = $validated['quantity'];

                if ($validated['type'] === 'subtract') {
                    if ($product->stock < $quantity) {
                        throw new \Exception(__('Insufficient stock.'));
                    }
                    $product->decrement('stock', $quantity);
                    $movementType = 'adjustment'; // or 'loss' if specific
                    $finalQuantity = -$quantity;
                }
                else {
                    $product->increment('stock', $quantity);
                    $movementType = 'adjustment';
                    $finalQuantity = $quantity;
                }

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'type' => $movementType,
                    'quantity' => $finalQuantity,
                    'reason' => $validated['reason'] ?: __('Manual adjustment'),
                ]);
            });

            return redirect()->back()->with('success', __('Stock adjusted successfully.'));

        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $movements = StockMovement::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Inventory/History', [
            'movements' => $movements,
        ]);
    }
}