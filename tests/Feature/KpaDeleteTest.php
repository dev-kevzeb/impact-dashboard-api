<?php

namespace Tests\Feature;

use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\Kpa\Domain\Kpa;
use App\Modules\Country\Domain\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpaDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/kpas';

    public function test_delete_kpa_removes_it_successfully(): void
    {
        $headers = $this->authHeaders('admin');
        $kpa = Kpa::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . '/' . $kpa->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'KPA deleted successfully');
        $this->assertDatabaseMissing('kpa', ['id' => $kpa->id]);
    }

    public function test_delete_kpa_fails_when_assigned_to_countries(): void
    {
        $headers = $this->authHeaders('admin');

        $kpa = Kpa::factory()->create();
        $country = Country::factory()->create();
        CountryKpa::factory()->create(['id_kpa' => $kpa->id, 'id_country' => $country->id]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $kpa->id, [], $headers);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Cannot delete a KPA that is assigned to countries.');

        $this->assertDatabaseHas('kpa', ['id' => $kpa->id]);
    }

    public function test_delete_kpa_returns_error_when_not_found(): void
    {
        $headers = $this->authHeaders('admin');

        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $headers);

        $response->assertStatus(400);
    }
}
