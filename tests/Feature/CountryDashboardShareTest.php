<?php

namespace Tests\Feature;

use App\Notifications\CountryDashboardAccessNotification;
use App\Modules\Country\Domain\Country;
use App\Modules\Currency\Domain\Currency;
use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CountryDashboardShareTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/country-dashboard-shares';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_country_manager_can_share_country_dashboard_with_admin(): void
    {
        Notification::fake();

        $countryContext = $this->authHeadersWithCountry('country-manager');
        $adminUserRoleId = $this->getUserRoleIdForTestUser('admin');

        $payload = [
            'country_id' => $countryContext['countryUserRole']->country_id,
            'shared_user_role_id' => $adminUserRoleId,
        ];

        $response = $this->postJson(self::BASE_URL, $payload, $countryContext['headers']);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Country dashboard shared successfully',
            ]);

        $this->assertDatabaseHas('country_dashboard_share', [
            'country_id' => $payload['country_id'],
            'owner_country_user_role_id' => $countryContext['countryUserRole']->id,
            'shared_user_role_id' => $adminUserRoleId,
        ]);

        $adminUser = User::where('email', 'test@pacific.com')->firstOrFail();
        Notification::assertSentTo(
            $adminUser,
            CountryDashboardAccessNotification::class,
            fn (CountryDashboardAccessNotification $notification) => $notification->isGranted() === true
        );
    }

    public function test_country_manager_cannot_share_dashboard_for_other_country(): void
    {
        $countryContext = $this->authHeadersWithCountry('country-manager');
        $adminUserRoleId = $this->getUserRoleIdForTestUser('admin');

        $currency = Currency::factory()->create();
        $otherCountry = Country::factory()->create(['currency_id' => $currency->id]);

        $response = $this->postJson(self::BASE_URL, [
            'country_id' => $otherCountry->id,
            'shared_user_role_id' => $adminUserRoleId,
        ], $countryContext['headers']);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'You can only share dashboards for your assigned country.',
            ]);
    }

    public function test_country_manager_cannot_share_with_non_admin_role(): void
    {
        $countryContext = $this->authHeadersWithCountry('country-manager');
        $projectManagerUserRoleId = $this->getUserRoleIdForTestUser('project-manager');

        $response = $this->postJson(self::BASE_URL, [
            'country_id' => $countryContext['countryUserRole']->country_id,
            'shared_user_role_id' => $projectManagerUserRoleId,
        ], $countryContext['headers']);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Country dashboard can only be shared with admin users.',
            ]);
    }

    public function test_admin_can_list_visible_country_dashboard_shares(): void
    {
        $countryContext = $this->authHeadersWithCountry('country-manager');
        $adminHeaders = $this->authHeaders('admin');
        $adminUserRoleId = $this->getUserRoleIdForTestUser('admin');

        $this->postJson(self::BASE_URL, [
            'country_id' => $countryContext['countryUserRole']->country_id,
            'shared_user_role_id' => $adminUserRoleId,
        ], $countryContext['headers'])->assertCreated();

        $response = $this->getJson(self::BASE_URL . '/visible-for-admin', $adminHeaders);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Visible country dashboards retrieved successfully',
            ])
            ->assertJsonPath('data.total', 1);
    }

    public function test_country_manager_can_list_admin_share_candidates(): void
    {
        $countryContext = $this->authHeadersWithCountry('country-manager');

        $activeState = UserState::where('name', 'active')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'api')->firstOrFail();

        $adminCandidate = User::factory()->create([
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);

        UserRole::create([
            'user_id' => $adminCandidate->id,
            'role_id' => $adminRole->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '/admin-candidates', $countryContext['headers']);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Admin share candidates retrieved successfully',
            ])
            ->assertJsonPath('data.total', 1);
    }

    public function test_admin_cannot_list_admin_share_candidates(): void
    {
        $adminHeaders = $this->authHeaders('admin');

        $response = $this->getJson(self::BASE_URL . '/admin-candidates', $adminHeaders);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Only country-manager can list admin share candidates.',
            ]);
    }

    public function test_country_manager_can_revoke_own_share(): void
    {
        Notification::fake();

        $countryContext = $this->authHeadersWithCountry('country-manager');
        $adminUserRoleId = $this->getUserRoleIdForTestUser('admin');

        $createResponse = $this->postJson(self::BASE_URL, [
            'country_id' => $countryContext['countryUserRole']->country_id,
            'shared_user_role_id' => $adminUserRoleId,
        ], $countryContext['headers'])->assertCreated();

        $shareId = (int) $createResponse->json('data.id');

        $deleteResponse = $this->deleteJson(self::BASE_URL . '/' . $shareId, [], $countryContext['headers']);

        $deleteResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Country dashboard share revoked successfully',
            ]);

        $this->assertDatabaseMissing('country_dashboard_share', ['id' => $shareId]);

        $adminUser = User::where('email', 'test@pacific.com')->firstOrFail();
        Notification::assertSentTo(
            $adminUser,
            CountryDashboardAccessNotification::class,
            fn (CountryDashboardAccessNotification $notification) => $notification->isGranted() === false
        );
    }

    private function getUserRoleIdForTestUser(string $roleName): int
    {
        $this->authenticateUser($roleName);

        $user = User::where('email', 'test@pacific.com')->firstOrFail();
        $role = Role::where('name', $roleName)->where('guard_name', 'api')->firstOrFail();

        return (int) UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->firstOrFail()
            ->id;
    }
}
