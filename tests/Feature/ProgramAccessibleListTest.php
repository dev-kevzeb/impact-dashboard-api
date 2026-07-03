<?php

namespace Tests\Feature;

use App\Modules\Contact\Domain\Contact;
use App\Modules\InviteProgram\Domain\InviteProgram;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Sdg\Domain\Sdg;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserRole\Domain\UserRole;
use App\Modules\UserState\Domain\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramAccessibleListTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_list_includes_owned_and_invited_with_can_edit_flag(): void
    {
        $auth = $this->authHeadersWithCountry('project-manager');
        $ownerCountryUserRole = $auth['countryUserRole'];

        $ownedProgram = Program::factory()->create();
        ProgramCountryUserRole::create([
            'program_id' => $ownedProgram->id,
            'country_user_role_id' => $ownerCountryUserRole->id,
        ]);

        $invitedProgram = Program::factory()->create();
        $otherOwnerAssignment = $this->createProgramOwnerAssignmentForDifferentPm($invitedProgram->id, $ownerCountryUserRole->country_id);

        InviteProgram::create([
            'program_country_user_role_id' => $otherOwnerAssignment->id,
            'invited_user_role_id' => $ownerCountryUserRole->user_role_id,
        ]);

        $unrelatedProgram = Program::factory()->create();

        $response = $this->getJson('/api/v1/programs?per_page=10', $auth['headers']);

        $response->assertOk();

        $programs = collect($response->json('data.programs'));

        $this->assertTrue($programs->contains(fn ($p) => $p['id'] === $ownedProgram->id && $p['can_edit'] === true));
        $this->assertTrue($programs->contains(fn ($p) => $p['id'] === $invitedProgram->id && $p['can_edit'] === false));
        $this->assertFalse($programs->contains(fn ($p) => $p['id'] === $unrelatedProgram->id));
    }

    public function test_program_list_includes_currency_code_per_country_in_country_user_roles(): void
    {
        $auth = $this->authHeadersWithCountry('project-manager');
        $ownerCountryUserRole = $auth['countryUserRole'];

        $country = \App\Modules\Country\Domain\Country::find($ownerCountryUserRole->country_id);
        $this->assertSame('USD', $country->currency->code, 'Test Country in TestCase must use USD currency');

        $ownedProgram = Program::factory()->create();
        ProgramCountryUserRole::create([
            'program_id' => $ownedProgram->id,
            'country_user_role_id' => $ownerCountryUserRole->id,
        ]);

        $response = $this->getJson('/api/v1/programs?per_page=10', $auth['headers']);

        $response->assertOk()
            ->assertJsonPath('data.programs.0.country_user_roles.0.country.id', $country->id)
            ->assertJsonPath('data.programs.0.country_user_roles.0.country.name', $country->name)
            ->assertJsonPath('data.programs.0.country_user_roles.0.country.active', true)
            ->assertJsonPath('data.programs.0.country_user_roles.0.country.currency_code', 'USD');
    }

    public function test_cannot_update_program_where_user_is_only_invited(): void
    {
        $auth = $this->authHeadersWithCountry('project-manager');
        $ownerCountryUserRole = $auth['countryUserRole'];

        $invitedProgram = Program::factory()->create();
        $otherOwnerAssignment = $this->createProgramOwnerAssignmentForDifferentPm($invitedProgram->id, $ownerCountryUserRole->country_id);

        InviteProgram::create([
            'program_country_user_role_id' => $otherOwnerAssignment->id,
            'invited_user_role_id' => $ownerCountryUserRole->user_role_id,
        ]);

        $contact = Contact::factory()->create();
        $state = ProgramState::factory()->create();
        $sdg = Sdg::create([
            'image' => 'sdg-1.png',
            'filename' => 'SDG 1',
        ]);

        $payload = [
            'name' => 'Updated invited program',
            'description' => 'Valid description for update action',
            'program_url' => 'https://example.org/invited-program',
            'program_state_id' => $state->id,
            'contact' => [
                'id' => $contact->id,
            ],
            'sdg_ids' => [$sdg->id],
        ];

        $response = $this->putJson('/api/v1/programs/' . $invitedProgram->id, $payload, $auth['headers']);
        $response->assertStatus(400)
            ->assertJsonPath('message', 'You do not have permission to edit this program.');
    }

    private function createProgramOwnerAssignmentForDifferentPm(int $programId, int $countryId): ProgramCountryUserRole
    {
        $activeState = UserState::where('name', 'active')->firstOrFail();
        $role = Role::where('name', 'project-manager')->where('guard_name', 'api')->firstOrFail();

        $otherUser = User::factory()->create([
            'email' => 'other-pm-' . uniqid() . '@test.com',
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);

        $otherUserRole = UserRole::create([
            'user_id' => $otherUser->id,
            'role_id' => $role->id,
        ]);

        $countryUserRole = CountryUserRole::create([
            'country_id' => $countryId,
            'user_role_id' => $otherUserRole->id,
        ]);

        return ProgramCountryUserRole::create([
            'program_id' => $programId,
            'country_user_role_id' => $countryUserRole->id,
        ]);
    }
}
