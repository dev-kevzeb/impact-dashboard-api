<?php

namespace Tests\Feature;

use App\Modules\InviteProgram\Domain\InviteProgram;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteProgramTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/invite_programs';

    private const ERROR_PROGRAM_COUNTRY_USER_ROLE_REQUIRED = 'The program country user role ID is required.';
    private const ERROR_PROGRAM_COUNTRY_USER_ROLE_INTEGER = 'The program country user role ID must be an integer.';
    private const ERROR_PROGRAM_COUNTRY_USER_ROLE_EXISTS = 'The selected program country user role does not exist.';
    private const ERROR_INVITED_USER_ROLE_REQUIRED = 'The invited user role ID is required.';
    private const ERROR_INVITED_USER_ROLE_INTEGER = 'The invited user role ID must be an integer.';
    private const ERROR_INVITED_USER_ROLE_EXISTS = 'The selected invited user role does not exist.';
    private const ERROR_SELF_INVITE = 'You cannot invite yourself.';
    private const ERROR_ONLY_OWNER_CAN_INVITE = 'Only the program owner can invite project managers.';
    private const ERROR_ONLY_PROJECT_MANAGER_ALLOWED = 'Only project-manager roles can be invited.';
    private const ERROR_DUPLICATE_INVITE = 'This user role is already invited to this program.';
    private const ERROR_NOT_ALLOWED_TO_REMOVE = 'You are not allowed to remove this invite.';
    private const ERROR_INVITE_NOT_FOUND_SHOW = 'InviteProgram not found with id: 99999 not Found';
    private const ERROR_INVITE_NOT_FOUND_DELETE = 'InviteProgram not found with id: 99999';

    private array $authContext;
    private ProgramCountryUserRole $ownerAssignment;

    protected function setUp(): void
    {
        parent::setUp();

        // Owner authenticated as project-manager with country context.
        $this->authContext = $this->authHeadersWithCountry('project-manager');

        $program = Program::factory()->create();
        $this->ownerAssignment = ProgramCountryUserRole::create([
            'program_id' => $program->id,
            'country_user_role_id' => $this->authContext['countryUserRole']->id,
        ]);
    }

    private function createInvitedProjectManagerUserRole(): UserRole
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();
        $pmRole = Role::where('name', 'project-manager')->firstOrFail();

        $invitedUser = User::factory()->create([
            'user_state_id' => $activeState->id,
        ]);

        return UserRole::create([
            'user_id' => $invitedUser->id,
            'role_id' => $pmRole->id,
        ]);
    }

    private function createUserRoleByRoleName(string $roleName): UserRole
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();
        $role = Role::where('name', $roleName)->firstOrFail();

        $user = User::factory()->create([
            'user_state_id' => $activeState->id,
        ]);

        return UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    private function headersForUser(User $user): array
    {
        $guard = auth('api');
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $token = $guard->login($user);

        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
    }

    public function test_can_create_invite_successfully(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ], $this->authContext['headers']);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Invite created successfully')
            ->assertJsonPath('data.program_country_user_role_id', $this->ownerAssignment->id)
            ->assertJsonPath('data.invited_user_role_id', $invitedUserRole->id);

        $this->assertDatabaseHas('invite_program', [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ]);
    }

    public function test_create_requires_program_country_user_role_id(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $response = $this->postJson(self::BASE_URL, [
            'invited_user_role_id' => $invitedUserRole->id,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_country_user_role_id'])
            ->assertJsonPath('errors.program_country_user_role_id.0', self::ERROR_PROGRAM_COUNTRY_USER_ROLE_REQUIRED);
    }

    public function test_create_requires_program_country_user_role_id_to_be_integer(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => 'abc',
            'invited_user_role_id' => $invitedUserRole->id,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_country_user_role_id'])
            ->assertJsonPath('errors.program_country_user_role_id.0', self::ERROR_PROGRAM_COUNTRY_USER_ROLE_INTEGER);
    }

    public function test_create_requires_program_country_user_role_id_to_exist(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => 99999,
            'invited_user_role_id' => $invitedUserRole->id,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_country_user_role_id'])
            ->assertJsonPath('errors.program_country_user_role_id.0', self::ERROR_PROGRAM_COUNTRY_USER_ROLE_EXISTS);
    }

    public function test_create_requires_invited_user_role_id(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => $this->ownerAssignment->id,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invited_user_role_id'])
            ->assertJsonPath('errors.invited_user_role_id.0', self::ERROR_INVITED_USER_ROLE_REQUIRED);
    }

    public function test_create_requires_invited_user_role_id_to_be_integer(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => 'xyz',
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invited_user_role_id'])
            ->assertJsonPath('errors.invited_user_role_id.0', self::ERROR_INVITED_USER_ROLE_INTEGER);
    }

    public function test_create_requires_invited_user_role_id_to_exist(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => 99999,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invited_user_role_id'])
            ->assertJsonPath('errors.invited_user_role_id.0', self::ERROR_INVITED_USER_ROLE_EXISTS);
    }

    public function test_project_manager_cannot_self_invite(): void
    {
        $ownerUserRoleId = (int) $this->authContext['countryUserRole']->user_role_id;

        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $ownerUserRoleId,
        ], $this->authContext['headers']);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::ERROR_SELF_INVITE);

        $this->assertDatabaseMissing('invite_program', [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $ownerUserRoleId,
        ]);
    }

    public function test_only_owner_can_invite_project_managers(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $nonOwnerAdminRole = $this->createUserRoleByRoleName('admin');
        $nonOwnerHeaders = $this->headersForUser($nonOwnerAdminRole->user);

        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ], $nonOwnerHeaders);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Insufficient permissions. Required scope: program_country_user_roles:write');
    }

    public function test_only_project_manager_roles_can_be_invited(): void
    {
        $invitedCountryManagerRole = $this->createUserRoleByRoleName('country-manager');

        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedCountryManagerRole->id,
        ], $this->authContext['headers']);

        $response->assertStatus(400)
            ->assertJsonPath('message', self::ERROR_ONLY_PROJECT_MANAGER_ALLOWED);
    }

    public function test_cannot_create_duplicate_invite_and_returns_validation_warning_message(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $payload = [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ];

        $this->postJson(self::BASE_URL, $payload, $this->authContext['headers'])
            ->assertCreated();

        $response = $this->postJson(self::BASE_URL, $payload, $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invited_user_role_id'])
            ->assertJsonPath('errors.invited_user_role_id.0', self::ERROR_DUPLICATE_INVITE);
    }

    public function test_owner_can_list_invite_candidates_with_minimal_fields(): void
    {
        $pmOne = $this->createInvitedProjectManagerUserRole();
        $pmTwo = $this->createInvitedProjectManagerUserRole();

        // Should be excluded because only project-manager candidates are allowed
        $this->createUserRoleByRoleName('country-manager');

        $response = $this->getJson(
            self::BASE_URL . '/candidates?program_country_user_role_id=' . $this->ownerAssignment->id . '&per_page=10',
            $this->authContext['headers']
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Invite candidates retrieved successfully')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'candidates' => [
                        '*' => ['id', 'name', 'email', 'agency'],
                    ],
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ]);

        $candidates = collect($response->json('data.candidates'));

        $this->assertTrue($candidates->contains(fn ($c) => $c['id'] === $pmOne->id));
        $this->assertTrue($candidates->contains(fn ($c) => $c['id'] === $pmTwo->id));
        $this->assertFalse($candidates->contains(fn ($c) => $c['id'] === $this->authContext['countryUserRole']->user_role_id));

        foreach ($candidates as $candidate) {
            $this->assertSame(['id', 'name', 'email', 'agency'], array_keys($candidate));
        }
    }

    public function test_candidates_support_search_by_name_or_email(): void
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();
        $pmRole = Role::where('name', 'project-manager')->firstOrFail();

        $alice = User::factory()->create([
            'name' => 'Alice Candidate',
            'email' => 'alice.invite@example.com',
            'user_state_id' => $activeState->id,
        ]);

        $bob = User::factory()->create([
            'name' => 'Bob External',
            'email' => 'bob.external@example.com',
            'user_state_id' => $activeState->id,
        ]);

        $aliceRole = UserRole::create(['user_id' => $alice->id, 'role_id' => $pmRole->id]);
        UserRole::create(['user_id' => $bob->id, 'role_id' => $pmRole->id]);

        $responseByName = $this->getJson(
            self::BASE_URL . '/candidates?program_country_user_role_id=' . $this->ownerAssignment->id . '&search=alice',
            $this->authContext['headers']
        );

        $responseByName->assertOk();
        $this->assertCount(1, $responseByName->json('data.candidates'));
        $this->assertSame($aliceRole->id, $responseByName->json('data.candidates.0.id'));

        $responseByEmail = $this->getJson(
            self::BASE_URL . '/candidates?program_country_user_role_id=' . $this->ownerAssignment->id . '&search=external@example.com',
            $this->authContext['headers']
        );

        $responseByEmail->assertOk();
        $this->assertCount(1, $responseByEmail->json('data.candidates'));
        $this->assertSame('bob.external@example.com', $responseByEmail->json('data.candidates.0.email'));
    }

    public function test_non_owner_cannot_list_invite_candidates(): void
    {
        $nonOwnerAdminRole = $this->createUserRoleByRoleName('admin');
        $nonOwnerHeaders = $this->headersForUser($nonOwnerAdminRole->user);

        $response = $this->getJson(
            self::BASE_URL . '/candidates?program_country_user_role_id=' . $this->ownerAssignment->id,
            $nonOwnerHeaders
        );

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Insufficient permissions. Required scope: program_country_user_roles');
    }

    public function test_show_returns_not_found_when_invite_does_not_exist(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999', $this->authContext['headers']);

        $response->assertNotFound()
            ->assertJsonPath('message', self::ERROR_INVITE_NOT_FOUND_SHOW);
    }

    public function test_delete_returns_not_found_when_invite_does_not_exist(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/99999', [], $this->authContext['headers']);

        $response->assertStatus(400)
            ->assertJsonPath('message', self::ERROR_INVITE_NOT_FOUND_DELETE);
    }

    public function test_owner_can_remove_invite_successfully(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $invite = InviteProgram::create([
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $invite->id, [], $this->authContext['headers']);

        $response->assertOk()
            ->assertJsonPath('message', 'Invite removed successfully');

        $this->assertDatabaseMissing('invite_program', ['id' => $invite->id]);
    }

    public function test_invited_user_can_remove_own_invite_successfully(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $invite = InviteProgram::create([
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ]);

        $invitedHeaders = $this->headersForUser($invitedUserRole->user);

        $response = $this->deleteJson(self::BASE_URL . '/' . $invite->id, [], $invitedHeaders);

        $response->assertOk()
            ->assertJsonPath('message', 'Invite removed successfully');

        $this->assertDatabaseMissing('invite_program', ['id' => $invite->id]);
    }

    public function test_third_party_user_cannot_remove_invite(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $invite = InviteProgram::create([
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ]);

        $thirdPartyRole = $this->createUserRoleByRoleName('admin');
        $thirdPartyHeaders = $this->headersForUser($thirdPartyRole->user);

        $response = $this->deleteJson(self::BASE_URL . '/' . $invite->id, [], $thirdPartyHeaders);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Insufficient permissions. Required scope: program_country_user_roles:write');

        $this->assertDatabaseHas('invite_program', ['id' => $invite->id]);
    }

    public function test_create_requires_authentication(): void
    {
        $invitedUserRole = $this->createInvitedProjectManagerUserRole();

        $response = $this->postJson(self::BASE_URL, [
            'program_country_user_role_id' => $this->ownerAssignment->id,
            'invited_user_role_id' => $invitedUserRole->id,
        ]);

        $response->assertStatus(401);
    }
}
