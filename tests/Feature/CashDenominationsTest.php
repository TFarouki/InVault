<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CashDenomination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashDenominationsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_denominations_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('cash-denominations.index'));

        $response->assertStatus(200);
    }

    public function test_denominations_index_displays_denominations()
    {
        CashDenomination::create([
            'name' => '10 DH',
            'value' => 10,
            'currency' => 'MAD',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('cash-denominations.index'));

        $response->assertStatus(200);
        $response->assertSee('10 DH');
    }

    public function test_can_create_denomination()
    {
        $response = $this->actingAs($this->user)->post(route('cash-denominations.store'), [
            'name' => '20 DH',
            'value' => 20,
            'currency' => 'MAD',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cash_denominations', [
            'name' => '20 DH',
            'value' => 20,
        ]);
    }

    public function test_can_update_denomination()
    {
        $denomination = CashDenomination::create([
            'name' => 'Old',
            'value' => 10,
            'currency' => 'MAD',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->put(
            route('cash-denominations.update', $denomination->id),
            [
                'name' => 'Updated',
                'value' => 50,
                'currency' => 'MAD',
                'is_active' => true,
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('cash_denominations', [
            'name' => 'Updated',
            'value' => 50,
            'id' => $denomination->id,
        ]);
    }

    public function test_can_delete_denomination()
    {
        $denomination = CashDenomination::create([
            'name' => 'To Delete',
            'value' => 10,
            'currency' => 'MAD',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->delete(
            route('cash-denominations.destroy', $denomination->id)
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('cash_denominations', [
            'id' => $denomination->id,
        ]);
    }

    public function test_can_toggle_denomination_active_status()
    {
        $denomination = CashDenomination::create([
            'name' => 'Toggle Test',
            'value' => 10,
            'currency' => 'MAD',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('cash-denominations.toggle', $denomination->id)
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('cash_denominations', [
            'id' => $denomination->id,
            'is_active' => false,
        ]);
    }

    public function test_denomination_validation_requires_fields()
    {
        $response = $this->actingAs($this->user)->post(
            route('cash-denominations.store'),
            []
        );

        $response->assertSessionHasErrors(['name', 'value']);
    }
}
