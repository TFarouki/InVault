<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'user'])->latest()->paginate(15);
        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_unit' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $total_ht = 0;
            $total_ttc = 0;
            $total_tax = 0;

            $purchase = Purchase::create([
                'reference' => 'PUR-' . strtoupper(Str::random(10)),
                'user_id' => auth()->id(),
                'supplier_id' => $request->supplier_id,
                'total_ht' => 0,
                'total_ttc' => 0,
                'tax_amount' => 0,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['id']);

                $item_total_ht = $item['price_unit'] * $item['quantity'];
                $item_tax = $item_total_ht * ($product->tax_percentage / 100);
                $item_total_ttc = $item_total_ht + $item_tax;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_unit' => $item['price_unit'],
                    'tax_amount' => $item_tax,
                    'total' => $item_total_ttc,
                ]);

                $product->increment('stock', $item['quantity']);

                $total_ht += $item_total_ht;
                $total_tax += $item_tax;
                $total_ttc += $item_total_ttc;
            }

            $purchase->update([
                'total_ht' => $total_ht,
                'total_ttc' => $total_ttc,
                'tax_amount' => $total_tax,
            ]);

            return redirect()->route('purchases.index')->with('success', __('Purchase completed successfully.'));
        });
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                Product::find($item->product_id)->decrement('stock', $item->quantity);
            }
            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('success', __('Purchase deleted successfully.'));
    }
}