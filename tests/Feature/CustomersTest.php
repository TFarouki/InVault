<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomersTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_customers_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('customers.index'));

        $response->assertStatus(200);
    }

    public function test_customers_index_displays_customers()
    {
        Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0612345678',
            'address' => '123 Main St',
        ]);

        $response = $this->actingAs($this->user)->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    public function test_can_create_customer()
    {
        $response = $this->actingAs($this->user)->post(route('customers.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0699999999',
            'address' => '456 Oak Ave',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_can_update_customer()
    {
        $customer = Customer::create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'phone' => '0600000000',
        ]);

        $response = $this->actingAs($this->user)->put(route('customers.update', $customer->id), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '0611111111',
            'address' => 'New Address',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'name' => 'New Name',
            'id' => $customer->id,
        ]);
    }

    public function test_can_delete_customer()
    {
        $customer = Customer::create([
            'name' => 'To Delete',
            'email' => 'delete@example.com',
        ]);

        $response = $this->actingAs($this->user)->delete(route('customers.destroy', $customer->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_validation_requires_name()
    {
        $response = $this->actingAs($this->user)->post(route('customers.store'), []);

        $response->assertSessionHasErrors(['name']);
    }
}
