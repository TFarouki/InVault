<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashDenominationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $denominations = [
            ['name' => '200 MAD', 'value' => 200, 'type' => 'bill'],
            ['name' => '100 MAD', 'value' => 100, 'type' => 'bill'],
            ['name' => '50 MAD', 'value' => 50, 'type' => 'bill'],
            ['name' => '20 MAD', 'value' => 20, 'type' => 'bill'],
            ['name' => '10 MAD', 'value' => 10, 'type' => 'bill/coin'],
            ['name' => '5 MAD', 'value' => 5, 'type' => 'coin'],
            ['name' => '2 MAD', 'value' => 2, 'type' => 'coin'],
            ['name' => '1 MAD', 'value' => 1, 'type' => 'coin'],
            ['name' => '0.5 MAD', 'value' => 0.5, 'type' => 'coin'],
        ];

        foreach ($denominations as $index => $denom) {
            \App\Models\CashDenomination::create([
                'name' => $denom['name'],
                'value' => $denom['value'],
                'currency' => 'MAD',
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }
    }
}