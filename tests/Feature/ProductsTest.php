<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Category::create(['name' => 'Drinks', 'slug' => 'drinks', 'code' => 'DRK']);
    }

    public function test_products_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertStatus(200);
    }

    public function test_products_index_displays_products()
    {
        $category = Category::first();
        Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'code' => 'TP001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 50,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Product');
    }

    public function test_products_index_search_works()
    {
        $category = Category::first();
        Product::create([
            'category_id' => $category->id,
            'name' => 'Coca Cola',
            'code' => 'CC001',
            'price_ht' => 5,
            'price_ttc' => 6,
            'stock' => 100,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['search' => 'Coca']));

        $response->assertStatus(200);
        $response->assertSee('Coca Cola');
    }

    public function test_can_create_product()
    {
        $category = Category::first();

        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'New Product',
            'code' => 'NP001',
            'price_ht' => 15,
            'price_ttc' => 18,
            'tax_percentage' => 20,
            'stock' => 100,
            'cost_price' => 10,
            'active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'code' => 'NP001',
        ]);
    }

    public function test_can_update_product()
    {
        $category = Category::first();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Old Name',
            'code' => 'ON001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 50,
            'cost_price' => 5,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->put(route('products.update', $product->id), [
            'category_id' => $category->id,
            'name' => 'Updated Name',
            'code' => 'UN001',
            'price_ht' => 20,
            'price_ttc' => 24,
            'tax_percentage' => 20,
            'stock' => 50,
            'cost_price' => 10,
            'active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Updated Name',
            'id' => $product->id,
        ]);
    }

    public function test_can_delete_product()
    {
        $category = Category::first();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'To Delete',
            'code' => 'TD001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 50,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->delete(route('products.destroy', $product->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_product_validation_fails_without_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), []);

        $response->assertSessionHasErrors(['name', 'code', 'category_id']);
    }

    public function test_product_unique_code_validation()
    {
        $category = Category::first();
        Product::create([
            'category_id' => $category->id,
            'name' => 'Product 1',
            'code' => 'UNIQUE001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 50,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Product 2',
            'code' => 'UNIQUE001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 50,
            'active' => true,
        ]);

        $response->assertSessionHasErrors('code');
    }
}
