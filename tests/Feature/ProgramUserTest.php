<?php

namespace Tests\Feature;

use App\Modules\Program\Domain\Program;
use App\Modules\ProgramUser\Domain\ProgramUser;
use App\Modules\CountryKpaUser\Domain\CountryKpaUser;
use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\UserRole\Domain\UserRole;
use App\Modules\User\Domain\User;
use App\Modules\Role\Domain\Role;
use App\Modules\Country\Domain\Country;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\UserState\Domain\UserState;
use App\Modules\Contact\Domain\Contact;
use App\Modules\ProgramState\Domain\ProgramState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramUserTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/program_users';

    private Program $program;
    private CountryKpaUser $countryKpaUser;
    private User $user;
    private Role $role;
    private UserRole $userRole;
    private CountryKpa $countryKpa;

    protected function setUp(): void
    {
        parent::setUp();

        // Create dependencies
        $country = Country::factory()->create();
        $kpa = Kpa::factory()->create();
        $userState = UserState::factory()->create();
        $contact = Contact::factory()->create();
        $programState = ProgramState::factory()->create();

        $this->user = User::factory()->create([
            'user_state_id' => $userState->id,
        ]);

        $this->role = Role::factory()->create(['name' => 'Program Manager']);

        $this->userRole = UserRole::factory()->create([
            'user_id' => $this->user->id,
            'role_id' => $this->role->id,
        ]);

        $this->countryKpa = CountryKpa::create([
            'id_country' => $country->id,
            'id_kpa' => $kpa->id,
        ]);

        $this->countryKpaUser = CountryKpaUser::factory()->create([
            'country_kpa_id' => $this->countryKpa->id,
            'user_role_id' => $this->userRole->id,
        ]);

        $this->program = Program::factory()->create([
            'contact_id' => $contact->id,
            'program_state_id' => $programState->id,
        ]);
    }

    public function test_can_create_program_user_assignment(): void
    {
        $data = [
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'program_id',
                    'country_kpa_user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('program_user', [
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ]);
    }

    public function test_cannot_create_duplicate_program_user_assignment(): void
    {
        ProgramUser::factory()->create([
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ]);

        $data = [
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_kpa_user_id']);
    }

    public function test_program_id_is_required(): void
    {
        $data = [
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_id']);
    }

    public function test_country_kpa_user_id_is_required(): void
    {
        $data = [
            'program_id' => $this->program->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_kpa_user_id']);
    }

    public function test_program_id_must_exist(): void
    {
        $data = [
            'program_id' => 99999,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_id']);
    }

    public function test_country_kpa_user_id_must_exist(): void
    {
        $data = [
            'program_id' => $this->program->id,
            'country_kpa_user_id' => 99999,
        ];

        $response = $this->postJson(self::BASE_URL, $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_kpa_user_id']);
    }

    public function test_can_list_all_assignments(): void
    {
        ProgramUser::factory()->count(3)->create([
            'program_id' => $this->program->id,
        ]);

        $response = $this->getJson(self::BASE_URL);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'assignments' => [
                        '*' => [
                            'id',
                            'program_id',
                            'country_kpa_user_id',
                        ],
                    ],
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    public function test_can_filter_assignments_by_program(): void
    {
        $program2 = Program::factory()->create([
            'contact_id' => Contact::factory()->create()->id,
            'program_state_id' => ProgramState::factory()->create()->id,
        ]);

        ProgramUser::factory()->create([
            'program_id' => $this->program->id,
        ]);

        ProgramUser::factory()->create([
            'program_id' => $program2->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "?program_id={$this->program->id}");

        $response->assertOk()
            ->assertJsonPath('data.total', 1);
    }

    public function test_can_filter_assignments_by_country_kpa_user(): void
    {
        $countryKpaUser2 = CountryKpaUser::factory()->create([
            'country_kpa_id' => $this->countryKpa->id,
        ]);

        ProgramUser::factory()->create([
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ]);

        ProgramUser::factory()->create([
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $countryKpaUser2->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "?country_kpa_user_id={$this->countryKpaUser->id}");

        $response->assertOk()
            ->assertJsonPath('data.total', 1);
    }

    public function test_can_get_assignment_by_id(): void
    {
        $assignment = ProgramUser::factory()->create([
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/{$assignment->id}");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'program_id',
                    'country_kpa_user_id',
                    'program',
                    'countryKpaUser',
                ],
            ]);
    }

    public function test_returns_404_when_assignment_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/99999');

        $response->assertNotFound();
    }

    public function test_can_update_assignment(): void
    {
        $assignment = ProgramUser::factory()->create([
            'program_id' => $this->program->id,
        ]);

        $newCountryKpaUser = CountryKpaUser::factory()->create([
            'country_kpa_id' => $this->countryKpa->id,
        ]);

        $data = [
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $newCountryKpaUser->id,
        ];

        $response = $this->putJson(self::BASE_URL . "/{$assignment->id}", $data);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('program_user', [
            'id' => $assignment->id,
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $newCountryKpaUser->id,
        ]);
    }

    public function test_cannot_update_to_create_duplicate(): void
    {
        $assignment1 = ProgramUser::factory()->create([
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ]);

        $countryKpaUser2 = CountryKpaUser::factory()->create([
            'country_kpa_id' => $this->countryKpa->id,
        ]);

        $assignment2 = ProgramUser::factory()->create([
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $countryKpaUser2->id,
        ]);

        // Try to update assignment2 to have the same combination as assignment1
        $data = [
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ];

        $response = $this->putJson(self::BASE_URL . "/{$assignment2->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_kpa_user_id']);
    }

    public function test_can_delete_assignment(): void
    {
        $assignment = ProgramUser::factory()->create([
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ]);

        $response = $this->deleteJson(self::BASE_URL . "/{$assignment->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('program_user', [
            'id' => $assignment->id,
        ]);
    }

    public function test_delete_returns_404_when_assignment_not_found(): void
    {
        $response = $this->deleteJson(self::BASE_URL . '/99999');

        $response->assertNotFound();
    }

    public function test_assignment_includes_relationships(): void
    {
        $assignment = ProgramUser::factory()->create([
            'program_id' => $this->program->id,
            'country_kpa_user_id' => $this->countryKpaUser->id,
        ]);

        $response = $this->getJson(self::BASE_URL . "/{$assignment->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'program_id',
                    'country_kpa_user_id',
                    'program',
                    'countryKpaUser',
                ],
            ]);
    }

    public function test_pagination_works_correctly(): void
    {
        ProgramUser::factory()->count(15)->create([
            'program_id' => $this->program->id,
        ]);

        $response = $this->getJson(self::BASE_URL . '?per_page=10');

        $response->assertOk()
            ->assertJsonPath('data.total', 15)
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonPath('data.last_page', 2)
            ->assertJsonCount(10, 'data.assignments');
    }
}
