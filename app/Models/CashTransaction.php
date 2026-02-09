<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = [
        'shift_id',
        'type',
        'amount',
        'reference_id',
        'denominations_json',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'denominations_json' => 'array',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}