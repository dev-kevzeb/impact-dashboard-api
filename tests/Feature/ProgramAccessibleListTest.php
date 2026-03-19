<?php

namespace Tests\Feature;

use App\Modules\Contact\Domain\Contact;
use App\Modules\InviteProgram\Domain\InviteProgram;
use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Sdg\Domain\Sdg;
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
        $otherOwnerAssignment = ProgramCountryUserRole::factory()->create([
            'program_id' => $invitedProgram->id,
        ]);

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

    public function test_cannot_update_program_where_user_is_only_invited(): void
    {
        $auth = $this->authHeadersWithCountry('project-manager');
        $ownerCountryUserRole = $auth['countryUserRole'];

        $invitedProgram = Program::factory()->create();
        $otherOwnerAssignment = ProgramCountryUserRole::factory()->create([
            'program_id' => $invitedProgram->id,
        ]);

        InviteProgram::create([
            'program_country_user_role_id' => $otherOwnerAssignment->id,
            'invited_user_role_id' => $ownerCountryUserRole->user_role_id,
        ]);

        $contact = Contact::factory()->create();
        $state = ProgramState::factory()->create();
        $sdg = Sdg::factory()->create();

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
}
