<?php

namespace Tests\Unit\Statistics;

use App\Modules\Statistics\Domain\ContributionShare;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ContributionShareTest extends TestCase
{
    public function test_two_donors_splitting_a_partially_implemented_measure_sum_to_100(): void
    {
        $result = new ContributionShare([
            ['id' => 1, 'name' => 'Australia', 'contribution' => 5],
            ['id' => 2, 'name' => 'European Union', 'contribution' => 5],
        ], 10);

        $this->assertEquals([
            ['id' => 1, 'name' => 'Australia', 'contribution' => 50.0],
            ['id' => 2, 'name' => 'European Union', 'contribution' => 50.0],
        ], $result->value());
    }

    public function test_shares_sum_to_100_regardless_of_implementation_level(): void
    {
        foreach ([1, 25, 55.4, 99] as $implementation) {
            $result = new ContributionShare([
                ['id' => 1, 'name' => 'Australia', 'contribution' => $implementation * 0.6],
                ['id' => 2, 'name' => 'European Union', 'contribution' => $implementation * 0.4],
            ], $implementation);

            $sum = array_sum(array_column($result->value(), 'contribution'));
            $this->assertEqualsWithDelta(100.0, $sum, 0.5);
        }
    }

    public function test_reproduces_pdf_worked_example(): void
    {
        $result = new ContributionShare([
            ['id' => 'AU', 'name' => 'Australia', 'contribution' => 24.7],
            ['id' => 'EU', 'name' => 'European Union', 'contribution' => 29.2],
            ['id' => 'NZ', 'name' => 'New Zealand', 'contribution' => 1.6],
        ], 55.4);

        $this->assertEquals([
            ['id' => 'AU', 'name' => 'Australia', 'contribution' => 44.58],
            ['id' => 'EU', 'name' => 'European Union', 'contribution' => 52.71],
            ['id' => 'NZ', 'name' => 'New Zealand', 'contribution' => 2.89],
        ], $result->value());
    }

    public function test_multiple_rows_for_the_same_contributor_are_summed_before_normalizing(): void
    {
        $result = new ContributionShare([
            ['id' => 1, 'name' => 'Australia', 'contribution' => 10],
            ['id' => 1, 'name' => 'Australia', 'contribution' => 15],
        ], 25);

        $this->assertEquals([
            ['id' => 1, 'name' => 'Australia', 'contribution' => 100.0],
        ], $result->value());
    }

    public function test_zero_implementation_returns_zero_contribution_without_division_by_zero(): void
    {
        $result = new ContributionShare([
            ['id' => 1, 'name' => 'Australia', 'contribution' => 0],
        ], 0);

        $this->assertEquals([
            ['id' => 1, 'name' => 'Australia', 'contribution' => 0.0],
        ], $result->value());
    }

    public function test_empty_contributions_returns_empty_array(): void
    {
        $result = new ContributionShare([], 50);

        $this->assertEquals([], $result->value());
    }

    public function test_negative_implementation_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ContributionShare::ERROR_INVALID_IMPLEMENTATION);

        new ContributionShare([['id' => 1, 'name' => 'Australia', 'contribution' => 5]], -1);
    }
}
