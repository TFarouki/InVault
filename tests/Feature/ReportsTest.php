<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Setting::updateOrCreate(['key' => 'currency'], ['value' => 'MAD']);
    }

    public function test_reports_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
    }

    public function test_reports_displays_sales_summary()
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

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('120');
    }

    public function test_reports_filters_by_date_range()
    {
        Sale::create([
            'user_id' => $this->user->id,
            'reference' => 'INV-DATE',
            'total_ht' => 50,
            'total_ttc' => 60,
            'tax_amount' => 10,
            'status' => 'completed',
            'payment_method' => 'cash',
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
    }

    public function test_reports_calculates_total_sales()
    {
        Sale::create([
            'user_id' => $this->user->id,
            'reference' => 'INV-1',
            'total_ht' => 100,
            'total_ttc' => 120,
            'tax_amount' => 20,
            'status' => 'completed',
            'payment_method' => 'cash',
        ]);

        Sale::create([
            'user_id' => $this->user->id,
            'reference' => 'INV-2',
            'total_ht' => 50,
            'total_ttc' => 60,
            'tax_amount' => 10,
            'status' => 'completed',
            'payment_method' => 'card',
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('180');
    }

    public function test_reports_requires_authentication()
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect('/login');
    }
}
