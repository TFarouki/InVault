<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Setting::updateOrCreate(['key' => 'tax_percentage'], ['value' => 20]);
    }

    public function test_purchases_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('purchases.index'));

        $response->assertStatus(200);
    }

    public function test_purchases_index_displays_purchases()
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@test.com',
        ]);

        Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'reference' => 'PO-001',
            'total_ht' => 100,
            'total_ttc' => 120,
            'tax_amount' => 20,
            'status' => 'received',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchases.index'));

        $response->assertStatus(200);
        $response->assertSee('PO-001');
    }

    public function test_can_create_purchase()
    {
        $supplier = Supplier::create([
            'name' => 'New Supplier',
            'email' => 'new@test.com',
        ]);

        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'code' => 'TST']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'code' => 'TP001',
            'price_ht' => 10,
            'price_ttc' => 12,
            'stock' => 50,
            'cost_price' => 8,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 10,
                    'price_unit' => 10,
                ]
            ],
            'total_amount' => 120,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchases', [
            'status' => 'completed',
        ]);
    }

    public function test_can_delete_purchase()
    {
        $supplier = Supplier::create([
            'name' => 'Delete Supplier',
            'email' => 'del@test.com',
        ]);

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => $this->user->id,
            'reference' => 'PO-DEL',
            'total_ht' => 50,
            'total_ttc' => 60,
            'tax_amount' => 10,
            'status' => 'ordered',
        ]);

        $response = $this->actingAs($this->user)->delete(route('purchases.destroy', $purchase->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('purchases', [
            'id' => $purchase->id,
        ]);
    }

    public function test_purchase_updates_stock_when_received()
    {
        $this->markTestSkipped('Requires update method implementation with status change handling');
    }
}
