<?php

namespace Tests\Unit\Statistics;

use App\Modules\Statistics\Domain\TopDown;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TopDownTest extends TestCase
{
    public function test_basic_calculation(): void
    {
        // (60 / 80) × 100 = 75
        $result = TopDown::calculate(60.0, 80.0);

        $this->assertEquals(75.0, $result->value());
    }

    public function test_full_achievement(): void
    {
        // (100 / 100) × 100 = 100
        $result = TopDown::calculate(100.0, 100.0);

        $this->assertEquals(100.0, $result->value());
    }

    public function test_zero_actual_value(): void
    {
        // (0 / 100) × 100 = 0
        $result = TopDown::calculate(0.0, 100.0);

        $this->assertEquals(0.0, $result->value());
    }

    public function test_over_achievement_allowed(): void
    {
        // (120 / 100) × 100 = 120
        $result = TopDown::calculate(120.0, 100.0);

        $this->assertEquals(120.0, $result->value());
    }

    public function test_target_zero_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(TopDown::$ERROR_TARGET_ZERO);

        TopDown::calculate(50.0, 0.0);
    }

    public function test_negative_target_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(TopDown::$ERROR_TARGET_ZERO);

        TopDown::calculate(50.0, -10.0);
    }

    public function test_negative_actual_value_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(TopDown::$ERROR_NEGATIVE_VALUES);

        TopDown::calculate(-5.0, 100.0);
    }

    public function test_result_is_rounded_to_two_decimals(): void
    {
        // (1 / 3) × 100 = 33.333... → rounded to 33.33
        $result = TopDown::calculate(1.0, 3.0);

        $this->assertEquals(33.33, $result->value());
    }

    public function test_decimal_values(): void
    {
        // (2.5 / 4.0) × 100 = 62.5
        $result = TopDown::calculate(2.5, 4.0);

        $this->assertEquals(62.5, $result->value());
    }
}
