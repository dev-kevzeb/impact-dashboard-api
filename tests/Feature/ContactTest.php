<?php

namespace Tests\Feature;

use App\Modules\Contact\Domain\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/contacts';

    public function test_can_list_contacts()
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson(self::BASE_URL, $this->authHeaders('project-manager'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'contacts' => [
                        '*' => ['id', 'first_name', 'last_name', 'title', 'email', 'phone']
                    ],
                    'total'
                ]
            ])
            ->assertJsonPath('data.total', 3);
    }

    public function test_can_show_contact()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson(self::BASE_URL . "/{$contact->id}", $this->authHeaders('project-manager'));

        $response->assertOk()
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonPath('data.email', $contact->email);
    }

    public function test_show_returns_404_for_nonexistent_contact()
    {
        $response = $this->getJson(self::BASE_URL . "/9999", $this->authHeaders('project-manager'));

        $response->assertStatus(404);
    }

    public function test_can_create_contact()
    {
        $payload = [
            'first_name' => 'Juan',
            'last_name'  => 'Perez',
            'title'      => 'Gerente General',
            'email'      => 'juan.perez@example.com',
            'phone'      => '+591 70000001'
        ];

        $response = $this->postJson(self::BASE_URL, $payload, $this->authHeaders('project-manager'));

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', 'Juan')
            ->assertJsonPath('data.email', 'juan.perez@example.com');

        $this->assertDatabaseHas('contact', [
            'email' => 'juan.perez@example.com',
        ]);
    }

    public function test_create_contact_validation_errors()
    {
        $payload = [
            'first_name' => '',
            'last_name' => '',
            'title' => '',
            'email' => 'not-an-email',
            'phone' => 'abc'
        ];

        $response = $this->postJson(self::BASE_URL, $payload, $this->authHeaders('project-manager'));

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'title',
                'email',
                'phone'
            ]);
    }

    public function test_can_update_contact()
    {
        $contact = Contact::factory()->create();

        $payload = [
            'first_name' => 'Carlos',
            'last_name' => 'Ramirez',
            'title' => 'Director Comercial',
            'email' => 'carlos.ramirez@example.com',
            'phone' => '+591 70000022'
        ];

        $response = $this->putJson(self::BASE_URL . "/{$contact->id}", $payload, $this->authHeaders('project-manager'));

        $response->assertOk()
            ->assertJsonPath('data.first_name', 'Carlos')
            ->assertJsonPath('data.email', 'carlos.ramirez@example.com');

        $this->assertDatabaseHas('contact', [
            'id' => $contact->id,
            'email' => 'carlos.ramirez@example.com',
        ]);
    }

    public function test_update_validation_errors()
    {
        $contact = Contact::factory()->create();

        $payload = [
            'first_name' => '',
            'last_name' => '',
            'title'=> '',
            'email'=> 'bademail',
            'phone'=> ''
        ];

        $response = $this->putJson(self::BASE_URL . "/{$contact->id}", $payload, $this->authHeaders('project-manager'));

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name', 'last_name', 'title', 'email', 'phone'
            ]);
    }

    public function test_update_returns_404_when_contact_not_found()
    {
        $payload = [
            'first_name' => 'Luis',
            'last_name'  => 'Mendoza',
            'title'      => 'CEO',
            'email'      => 'luis@example.com',
            'phone'      => '+591 72100000'
        ];

        $response = $this->putJson(self::BASE_URL . "/9999", $payload, $this->authHeaders('project-manager'));

        $response->assertStatus(404);
    }

    public function test_can_search_contact_by_email()
    {
        $contact = Contact::factory()->create([
            'email' => 'searchme@example.com'
        ]);

        $response = $this->getJson(self::BASE_URL . "/search?email=searchme@example.com", $this->authHeaders('project-manager'));

        $response->assertOk()
            ->assertJsonPath('data.email', 'searchme@example.com');
    }

    public function test_search_returns_404_if_not_found()
    {
        $response = $this->getJson(self::BASE_URL . "/search?email=unknown@example.com", $this->authHeaders('project-manager'));

        $response->assertStatus(404);
    }

    public function test_search_validation_error()
    {
        $response = $this->getJson(self::BASE_URL . "/search?email=not-valid", $this->authHeaders('project-manager'));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
