<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Role;
use App\Models\CashDenomination;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_successfully()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_dashboard_displays_sales_stats()
    {
        $user = User::factory()->create();
        
        Sale::create([
            'user_id' => $user->id,
            'reference' => 'INV-001',
            'total_ht' => 100,
            'total_ttc' => 120,
            'tax_amount' => 20,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('120');
    }

    public function test_dashboard_requires_authentication()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect('/login');
    }
}
