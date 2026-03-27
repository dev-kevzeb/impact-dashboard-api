<?php

namespace Tests\Feature;

use App\Modules\Measure\Domain\Measure;
use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategicOutputDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/strategic-outputs';

    public function test_delete_strategic_output_removes_it_successfully(): void
    {
        $headers = $this->authHeaders('admin');
        $strategicOutput = StrategicOutput::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . '/' . $strategicOutput->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Strategic output deleted successfully');
        $this->assertDatabaseMissing('strategic_output', ['id' => $strategicOutput->id]);
    }

    public function test_delete_strategic_output_fails_when_it_has_measures(): void
    {
        $headers = $this->authHeaders('admin');

        $strategicOutput = StrategicOutput::factory()->create();
        Measure::factory()->create(['strategic_output_id' => $strategicOutput->id]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $strategicOutput->id, [], $headers);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Cannot delete a strategic output that has associated measures.');

        $this->assertDatabaseHas('strategic_output', ['id' => $strategicOutput->id]);
    }

    public function test_delete_strategic_output_returns_error_when_not_found(): void
    {
        $headers = $this->authHeaders('admin');

        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $headers);

        $response->assertStatus(400);
    }
}
