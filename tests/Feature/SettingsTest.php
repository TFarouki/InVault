<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_settings_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('settings.index'));

        $response->assertStatus(200);
    }

    public function test_can_update_settings()
    {
        Setting::updateOrCreate(['key' => 'app_name'], ['value' => 'Old Name']);

        $response = $this->actingAs($this->user)->post(route('settings.update'), [
            'settings' => [
                'app_name' => 'New InVault',
                'currency' => 'EUR',
                'tax_percentage' => 15,
                'phone' => '+212600000000',
                'address' => 'New Address',
            ],
            'language' => 'fr',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key' => 'app_name',
            'value' => 'New InVault',
        ]);
    }

    public function test_settings_persists_across_requests()
    {
        Setting::updateOrCreate(['key' => 'currency'], ['value' => 'USD']);

        $response = $this->actingAs($this->user)->get(route('settings.index'));

        $response->assertStatus(200);
    }

    public function test_settings_requires_authentication()
    {
        $response = $this->get(route('settings.index'));

        $response->assertRedirect('/login');
    }
}
