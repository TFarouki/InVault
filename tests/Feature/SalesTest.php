<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Setting::updateOrCreate(['key' => 'tax_percentage'], ['value' => 20]);
    }

    public function test_sales_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('sales.index'));

        $response->assertStatus(200);
    }

    public function test_sales_index_displays_sales()
    {
        Sale::create([
            'user_id' => $this->user->id,
            'reference' => 'INV-001',
            'total_ht' => 100,
            'total_ttc' => 120,
            'tax_amount' => 20,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.index'));

        $response->assertStatus(200);
        $response->assertSee('INV-001');
    }

    public function test_sales_index_search_works()
    {
        Sale::create([
            'user_id' => $this->user->id,
            'reference' => 'SEARCH-TEST-001',
            'total_ht' => 50,
            'total_ttc' => 60,
            'tax_amount' => 10,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.index', ['search' => 'SEARCH']));

        $response->assertStatus(200);
        $response->assertSee('SEARCH-TEST-001');
    }

    public function test_can_create_sale()
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'code' => 'TST']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'code' => 'TP001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 100,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('sales.store'), [
            'items' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => 12,
                    'quantity' => 2,
                ]
            ],
            'total_amount' => 24,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', [
            'total_ttc' => 24,
            'status' => 'completed',
        ]);
    }

    public function test_can_view_sale_details()
    {
        $sale = Sale::create([
            'user_id' => $this->user->id,
            'reference' => 'INV-100',
            'total_ht' => 100,
            'total_ttc' => 120,
            'tax_amount' => 20,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.show', $sale->id));

        $response->assertStatus(200);
        $response->assertSee('INV-100');
    }

    public function test_can_delete_sale()
    {
        $sale = Sale::create([
            'user_id' => $this->user->id,
            'reference' => 'INV-DEL',
            'total_ht' => 50,
            'total_ttc' => 60,
            'tax_amount' => 10,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->delete(route('sales.destroy', $sale->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('sales', [
            'id' => $sale->id,
        ]);
    }

    public function test_sale_updates_stock()
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'code' => 'TST']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Stock Test',
            'code' => 'ST001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 100,
            'active' => true,
        ]);

        $this->actingAs($this->user)->post(route('sales.store'), [
            'items' => [
                ['id' => $product->id, 'quantity' => 5, 'price' => 12]
            ],
            'total_amount' => 60,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 95,
        ]);
    }
}
