<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftCashCount extends Model
{
    protected $fillable = [
        'shift_id',
        'denomination_id',
        'type',
        'quantity',
        'total_value',
    ];

    protected $casts = [
        'total_value' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function denomination()
    {
        return $this->belongsTo(CashDenomination::class);
    }
}