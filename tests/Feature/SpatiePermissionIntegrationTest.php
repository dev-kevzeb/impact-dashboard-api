<?php

namespace Tests\Feature;

use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SpatiePermissionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/auth';

    /**
     * Setup: Seed roles and permissions before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create user state
        UserState::create(['name' => 'active']);

        // Seed roles first
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        // Seed permissions
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);

        // Seed role permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /**
     * Test 1: Admin login generates JWT with wildcard scope from database
     */
    public function test_admin_login_generates_jwt_with_wildcard_scope(): void
    {
        // Arrange: Get active user state
        $activeState = UserState::where('name', 'active')->first();

        // Arrange: Create admin user
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password123',
            'user_state_id' => $activeState->id,
        ]);
        $admin->assignRole('admin');

        // Act: Login
        $response = $this->postJson(self::BASE_URL . '/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Assert: Response success
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['access_token']]);

        // Assert: Admin can access all endpoints (wildcard permission)
        $token = $response->json('data.access_token');

        // Test admin wildcard access by trying to create a donor
        $createResponse = $this->postJson(
            '/api/v1/donors',
            ['name' => 'Test Donor'],
            ['Authorization' => 'Bearer ' . $token]
        );

        $createResponse->assertStatus(201);  // Admin should have full access
    }

    /**
     * Test 2: Project Manager has correct permissions from database (not hardcoded)
     */
    public function test_project_manager_login_generates_correct_scopes_from_database(): void
    {
        // Arrange: Get active user state
        $activeState = UserState::where('name', 'active')->first();

        // Arrange: Create project-manager user
        $projectManager = User::factory()->create([
            'email' => 'pm@test.com',
            'password' => 'password123',
            'user_state_id' => $activeState->id,
        ]);
        $projectManager->assignRole('project-manager');

        // Act: Login
        $response = $this->postJson(self::BASE_URL . '/login', [
            'email' => 'pm@test.com',
            'password' => 'password123',
        ]);

        // Assert: Response success
        $response->assertStatus(200);

        // Assert: Project Manager has correct permissions from database
        $token = $response->json('data.access_token');

        // PM should have projects:write (can create project)
        $canAccessProjects = $this->postJson(
            '/api/v1/projects',
            [
                'name' => 'Test Project',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'budget' => 100000,
                'currency_id' => 1,
                'project_state_id' => 1,
                'program_id' => 1
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        // Should succeed (has projects:write)
        $this->assertContains($canAccessProjects->status(), [200, 201, 422]);  // 422 if FK missing

        // PM should NOT have users:write (cannot create user)
        $cannotAccessUsers = $this->postJson(
            '/api/v1/users',
            ['name' => 'Test User', 'email' => 'test@test.com'],
            ['Authorization' => 'Bearer ' . $token]
        );

        $cannotAccessUsers->assertStatus(403);  // Forbidden
    }

    /**
     * Test 3: Country Manager has correct permissions from database
     */
    public function test_country_manager_login_generates_correct_scopes_from_database(): void
    {
        // Arrange: Get active user state
        $activeState = UserState::where('name', 'active')->first();

        // Arrange: Create country-manager user
        $countryManager = User::factory()->create([
            'email' => 'cm@test.com',
            'password' => 'password123',
            'user_state_id' => $activeState->id,
        ]);
        $countryManager->assignRole('country-manager');

        // Act: Login
        $response = $this->postJson(self::BASE_URL . '/login', [
            'email' => 'cm@test.com',
            'password' => 'password123',
        ]);

        // Assert: Response success
        $response->assertStatus(200);

        // Assert: Country Manager has correct permissions from database
        $token = $response->json('data.access_token');

        // CM should have kpas:write (can create KPA)
        $canAccessKpas = $this->postJson(
            '/api/v1/kpas',
            ['name' => 'Test KPA'],
            ['Authorization' => 'Bearer ' . $token]
        );

        $this->assertContains($canAccessKpas->status(), [200, 201, 422]);

        // CM should NOT have projects:write (cannot create project)
        $cannotAccessProjects = $this->postJson(
            '/api/v1/projects',
            [
                'name' => 'Test Project',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'budget' => 100000,
                'currency_id' => 1,
                'project_state_id' => 1,
                'program_id' => 1
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $cannotAccessProjects->assertStatus(403);  // Forbidden
    }

    /**
     * Test 4: Direct user permissions are included in JWT (user_permission table)
     */
    public function test_direct_user_permissions_are_included_in_jwt(): void
    {
        // Arrange: Get active user state
        $activeState = UserState::where('name', 'active')->first();

        // Arrange: Create project-manager with additional direct permission
        $projectManager = User::factory()->create([
            'email' => 'pm-special@test.com',
            'password' => 'password123',
            'user_state_id' => $activeState->id,
        ]);
        $projectManager->assignRole('project-manager');

        // Give direct permission (exception)
        $projectManager->givePermissionTo('users:write');

        // Act: Login
        $response = $this->postJson(self::BASE_URL . '/login', [
            'email' => 'pm-special@test.com',
            'password' => 'password123',
        ]);

        // Assert: JWT includes both role permissions + direct permission
        $token = $response->json('data.access_token');

        // PM normally doesn't have users:write, but we gave it directly
        $canAccessUsers = $this->postJson(
            '/api/v1/users',
            [
                'name' => 'Test User',
                'email' => 'newuser@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'user_state_id' => 1,
                'role_id' => 1
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        // Should succeed because of direct permission
        $this->assertContains($canAccessUsers->status(), [200, 201, 422]);  // Not 403
    }

    /**
     * Test 5: Middleware CheckScope validates permissions from database
     */
    public function test_middleware_validates_scopes_from_database(): void
    {
        // Arrange: Get active user state
        $activeState = UserState::where('name', 'active')->first();

        // Arrange: Create user with limited permissions
        $user = User::factory()->create([
            'email' => 'limited@test.com',
            'password' => 'password123',
            'user_state_id' => $activeState->id,
        ]);
        $user->assignRole('project-manager'); // Has donors:read but NOT donors:write

        // Login to get token
        $loginResponse = $this->postJson(self::BASE_URL . '/login', [
            'email' => 'limited@test.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.access_token');

        // Act: Try to access endpoint requiring donors:write (not allowed)
        $response = $this->postJson(
            '/api/v1/donors',
            ['name' => 'New Donor'],
            ['Authorization' => 'Bearer ' . $token]
        );

        // Assert: Should be forbidden (project-manager has donors:read, not donors:write)
        $response->assertStatus(403);
        $this->assertTrue($response->json('success') === false);
        $this->assertStringContainsString('Insufficient permissions', $response->json('message'));
    }

    /**
     * Test 6: User without any role gets fallback permissions
     */
    public function test_user_without_role_gets_fallback_permissions(): void
    {
        // Arrange: Get active user state
        $activeState = UserState::where('name', 'active')->first();

        // Arrange: Create user without any role
        $user = User::factory()->create([
            'email' => 'norole@test.com',
            'password' => 'password123',
            'user_state_id' => $activeState->id,
        ]);
        // Note: NO role assigned

        // Act: Login
        $response = $this->postJson(self::BASE_URL . '/login', [
            'email' => 'norole@test.com',
            'password' => 'password123',
        ]);

        // Assert: Gets minimal fallback permissions
        $token = $response->json('data.access_token');

        // User without role should have fallback: donors:read, beneficiaries:read
        $canReadDonors = $this->getJson(
            '/api/v1/donors',
            ['Authorization' => 'Bearer ' . $token]
        );

        $canReadDonors->assertStatus(200);  // Has donors:read (fallback)

        // But cannot write
        $cannotWriteDonors = $this->postJson(
            '/api/v1/donors',
            ['name' => 'Test Donor'],
            ['Authorization' => 'Bearer ' . $token]
        );

        $cannotWriteDonors->assertStatus(403);  // No donors:write
    }

    /**
     * Test 7: Revoking permission from role reflects in next login
     */
    public function test_revoking_permission_reflects_in_new_jwt(): void
    {
        // Arrange: Get active user state
        $activeState = UserState::where('name', 'active')->first();

        // Arrange: Create project-manager
        $projectManager = User::factory()->create([
            'email' => 'pm-revoke@test.com',
            'password' => 'password123',
            'user_state_id' => $activeState->id,
        ]);
        $projectManager->assignRole('project-manager');

        // Act 1: Login and verify has projects:write
        $response1 = $this->postJson(self::BASE_URL . '/login', [
            'email' => 'pm-revoke@test.com',
            'password' => 'password123',
        ]);
        $token1 = $response1->json('data.access_token');

        // Verify can create project (has projects:write)
        $canCreate1 = $this->postJson(
            '/api/v1/projects',
            [
                'name' => 'Test Project Before Revoke',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'budget' => 100000,
                'currency_id' => 1,
                'project_state_id' => 1,
                'program_id' => 1
            ],
            ['Authorization' => 'Bearer ' . $token1]
        );
        $this->assertContains($canCreate1->status(), [200, 201, 422]);  // Has permission

        // Act 2: Revoke projects:write from project-manager role
        $role = Role::where('name', 'project-manager')->first();
        $role->revokePermissionTo('projects:write');

        // Act 3: Login again
        $response2 = $this->postJson(self::BASE_URL . '/login', [
            'email' => 'pm-revoke@test.com',
            'password' => 'password123',
        ]);
        $token2 = $response2->json('data.access_token');

        // Assert: projects:write should NOT work anymore
        $cannotCreate2 = $this->postJson(
            '/api/v1/projects',
            [
                'name' => 'Test Project After Revoke',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'budget' => 100000,
                'currency_id' => 1,
                'project_state_id' => 1,
                'program_id' => 1
            ],
            ['Authorization' => 'Bearer ' . $token2]
        );

        $cannotCreate2->assertStatus(403);  // Permission revoked
    }
}
