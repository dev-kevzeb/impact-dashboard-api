<?php

namespace Tests\Feature;

use App\Modules\User\Domain\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private array $adminHeaders;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->adminHeaders = $this->authHeaders('admin');
        $user = User::where('email', 'test@pacific.com')->first();
        $user->givePermissionTo(['roles:read', 'roles:write']);
        $this->adminHeaders['Authorization'] = 'Bearer ' . auth('api')->login($user);
    }

    public function test_roles_index_hides_admin_role(): void
    {
        $response = $this->getJson('/api/v1/roles', $this->adminHeaders);

        $response->assertOk()
            ->assertJsonMissing(['name' => 'admin'])
            ->assertJsonFragment(['name' => 'project-manager'])
            ->assertJsonFragment(['name' => 'country-manager']);
    }

    public function test_permissions_index_hides_wildcard_permission(): void
    {
        $response = $this->getJson('/api/v1/permissions', $this->adminHeaders);

        $response->assertOk()
            ->assertJsonMissing(['name' => '*:*'])
            ->assertJsonFragment(['name' => 'roles:read']);
    }

    public function test_admin_role_permissions_endpoint_is_not_manageable(): void
    {
        // Role ID 1 is seeded as 'admin'
        $response = $this->getJson('/api/v1/roles/1/permissions', $this->adminHeaders);

        $response->assertStatus(404);
    }

    public function test_cannot_assign_wildcard_permission_to_non_admin_role(): void
    {
        $wildcardPermission = Permission::where('name', '*:*')->where('guard_name', 'api')->firstOrFail();

        $response = $this->postJson(
            '/api/v1/roles/2/permissions',
            ['permission_id' => $wildcardPermission->id],
            $this->adminHeaders
        );

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'The wildcard permission cannot be managed through this endpoint.',
            ]);
    }

    public function test_cannot_remove_wildcard_permission_through_role_permissions_endpoint(): void
    {
        $wildcardPermission = Permission::where('name', '*:*')->where('guard_name', 'api')->firstOrFail();

        $response = $this->deleteJson(
            "/api/v1/roles/1/permissions/{$wildcardPermission->id}",
            [],
            $this->adminHeaders
        );

        $response->assertStatus(404);
    }
}