<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'terminal_id',
        'start_time',
        'end_time',
        'start_cash',
        'end_cash',
        'expected_cash',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_cash' => 'decimal:2',
        'end_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashCounts()
    {
        return $this->hasMany(ShiftCashCount::class);
    }

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function sales()
    {
        // Assuming we'll add shift_id to sales table later or link via time
        return $this->hasMany(Sale::class);
    }
}