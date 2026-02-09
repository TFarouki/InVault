<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function admin(): ?Role
    {
        return static::where('slug', 'admin')->first();
    }

    public static function manager(): ?Role
    {
        return static::where('slug', 'manager')->first();
    }

    public static function cashier(): ?Role
    {
        return static::where('slug', 'cashier')->first();
    }
}