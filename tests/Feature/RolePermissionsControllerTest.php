<?php

namespace Tests\Feature;

use App\Models\Authorization\Permission;
use App\Models\Authorization\Role;
use App\Models\Usuarios\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::factory()->create(['name' => 'Super Admin']);
        Role::findByName('Super Admin')->givePermissionTo(Permission::create(['name' => 'manage roles']));
        Role::findByName('Super Admin')->givePermissionTo(Permission::create(['name' => 'ver roles']));
        Role::findByName('Super Admin')->givePermissionTo(Permission::create(['name' => 'ver permisos']));
        $this->user = Usuario::factory()->create();
        $this->user->assignRole('Super Admin');
        $this->actingAs($this->user);
    }

    public function test_index_roles_returns_paginated_roles()
    {
        Role::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/roles?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'guard_name', 'created_at', 'updated_at']
            ],
            'links',
        ]);
    }

    public function test_index_permissions_returns_all_permissions()
    {
        Permission::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/permissions');

        $response->assertStatus(200);
        $response->assertJsonCount(13);
    }

    public function test_show_role_returns_role_with_permissions()
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        $role->givePermissionTo($permission);

        $response = $this->getJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id', 'name', 'guard_name', 'permissions' => [
                '*' => ['id', 'name', 'guard_name']
            ]
        ]);
    }

    public function test_show_role_returns_404_if_role_not_found()
    {
        $response = $this->getJson('/api/v1/roles/9999');
        $response->assertStatus(404);
        $response->assertJson(['message' => 'Rol no encontrado']);
    }

    public function test_store_role_creates_new_role_with_permissions()
    {
        $permissions = Permission::factory()->count(3)->create();

        $data = [
            'name' => 'Test Role',
            'permissions' => $permissions->pluck('name')->toArray()
        ];

        $response = $this->postJson('/api/v1/roles', $data);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Rol creado correctamente', 'role' => ['name' => 'Test Role']]);
        $this->assertDatabaseHas('roles', ['name' => 'Test Role']);
    }

    public function test_store_role_fails_if_name_is_duplicate()
    {
        Role::factory()->create(['name' => 'Existing Role']);

        $data = [
            'name' => 'Existing Role'
        ];

        $response = $this->postJson('/api/v1/roles', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_update_role_updates_role_and_permissions()
    {
        $role = Role::factory()->create(['name' => 'Original Role']);
        $newPermission = Permission::factory()->create();

        $data = [
            'name' => 'Updated Role',
            'permissions' => [$newPermission->name]
        ];

        $response = $this->putJson("/api/v1/roles/{$role->id}", $data);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Rol actualizado correctamente', 'role' => ['name' => 'Updated Role']]);
        $this->assertDatabaseHas('roles', ['name' => 'Updated Role']);
        $this->assertTrue($role->hasPermissionTo($newPermission));
    }

    public function test_update_role_returns_404_if_role_not_found()
    {
        $response = $this->putJson('/api/v1/roles/9999', ['name' => 'Updated Role']);
        $response->assertStatus(404);
        $response->assertJson(['message' => 'Rol no encontrado']);
    }

    public function test_destroy_role_deletes_role()
    {
        $role = Role::factory()->create();

        $response = $this->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Rol eliminado']);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_destroy_role_returns_404_if_role_not_found()
    {
        $response = $this->deleteJson('/api/v1/roles/9999');
        $response->assertStatus(404);
        $response->assertJson(['message' => 'Rol no encontrado']);
    }
}
