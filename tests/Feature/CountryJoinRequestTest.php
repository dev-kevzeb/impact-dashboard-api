<?php

namespace Tests\Feature;

use App\Modules\Country\Domain\Country;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\Currency\Domain\Currency;
use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryJoinRequestTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/country-join-requests';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_project_manager_can_submit_join_request_to_active_country(): void
    {
        [$targetCountry, $otherCountry] = $this->createCountries();

        [$pmUser, $pmUserRole, $pmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'pm1@test.com',
            'project-manager',
            $otherCountry->id
        );

        $response = $this->postJson(self::BASE_URL, [
            'country_id' => $targetCountry->id,
        ], $pmHeaders);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.country.id', $targetCountry->id)
            ->assertJsonPath('data.requester_user_role.id', $pmUserRole->id);

        $this->assertDatabaseHas('country_join_request', [
            'country_id' => $targetCountry->id,
            'requester_user_role_id' => $pmUserRole->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('country_user_role', [
            'country_id' => $targetCountry->id,
            'user_role_id' => $pmUserRole->id,
        ]);
    }

    public function test_project_manager_cannot_submit_request_to_inactive_country(): void
    {
        $currency = Currency::factory()->create();
        $inactiveCountry = Country::factory()->create([
            'currency_id' => $currency->id,
            'active' => false,
        ]);

        [, , $pmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'pm2@test.com',
            'project-manager',
            Country::factory()->create(['currency_id' => $currency->id, 'active' => true])->id
        );

        $response = $this->postJson(self::BASE_URL, [
            'country_id' => $inactiveCountry->id,
        ], $pmHeaders);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Only active countries can receive join requests.');
    }

    public function test_country_manager_can_approve_request_and_assign_country_membership(): void
    {
        [$targetCountry, $otherCountry] = $this->createCountries();

        [, $pmUserRole, $pmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'pm3@test.com',
            'project-manager',
            $otherCountry->id
        );

        [, , $cmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'cm1@test.com',
            'country-manager',
            $targetCountry->id
        );

        $createResponse = $this->postJson(self::BASE_URL, ['country_id' => $targetCountry->id], $pmHeaders);
        $createResponse->assertCreated();

        $requestId = (int) $createResponse->json('data.id');

        $reviewResponse = $this->putJson(self::BASE_URL . '/' . $requestId, [
            'action' => 'approve',
        ], $cmHeaders);

        $reviewResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('country_user_role', [
            'country_id' => $targetCountry->id,
            'user_role_id' => $pmUserRole->id,
        ]);
    }

    public function test_country_manager_can_revoke_request_and_remove_country_membership(): void
    {
        [$targetCountry, $otherCountry] = $this->createCountries();

        [, $pmUserRole, $pmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'pm4@test.com',
            'project-manager',
            $otherCountry->id
        );

        [, , $cmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'cm2@test.com',
            'country-manager',
            $targetCountry->id
        );

        $createResponse = $this->postJson(self::BASE_URL, ['country_id' => $targetCountry->id], $pmHeaders);
        $requestId = (int) $createResponse->json('data.id');

        $this->putJson(self::BASE_URL . '/' . $requestId, [
            'action' => 'approve',
        ], $cmHeaders)->assertOk();

        $this->assertDatabaseHas('country_user_role', [
            'country_id' => $targetCountry->id,
            'user_role_id' => $pmUserRole->id,
        ]);

        $revokeResponse = $this->putJson(self::BASE_URL . '/' . $requestId, [
            'action' => 'revoke',
        ], $cmHeaders);

        $revokeResponse->assertOk()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseMissing('country_user_role', [
            'country_id' => $targetCountry->id,
            'user_role_id' => $pmUserRole->id,
        ]);
    }

    private function createCountries(): array
    {
        $currency = Currency::factory()->create();

        $targetCountry = Country::factory()->create([
            'currency_id' => $currency->id,
            'active' => true,
            'name' => 'Bolivia',
        ]);

        $otherCountry = Country::factory()->create([
            'currency_id' => $currency->id,
            'active' => true,
            'name' => 'Paraguay',
        ]);

        return [$targetCountry, $otherCountry];
    }

    private function createAuthenticatedUserWithRoleAndCountry(string $email, string $roleName, int $countryId): array
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();
        $role = Role::where('name', $roleName)->where('guard_name', 'api')->firstOrFail();

        /** @var User $user */
        $user = User::factory()->create([
            'email' => $email,
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);

        $userRole = UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        CountryUserRole::firstOrCreate([
            'country_id' => $countryId,
            'user_role_id' => $userRole->id,
        ]);

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->login($user);

        return [
            $user,
            $userRole,
            [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ];
    }

    public function test_project_manager_does_not_see_own_country_in_public_list(): void
    {
        [$countryA, $countryB] = $this->createCountries();

        [, , $pmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'pm5@test.com',
            'project-manager',
            $countryA->id
        );

        $response = $this->getJson('/api/v1/countries?active=1', $pmHeaders);
        $response->assertOk()->assertJsonPath('success', true);

        $countries = $response->json('data.countries');
        $ids = array_map(fn($c) => $c['id'], $countries);

        $this->assertNotContains($countryA->id, $ids);
        $this->assertContains($countryB->id, $ids);
    }

    public function test_project_manager_approved_in_other_country_sees_it_as_collaborating(): void
    {
        [$countryA, $countryB] = $this->createCountries();

        [, $pmUserRole, $pmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'pm6@test.com',
            'project-manager',
            $countryA->id
        );

        [, , $cmHeaders] = $this->createAuthenticatedUserWithRoleAndCountry(
            'cm3@test.com',
            'country-manager',
            $countryB->id
        );

        $createResponse = $this->postJson(self::BASE_URL, ['country_id' => $countryB->id], $pmHeaders);
        $createResponse->assertCreated();

        $requestId = (int) $createResponse->json('data.id');

        $this->putJson(self::BASE_URL . '/' . $requestId, [
            'action' => 'approve',
        ], $cmHeaders)->assertOk();

        $response = $this->getJson('/api/v1/countries?active=1', $pmHeaders);
        $response->assertOk()->assertJsonPath('success', true);

        $countries = $response->json('data.countries');
        $ids = array_map(fn($c) => $c['id'], $countries);
        $countryBData = collect($countries)->firstWhere('id', $countryB->id);

        $this->assertNotContains($countryA->id, $ids);
        $this->assertContains($countryB->id, $ids);
        $this->assertSame('collaborating', $countryBData['relationship_status']);
    }
}
