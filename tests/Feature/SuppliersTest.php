<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppliersTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_suppliers_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('suppliers.index'));

        $response->assertStatus(200);
    }

    public function test_suppliers_index_displays_suppliers()
    {
        Supplier::create([
            'name' => 'Supplier Co',
            'email' => 'supplier@example.com',
            'phone' => '0612345678',
            'address' => 'Supplier St',
        ]);

        $response = $this->actingAs($this->user)->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('Supplier Co');
    }

    public function test_can_create_supplier()
    {
        $response = $this->actingAs($this->user)->post(route('suppliers.store'), [
            'name' => 'New Supplier',
            'email' => 'newsupplier@example.com',
            'phone' => '0699999999',
            'address' => 'New Supplier Address',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', [
            'name' => 'New Supplier',
        ]);
    }

    public function test_can_update_supplier()
    {
        $supplier = Supplier::create([
            'name' => 'Old Supplier',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($this->user)->put(route('suppliers.update', $supplier->id), [
            'name' => 'Updated Supplier',
            'email' => 'updated@example.com',
            'phone' => '0611111111',
            'address' => 'Updated Address',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Updated Supplier',
            'id' => $supplier->id,
        ]);
    }

    public function test_can_delete_supplier()
    {
        $supplier = Supplier::create([
            'name' => 'To Delete',
            'email' => 'delete@example.com',
        ]);

        $response = $this->actingAs($this->user)->delete(route('suppliers.destroy', $supplier->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
