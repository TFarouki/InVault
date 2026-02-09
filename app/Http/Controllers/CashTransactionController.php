<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashTransactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:drop,payout,split',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'reference_id' => 'nullable|string',
        ]);

        $user = $request->user();
        $shift = \App\Models\Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->firstOrFail();

        $transaction = $shift->transactions()->create([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'reference_id' => $request->reference_id,
        ]);

        return response()->json(['message' => 'Transaction recorded successfully.', 'transaction' => $transaction]);
    }
}