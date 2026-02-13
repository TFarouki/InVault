<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_categories_index_page_loads()
    {
        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertStatus(200);
    }

    public function test_categories_index_displays_categories()
    {
        Category::create(['name' => 'Boissons', 'slug' => 'boissons', 'code' => 'BOI']);

        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Boissons');
    }

    public function test_can_create_category()
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Alimentation',
            'slug' => 'alimentation',
            'code' => 'ALI',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Alimentation',
            'slug' => 'alimentation',
        ]);
    }

    public function test_can_update_category()
    {
        $category = Category::create(['name' => 'Old', 'slug' => 'old', 'code' => 'OLD']);

        $response = $this->actingAs($this->user)->put(route('categories.update', $category->id), [
            'name' => 'New Category',
            'slug' => 'new-category',
            'code' => 'NEW',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'id' => $category->id,
        ]);
    }

    public function test_can_delete_category()
    {
        $category = Category::create(['name' => 'To Delete', 'slug' => 'to-delete', 'code' => 'DEL']);

        $response = $this->actingAs($this->user)->delete(route('categories.destroy', $category->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_category_validation_requires_name()
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), []);

        $response->assertSessionHasErrors(['name']);
    }
}
