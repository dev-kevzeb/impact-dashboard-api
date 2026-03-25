<?php

namespace Tests\Feature;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\IndicatorType\Domain\IndicatorType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicatorTypeDeleteTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/indicator-types';

    public function test_delete_indicator_type_removes_it_successfully(): void
    {
        $headers = $this->authHeaders('admin');
        $type = IndicatorType::factory()->create();

        $response = $this->deleteJson(self::BASE_URL . '/' . $type->id, [], $headers);

        $response->assertOk()->assertJsonPath('message', 'Indicator Type deleted successfully');
        $this->assertDatabaseMissing('indicator_type', ['id' => $type->id]);
    }

    public function test_delete_indicator_type_fails_when_assigned_to_indicator(): void
    {
        $headers = $this->authHeaders('admin');

        $type = IndicatorType::factory()->create();
        Indicator::factory()->create(['type_id' => $type->id]);

        $response = $this->deleteJson(self::BASE_URL . '/' . $type->id, [], $headers);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Cannot delete an indicator type that is assigned to one or more indicators.');

        $this->assertDatabaseHas('indicator_type', ['id' => $type->id]);
    }

    public function test_delete_indicator_type_returns_error_when_not_found(): void
    {
        $headers = $this->authHeaders('admin');

        $response = $this->deleteJson(self::BASE_URL . '/999999', [], $headers);

        $response->assertStatus(400);
    }
}
