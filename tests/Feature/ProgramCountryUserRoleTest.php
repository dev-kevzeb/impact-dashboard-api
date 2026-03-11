<?php

namespace Tests\Feature;

use App\Modules\Program\Domain\Program;
use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\ProgramState\Domain\ProgramState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramCountryUserRoleTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/program_country_user_roles';

    // FormRequest validation errors (422)
    private const ERROR_PROGRAM_REQUIRED         = 'The program ID is required.';
    private const ERROR_PROGRAM_EXISTS           = 'The selected program does not exist.';
    private const ERROR_CUR_REQUIRED             = 'The country user role ID is required.';
    private const ERROR_CUR_EXISTS               = 'The selected country user role does not exist.';
    private const ERROR_CUR_UNIQUE               = 'This program is already assigned to this country user role.';

    private array $authContext;
    private Program $program;
    private ProgramState $inactiveState;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inactiveState = ProgramState::firstOrCreate(['name' => 'Inactive']);
        $this->authContext   = $this->authHeadersWithCountry('project-manager');
        $this->program       = Program::factory()->create(['program_state_id' => $this->inactiveState->id]);
    }

    // ========== LIST TESTS ==========

    public function test_can_list_assignments(): void
    {
        $cur = $this->authContext['countryUserRole'];
        Program::factory()->count(3)
            ->create(['program_state_id' => $this->inactiveState->id])
            ->each(fn($p) => ProgramCountryUserRole::create([
                'program_id'           => $p->id,
                'country_user_role_id' => $cur->id,
            ]));

        $response = $this->getJson(self::BASE_URL, $this->authContext['headers']);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'assignments',
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    public function test_list_returns_empty_when_no_assignments(): void
    {
        $response = $this->getJson(self::BASE_URL, $this->authContext['headers']);

        $response->assertOk()
            ->assertJsonPath('data.total', 0);
    }

    public function test_can_filter_by_program_id(): void
    {
        $cur = $this->authContext['countryUserRole'];

        ProgramCountryUserRole::create(['program_id' => $this->program->id, 'country_user_role_id' => $cur->id]);
        ProgramCountryUserRole::factory()->create(); // belongs to a different program & country

        $response = $this->getJson(self::BASE_URL . "?program_id={$this->program->id}", $this->authContext['headers']);

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.total'));
        $this->assertEquals($this->program->id, $response->json('data.assignments.0.program_id'));
    }

    public function test_can_filter_by_country_user_role_id(): void
    {
        $cur = $this->authContext['countryUserRole'];

        ProgramCountryUserRole::create(['program_id' => $this->program->id, 'country_user_role_id' => $cur->id]);
        ProgramCountryUserRole::factory()->create(); // belongs to a different countryUserRole

        $response = $this->getJson(self::BASE_URL . "?country_user_role_id={$cur->id}", $this->authContext['headers']);

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.total'));
        $this->assertEquals($cur->id, $response->json('data.assignments.0.country_user_role_id'));
    }

    // ========== CREATE TESTS ==========

    public function test_can_create_assignment(): void
    {
        $cur = $this->authContext['countryUserRole'];

        $response = $this->postJson(self::BASE_URL, [
            'program_id'           => $this->program->id,
            'country_user_role_id' => $cur->id,
        ], $this->authContext['headers']);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'program_id', 'country_user_role_id'],
            ]);

        $this->assertDatabaseHas('program_country_user_role', [
            'program_id'           => $this->program->id,
            'country_user_role_id' => $cur->id,
        ]);
    }

    public function test_program_id_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'country_user_role_id' => $this->authContext['countryUserRole']->id,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_id'])
            ->assertJsonPath('errors.program_id.0', self::ERROR_PROGRAM_REQUIRED);
    }

    public function test_country_user_role_id_is_required(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'program_id' => $this->program->id,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_user_role_id'])
            ->assertJsonPath('errors.country_user_role_id.0', self::ERROR_CUR_REQUIRED);
    }

    public function test_program_must_exist(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'program_id'           => 99999,
            'country_user_role_id' => $this->authContext['countryUserRole']->id,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_id'])
            ->assertJsonPath('errors.program_id.0', self::ERROR_PROGRAM_EXISTS);
    }

    public function test_country_user_role_must_exist(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'program_id'           => $this->program->id,
            'country_user_role_id' => 99999,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_user_role_id'])
            ->assertJsonPath('errors.country_user_role_id.0', self::ERROR_CUR_EXISTS);
    }

    public function test_cannot_create_duplicate_assignment(): void
    {
        $cur = $this->authContext['countryUserRole'];

        ProgramCountryUserRole::create([
            'program_id'           => $this->program->id,
            'country_user_role_id' => $cur->id,
        ]);

        $response = $this->postJson(self::BASE_URL, [
            'program_id'           => $this->program->id,
            'country_user_role_id' => $cur->id,
        ], $this->authContext['headers']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_user_role_id']);
    }

    // ========== SHOW TESTS ==========

    public function test_can_show_assignment(): void
    {
        $cur        = $this->authContext['countryUserRole'];
        $assignment = ProgramCountryUserRole::create([
            'program_id'           => $this->program->id,
            'country_user_role_id' => $cur->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/{$assignment->id}", $this->authContext['headers']);

        $response->assertOk()
            ->assertJsonPath('data.id', $assignment->id)
            ->assertJsonPath('data.program_id', $this->program->id)
            ->assertJsonPath('data.country_user_role_id', $cur->id);
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999', $this->authContext['headers']);

        $response->assertNotFound();
    }

    // ========== DELETE TESTS ==========

    public function test_can_delete_assignment(): void
    {
        $cur        = $this->authContext['countryUserRole'];
        $assignment = ProgramCountryUserRole::create([
            'program_id'           => $this->program->id,
            'country_user_role_id' => $cur->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . "/{$assignment->id}", [], $this->authContext['headers']);

        $response->assertOk();

        $this->assertDatabaseMissing('program_country_user_role', ['id' => $assignment->id]);
        // Program still exists after unlinking
        $this->assertDatabaseHas('program', ['id' => $this->program->id]);
    }

    public function test_delete_returns_404_when_not_found(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/99999', [], $this->authContext['headers']);

        $response->assertNotFound();
    }

    public function test_delete_unlinks_program_without_deleting_it(): void
    {
        $cur        = $this->authContext['countryUserRole'];
        $assignment = ProgramCountryUserRole::create([
            'program_id'           => $this->program->id,
            'country_user_role_id' => $cur->id,
        ]);

        $this->deleteJson(self::BASE_URL . "/{$assignment->id}", [], $this->authContext['headers'])
            ->assertOk();

        // The pivot row is gone
        $this->assertDatabaseMissing('program_country_user_role', ['id' => $assignment->id]);
        // But the program entity still exists
        $this->assertDatabaseHas('program', ['id' => $this->program->id]);
        // And can be re-linked
        $this->assertDatabaseMissing('program_country_user_role', [
            'program_id'           => $this->program->id,
            'country_user_role_id' => $cur->id,
        ]);
    }
}
