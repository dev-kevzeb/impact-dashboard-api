<?php

namespace Tests\Feature;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Measure\Domain\Measure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasureDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/measures';

    public function test_delete_measure_removes_it_successfully(): void
    {
        $headers = $this->authHeaders('admin');
        $measure = Measure::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . '/' . $measure->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Measure deleted successfully');
        $this->assertDatabaseMissing('measure', ['id' => $measure->id]);
    }

    public function test_delete_measure_fails_when_it_has_indicators(): void
    {
        $headers = $this->authHeaders('admin');

        $measure = Measure::factory()->create();
        Indicator::factory()->create(['measure_id' => $measure->id]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $measure->id, [], $headers);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Cannot delete a measure that has associated indicators.');

        $this->assertDatabaseHas('measure', ['id' => $measure->id]);
    }

    public function test_delete_measure_returns_error_when_not_found(): void
    {
        $headers = $this->authHeaders('admin');

        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $headers);

        $response->assertStatus(400);
    }
}
