<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'code' => 'TST']);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'code' => 'TP001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 100,
            'active' => true,
        ]);
    }

    public function test_inventory_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('inventory.index'));

        $response->assertStatus(200);
    }

    public function test_inventory_index_displays_products()
    {
        $response = $this->actingAs($this->user)->get(route('inventory.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Product');
    }

    public function test_inventory_index_shows_low_stock()
    {
        $category = Category::create(['name' => 'Low', 'slug' => 'low', 'code' => 'LOW']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Low Stock Product',
            'code' => 'LS001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 2,
            'min_stock' => 5,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('inventory.index'));

        $response->assertStatus(200);
        $response->assertSee('Low Stock Product');
    }

    public function test_can_adjust_inventory_stock()
    {
        $response = $this->actingAs($this->user)->post(
            route('inventory.adjust', $this->product->id),
            [
                'quantity' => 50,
                'type' => 'add',
                'reason' => 'Stock count correction',
            ]
        );

        $response->assertRedirect();
        
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock' => 150,
        ]);
    }

    public function test_can_deduct_inventory_stock()
    {
        $response = $this->actingAs($this->user)->post(
            route('inventory.adjust', $this->product->id),
            [
                'quantity' => 30,
                'type' => 'subtract',
                'reason' => 'Damaged goods',
            ]
        );

        $response->assertRedirect();
        
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock' => 70,
        ]);
    }

    public function test_inventory_adjustment_creates_movement_record()
    {
        $this->actingAs($this->user)->post(
            route('inventory.adjust', $this->product->id),
            [
                'quantity' => 25,
                'type' => 'add',
                'reason' => 'Test adjustment',
            ]
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'quantity' => 25,
        ]);
    }

    public function test_inventory_history_page_loads()
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'type' => 'sale',
            'quantity' => -5,
            'reason' => 'Sale',
        ]);

        $response = $this->actingAs($this->user)->get(route('inventory.history'));

        $response->assertStatus(200);
    }

    public function test_inventory_history_filters_by_product()
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'type' => 'sale',
            'quantity' => -5,
            'reason' => 'Sale',
        ]);

        $response = $this->actingAs($this->user)->get(
            route('inventory.history', ['product_id' => $this->product->id])
        );

        $response->assertStatus(200);
    }
}
