<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashDenominationController extends Controller
{
    public function index()
    {
        return inertia('Admin/Denominations/Index', [
            'denominations' => \App\Models\CashDenomination::orderBy('sort_order')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'image' => 'nullable|image|max:1024', // 1MB Max
            'sort_order' => 'integer',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('denominations', 'public');
            $validated['image_path'] = $path;
        }

        \App\Models\CashDenomination::create($validated);

        return redirect()->back()->with('success', 'Denomination created successfully.');
    }

    public function update(Request $request, \App\Models\CashDenomination $denomination)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'image' => 'nullable|image|max:1024',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('denominations', 'public');
            $validated['image_path'] = $path;
        }

        $denomination->update($validated);

        return redirect()->back()->with('success', 'Denomination updated successfully.');
    }

    public function destroy(\App\Models\CashDenomination $denomination)
    {
        $denomination->delete();
        return redirect()->back()->with('success', 'Denomination deleted successfully.');
    }

    public function toggleActive(\App\Models\CashDenomination $denomination)
    {
        $denomination->update(['is_active' => !$denomination->is_active]);
        return redirect()->back()->with('success', 'Status updated successfully.');
    }
}