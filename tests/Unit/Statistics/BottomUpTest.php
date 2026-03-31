<?php

namespace Tests\Unit\Statistics;

use App\Modules\Statistics\Domain\BottomUp;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BottomUpTest extends TestCase
{
    public function test_basic_calculation(): void
    {
        // Σ(θ_i × W_i) × 100 = (1×0.2 + 0.5×0.4 + 0.25×0.3) × 100 = (0.2 + 0.2 + 0.075) × 100 = 47.5
        $result = BottomUp::calculate([
            ['implementation' => 1.0,  'weight' => 0.2],
            ['implementation' => 0.5,  'weight' => 0.4],
            ['implementation' => 0.25, 'weight' => 0.3],
        ]);

        $this->assertEquals(47.5, $result->value());
    }

    public function test_full_weight_and_full_progress(): void
    {
        // (0.8 × 1.0) × 100 = 80
        $result = BottomUp::calculate([
            ['implementation' => 0.8, 'weight' => 1.0],
        ]);

        $this->assertEquals(80.0, $result->value());
    }

    public function test_weights_sum_less_than_one_is_valid(): void
    {
        // sum(W) = 0.3, which is < 1 — must NOT throw
        $result = BottomUp::calculate([
            ['implementation' => 0.5, 'weight' => 0.3],
        ]);

        $this->assertEquals(15.0, $result->value());
    }

    public function test_zero_progress_returns_zero(): void
    {
        $result = BottomUp::calculate([
            ['implementation' => 0.0, 'weight' => 0.5],
            ['implementation' => 0.0, 'weight' => 0.5],
        ]);

        $this->assertEquals(0.0, $result->value());
    }

    public function test_empty_projects_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(BottomUp::$ERROR_PROJECTS_EMPTY);

        BottomUp::calculate([]);
    }

    public function test_negative_rate_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(BottomUp::$ERROR_INVALID_RATE);

        BottomUp::calculate([
            ['implementation' => -0.1, 'weight' => 0.5],
        ]);
    }

    public function test_rate_above_one_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(BottomUp::$ERROR_INVALID_RATE);

        BottomUp::calculate([
            ['implementation' => 1.01, 'weight' => 0.5],
        ]);
    }

    public function test_negative_weight_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(BottomUp::$ERROR_INVALID_WEIGHT);

        BottomUp::calculate([
            ['implementation' => 0.5, 'weight' => -0.1],
        ]);
    }

    public function test_weight_above_one_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(BottomUp::$ERROR_INVALID_WEIGHT);

        BottomUp::calculate([
            ['implementation' => 0.5, 'weight' => 1.01],
        ]);
    }

    public function test_result_is_rounded_to_two_decimals(): void
    {
        // (0.333 × 0.333) × 100 = 11.0889 → rounded to 11.09
        $result = BottomUp::calculate([
            ['implementation' => 0.333, 'weight' => 0.333],
        ]);

        $this->assertEquals(11.09, $result->value());
    }
}
