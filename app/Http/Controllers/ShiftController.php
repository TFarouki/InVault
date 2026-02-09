<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function getStatus(Request $request)
    {
        $user = $request->user();
        // Check for an open shift for this user (and optionally terminal)
        $shift = \App\Models\Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        return response()->json([
            'has_open_shift' => !!$shift,
            'shift' => $shift,
            'denominations' => \App\Models\CashDenomination::active()->get()
        ]);
    }

    public function open(Request $request)
    {
        $request->validate([
            'start_cash' => 'required|numeric|min:0',
            'counts' => 'array', // Array of { denomination_id, quantity }
            'counts.*.denomination_id' => 'required|exists:cash_denominations,id',
            'counts.*.quantity' => 'required|integer|min:0',
        ]);

        $user = $request->user();

        // Ensure no existing open shift
        $existingShift = \App\Models\Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json(['message' => 'You already have an open shift.'], 400);
        }

        $shift = \App\Models\Shift::create([
            'user_id' => $user->id,
            'terminal_id' => $request->header('X-Terminal-ID', 'default'), // Helper or header
            'start_time' => now(),
            'start_cash' => $request->start_cash,
            'status' => 'open',
        ]);

        // Record opening counts
        $counts = $request->counts ?? [];
        foreach ($counts as $count) {
            $denom = \App\Models\CashDenomination::find($count['denomination_id']);
            if ($denom) {
                $shift->cashCounts()->create([
                    'denomination_id' => $denom->id,
                    'type' => 'opening',
                    'quantity' => $count['quantity'],
                    'total_value' => $count['quantity'] * $denom->value,
                ]);
            }
        }

        return response()->json(['message' => 'Shift opened successfully.', 'shift' => $shift]);
    }

    public function close(Request $request)
    {
        $request->validate([
            'end_cash' => 'required|numeric|min:0',
            'counts' => 'array',
            'counts.*.denomination_id' => 'required|exists:cash_denominations,id',
            'counts.*.quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $user = $request->user();
        $shift = \App\Models\Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->firstOrFail();

        // Calculate expected cash
        // Start Cash + Cash Sales + Cash Splits In - Cash Refunds - Payouts - Drops - Cash Splits Out
        // For now, simpler calculation: Start + (Sales - Refunds)
        // We need to query Sales model for cash sales in this shift period

        $cashSales = \App\Models\Sale::where('user_id', $user->id)
            ->where('created_at', '>=', $shift->start_time)
            ->where('payment_method', 'cash')
            ->sum('total_ttc'); // Assuming total_ttc is the final amount

        // Add Transactions logic later (Drops/Payouts)
        $transactionsNet = -$shift->transactions()->where('type', 'drop')->sum('amount')
            - $shift->transactions()->where('type', 'payout')->sum('amount');


        $expected = $shift->start_cash + $cashSales + $transactionsNet;

        $shift->update([
            'end_time' => now(),
            'end_cash' => $request->end_cash,
            'expected_cash' => $expected,
            'status' => 'closed',
            'notes' => $request->notes,
        ]);

        // Record closing counts
        $counts = $request->counts ?? [];
        foreach ($counts as $count) {
            $denom = \App\Models\CashDenomination::find($count['denomination_id']);
            if ($denom) {
                $shift->cashCounts()->create([
                    'denomination_id' => $denom->id,
                    'type' => 'closing',
                    'quantity' => $count['quantity'],
                    'total_value' => $count['quantity'] * $denom->value,
                ]);
            }
        }

        return response()->json([
            'message' => 'Shift closed successfully.',
            'shift' => $shift,
            'discrepancy' => $request->end_cash - $expected
        ]);
    }
}