<?php

namespace Tests\Feature;

use App\Modules\Program\Domain\Program;
use App\Modules\Contact\Domain\Contact;
use App\Modules\ProgramState\Domain\ProgramState;
use App\Modules\Sdg\Domain\Sdg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/programs';

    // Domain validation errors (400)
    private const ERROR_NAME_MIN_LENGTH = 'The program name must be at least 3 characters long';
    private const ERROR_DESCRIPTION_MIN_LENGTH = 'The program description must be at least 10 characters long';

    // FormRequest validation errors (422)
    private const ERROR_NAME_REQUIRED = 'The program name is required.';
    private const ERROR_NAME_UNIQUE = 'A program with this name already exists.';
    private const ERROR_DESCRIPTION_REQUIRED = 'The program description is required.';
    private const ERROR_CONTACT_REQUIRED = 'Los datos del contacto son obligatorios.';
    private const ERROR_SDG_IDS_REQUIRED = 'You must select at least one SDG.';
    private const ERROR_SDG_IDS_MIN = 'You must select at least one SDG.';

    private ProgramState $inactiveState;
    private Contact $defaultContact;
    private array $validContactData;
    private array $testSdgs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inactiveState = ProgramState::firstOrCreate(['name' => 'Inactive']);
        $this->defaultContact = Contact::factory()->create();

        // Create test SDGs (minimum required data)
        $this->testSdgs = [
            Sdg::create(['image' => 'sdg_images/sdg1.png', 'filename' => 'sdg1.png']),
            Sdg::create(['image' => 'sdg_images/sdg2.png', 'filename' => 'sdg2.png']),
            Sdg::create(['image' => 'sdg_images/sdg3.png', 'filename' => 'sdg3.png']),
        ];

        $this->validContactData = [
            'contact' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'title' => 'Program Manager',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890'
            ]
        ];
    }

    private function getContactData(?Contact $contact = null): array
    {
        if ($contact) {
            return ['contact' => ['id' => $contact->id]];
        }
        return $this->validContactData;
    }

    private function getValidProgramData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Program ' . uniqid(),
            'description' => 'This is a valid test program description with more than ten characters',
            'program_url' => 'https://www.test-program.org',
            'sdg_ids' => [$this->testSdgs[0]->id, $this->testSdgs[1]->id], // At least 1 SDG required
        ], $this->getContactData($this->defaultContact), $overrides);
    }


    // ========== LIST TESTS ==========

    public function test_can_list_programs(): void
    {
        Program::factory()->count(3)->create(['program_state_id' => $this->inactiveState->id]);

        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'programs',
                    'total',
                    'per_page',
                    'current_page',
                    'last_page'
                ]
            ]);
    }

    public function test_list_returns_empty_when_no_programs(): void
    {
        $response = $this->getJson(self::BASE_URL, $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.total', 0);
    }

    public function test_list_supports_pagination(): void
    {
        Program::factory()->count(15)->create(['program_state_id' => $this->inactiveState->id]);

        $response = $this->getJson(self::BASE_URL . '?per_page=5', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonCount(5, 'data.programs');
    }


    // ========== CREATE TESTS ==========

    public function test_can_create_program(): void
    {
        $data = $this->getValidProgramData([
            'name' => 'Education Program 2025',
        ]);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Program created successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'banner_img',
                    'program_url',
                ]
            ]);

        $this->assertDatabaseHas('program', [
            'name' => 'Education Program 2025'
        ]);
    }

    public function test_name_is_required_on_create(): void
    {
        $data = $this->getValidProgramData();
        unset($data['name']);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', self::ERROR_NAME_REQUIRED);
    }

    public function test_description_is_required_on_create(): void
    {
        $data = $this->getValidProgramData();
        unset($data['description']);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description'])
            ->assertJsonPath('errors.description.0', self::ERROR_DESCRIPTION_REQUIRED);
    }

    public function test_contact_is_required_on_create(): void
    {
        $response = $this->postJson(self::BASE_URL, [
            'name' => 'Test Program',
            'description' => 'Test description for program'
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['contact'])
            ->assertJsonPath('errors.contact.0', self::ERROR_CONTACT_REQUIRED);
    }

    public function test_sdg_ids_is_required_on_create(): void
    {
        $data = $this->getValidProgramData();
        unset($data['sdg_ids']);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sdg_ids'])
            ->assertJsonPath('errors.sdg_ids.0', self::ERROR_SDG_IDS_REQUIRED);
    }

    public function test_sdg_ids_must_have_at_least_one_sdg(): void
    {
        $data = $this->getValidProgramData(['sdg_ids' => []]);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sdg_ids'])
            ->assertJsonPath('errors.sdg_ids.0', self::ERROR_SDG_IDS_MIN);
    }

    public function test_sdg_ids_must_be_valid_sdg_ids(): void
    {
        $data = $this->getValidProgramData(['sdg_ids' => [99999]]);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sdg_ids.0']);
    }

    public function test_name_must_be_at_least_3_characters(): void
    {
        $data = $this->getValidProgramData(['name' => 'AB']);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('message', self::ERROR_NAME_MIN_LENGTH);
    }

    public function test_description_must_be_at_least_10_characters(): void
    {
        $data = $this->getValidProgramData(['description' => 'Short']);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('message', self::ERROR_DESCRIPTION_MIN_LENGTH);
    }

    public function test_cannot_create_duplicate_program_name(): void
    {
        Program::factory()->create([
            'name' => 'Unique Program',
            'program_state_id' => $this->inactiveState->id
        ]);
        $data = $this->getValidProgramData(['name' => 'Unique Program']);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', self::ERROR_NAME_UNIQUE);
    }

    public function test_name_is_trimmed_before_saving(): void
    {
        $data = $this->getValidProgramData(['name' => '   Trimmed Program   ']);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated();
        $this->assertDatabaseHas('program', ['name' => 'Trimmed Program']);
    }

    public function test_program_is_created_with_inactive_state_by_default(): void
    {
        $data = $this->getValidProgramData(['name' => 'New Default Program']);

        $response = $this->postJson(self::BASE_URL, $data, $this->authHeaders());

        $response->assertCreated();

        $program = Program::where('name', 'New Default Program')->first();
        $this->assertEquals('Inactive', $program->programState->name);
    }


    // ========== SHOW TESTS ==========

    public function test_can_show_program(): void
    {
        $program = Program::factory()->create(['program_state_id' => $this->inactiveState->id]);

        $response = $this->getJson(self::BASE_URL . "/{$program->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.id', $program->id)
            ->assertJsonPath('data.name', $program->name)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'banner_img',
                    'program_url',
                    'contact' => ['id', 'first_name', 'last_name', 'email'],
                    'program_state' => ['id', 'name'],
                    'projects_count',
                ]
            ]);
    }

    public function test_show_returns_404_when_program_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . "/999", $this->authHeaders());

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Program not Found');
    }


    // ========== UPDATE TESTS ==========

    public function test_can_update_program(): void
    {
        $program = Program::factory()->create(['program_state_id' => $this->inactiveState->id]);
        $activeState = ProgramState::factory()->create(['name' => 'Active']);

        $data = $this->getValidProgramData([
            'name' => 'Updated Program Name',
            'program_state_id' => $activeState->id
        ]);

        $response = $this->putJson(self::BASE_URL . "/{$program->id}", $data, $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'banner_img',
                    'program_url',
                ]
            ]);

        $this->assertDatabaseHas('program', [
            'id' => $program->id,
            'name' => 'Updated Program Name',
            'program_state_id' => $activeState->id
        ]);
    }

    public function test_update_requires_name_and_description(): void
    {
        $program = Program::factory()->create(['program_state_id' => $this->inactiveState->id]);
        $data = $this->getValidProgramData();
        unset($data['name'], $data['description']);

        $response = $this->putJson(self::BASE_URL . "/{$program->id}", $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);
    }

    public function test_update_requires_program_state_id(): void
    {
        $program = Program::factory()->create(['program_state_id' => $this->inactiveState->id]);
        $data = $this->getValidProgramData();
        unset($data['program_state_id']);

        $response = $this->putJson(self::BASE_URL . "/{$program->id}", $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_state_id']);
    }

    public function test_cannot_update_to_duplicate_name(): void
    {
        Program::factory()->create([
            'name' => 'Existing Program',
            'program_state_id' => $this->inactiveState->id
        ]);
        $program2 = Program::factory()->create([
            'name' => 'Another Program',
            'program_state_id' => $this->inactiveState->id
        ]);
        $state = ProgramState::firstOrCreate(['name' => 'Active']);

        $data = $this->getValidProgramData([
            'name' => 'Existing Program',
            'program_state_id' => $state->id
        ]);

        $response = $this->putJson(self::BASE_URL . "/{$program2->id}", $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_update_with_same_name(): void
    {
        $program = Program::factory()->create([
            'name' => 'My Program',
            'program_state_id' => $this->inactiveState->id
        ]);
        $state = ProgramState::firstOrCreate(['name' => 'Active']);

        $data = $this->getValidProgramData([
            'name' => 'My Program',
            'program_state_id' => $state->id
        ]);

        $response = $this->putJson(self::BASE_URL . "/{$program->id}", $data, $this->authHeaders());

        $response->assertOk();
    }


    // ========== SEARCH TESTS ==========

    public function test_can_search_program_by_name(): void
    {
        $program = Program::factory()->create([
            'name' => 'Searchable Program',
            'program_state_id' => $this->inactiveState->id
        ]);

        $response = $this->getJson(self::BASE_URL . '/search?name=Searchable Program', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.name', 'Searchable Program');
    }

    public function test_search_is_case_insensitive(): void
    {
        Program::factory()->create([
            'name' => 'CaseSensitive Program',
            'program_state_id' => $this->inactiveState->id
        ]);

        $response = $this->getJson(self::BASE_URL . '/search?name=casesensitive program', $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.name', 'CaseSensitive Program');
    }

    public function test_search_returns_404_when_not_found(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search?name=Nonexistent Program', $this->authHeaders());

        $response->assertStatus(404);
    }

    public function test_search_requires_name_parameter(): void
    {
        $response = $this->getJson(self::BASE_URL . '/search', $this->authHeaders());

        $response->assertStatus(400);
    }


    // ========== RELATIONSHIPS TESTS ==========

    public function test_program_has_contact_relationship(): void
    {
        $program = Program::factory()->create(['program_state_id' => $this->inactiveState->id]);

        $this->assertInstanceOf(Contact::class, $program->contact);
    }

    public function test_program_has_program_state_relationship(): void
    {
        $program = Program::factory()->create(['program_state_id' => $this->inactiveState->id]);

        $this->assertInstanceOf(ProgramState::class, $program->programState);
    }

    public function test_program_has_projects_count_attribute(): void
    {
        $program = Program::factory()->create(['program_state_id' => $this->inactiveState->id]);

        $response = $this->getJson(self::BASE_URL . "/{$program->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data' => ['projects_count']]);
    }
}
