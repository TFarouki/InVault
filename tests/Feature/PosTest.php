<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_store_creates_completed_sale()
    {
        $user = User::factory()->create();

        $category = Category::create(['name' => 'Drinks', 'slug' => 'drinks', 'code' => 'DRK']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Coca Cola 33cl',
            'code' => 'COKE33',
            'price_ht' => 5,
            'price_ttc' => 6,
            'stock' => 100,
            'active' => true,
        ]);

        Setting::updateOrCreate(['key' => 'tax_percentage'], ['value' => 20]);

        $response = $this->actingAs($user)->from('/pos')->post(route('pos.store'), [
            'items' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => 6,
                    'quantity' => 5,
                ]
            ],
            'total_amount' => 30,
            'payment_method' => 'cash',
            'status' => 'completed',
            'received_amount' => 0,
        ]);

        $response->assertRedirect();



        $this->assertDatabaseHas('sales', [
            'total_ttc' => 30,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHas('success');

        // Check tax calculation: 30 / 1.2 = 25 (HT), Tax = 5
        $sale = Sale::first();
        $this->assertEquals(25, $sale->total_ht);
        $this->assertEquals(5, $sale->tax_amount);

        // Check stock deduction
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 95,
        ]);

        // Check stock movement
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => -5,
        ]);
    }

    public function test_pos_store_creates_held_sale()
    {
        $user = User::factory()->create();

        $category = Category::create(['name' => 'Drinks', 'slug' => 'drinks', 'code' => 'DRK']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Coca Cola 33cl',
            'code' => 'COKE33',
            'price_ht' => 5,
            'price_ttc' => 6,
            'stock' => 100,
            'active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('pos.store'), [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => 2,
                    'price' => 6,
                ]
            ],
            'total_amount' => 12,
            'payment_method' => 'cash',
            'status' => 'held',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sales', [
            'status' => 'held',
            'total_ttc' => 12,
        ]);

        // Stock should NOT be deducted for held sales
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 100,
        ]);
    }

    public function test_pos_history_returns_correct_data()
    {
        $user = User::factory()->create();

        // Create 1 Held Sale
        Sale::create([
            'user_id' => $user->id,
            'reference' => 'HOLD-1',
            'total_ht' => 10,
            'total_ttc' => 12,
            'tax_amount' => 2,
            'status' => 'held',
            'payment_method' => 'cash',
        ]);

        // Create 1 Completed Sale
        Sale::create([
            'user_id' => $user->id,
            'reference' => 'INV-1',
            'total_ht' => 20,
            'total_ttc' => 24,
            'tax_amount' => 4,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($user)->get(route('pos.history'));

        $response->assertStatus(200);
        $response->assertJsonCount(2); // Should have both
    }
}