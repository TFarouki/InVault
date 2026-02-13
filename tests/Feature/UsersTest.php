<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin']);
        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        
        $userRole = Role::create(['name' => 'User', 'slug' => 'user', 'description' => 'User']);
        $this->regularUser = User::factory()->create(['role_id' => $userRole->id]);
    }

    public function test_users_index_page_loads_for_admin()
    {
        $response = $this->actingAs($this->adminUser)->get(route('users.index'));

        $response->assertStatus(200);
    }

    public function test_users_index_displays_users()
    {
        $response = $this->actingAs($this->adminUser)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee($this->regularUser->name);
    }

    public function test_can_create_user()
    {
        $role = Role::first();

        $response = $this->actingAs($this->adminUser)->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $role->id,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
        ]);
    }

    public function test_can_update_user()
    {
        $response = $this->actingAs($this->adminUser)->put(
            route('users.update', $this->regularUser->id),
            [
                'name' => 'Updated Name',
                'email' => $this->regularUser->email,
                'role_id' => $this->regularUser->role_id,
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_user()
    {
        $response = $this->actingAs($this->adminUser)->delete(
            route('users.destroy', $this->regularUser->id)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'id' => $this->regularUser->id,
        ]);
    }

    public function test_cannot_delete_self()
    {
        $response = $this->actingAs($this->adminUser)->delete(
            route('users.destroy', $this->adminUser->id)
        );

        $response->assertSessionHas('error');
    }

    public function test_user_validation_requires_email()
    {
        $response = $this->actingAs($this->adminUser)->post(route('users.store'), [
            'name' => 'Test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_email_must_be_unique()
    {
        $response = $this->actingAs($this->adminUser)->post(route('users.store'), [
            'name' => 'Test',
            'email' => $this->regularUser->email,
            'password' => 'password123',
            'role_id' => $this->regularUser->role_id,
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
