<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category_id',
        'price_ht',
        'price_ttc',
        'tax_percentage',
        'stock',
        'cost_price',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price_ht' => 'float',
        'price_ttc' => 'float',
        'tax_percentage' => 'float',
        'stock' => 'integer',
        'cost_price' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}