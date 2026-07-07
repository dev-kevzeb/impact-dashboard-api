<?php

namespace Tests\Unit\Statistics;

use App\Modules\Statistics\Domain\ImplementationAggregate;
use PHPUnit\Framework\TestCase;

class ImplementationAggregateTest extends TestCase
{
    private function node(float $implementation, int $total, float $resource = 0, array $agencies = [], array $donors = [], array $beneficiaries = []): array
    {
        return [
            'implementation' => $implementation,
            'total' => $total,
            'resource' => $resource,
            'beneficiaries' => $beneficiaries,
            'agencies' => $agencies,
            'donors' => $donors,
        ];
    }

    public function test_implementation_is_averaged_weighted_by_measure_count(): void
    {
        $result = new ImplementationAggregate([
            $this->node(100, 1),
            $this->node(50, 1),
            $this->node(0, 1),
        ]);

        $this->assertEquals(50.0, $result->value()['implementation']);
        $this->assertEquals(3, $result->value()['total']);
    }

    public function test_children_are_weighted_by_their_own_measure_count(): void
    {
        // A strategic output covering 3 measures at 90% should count 3x as much
        // as one covering 1 measure at 0%.
        $result = new ImplementationAggregate([
            $this->node(90, 3),
            $this->node(0, 1),
        ]);

        $this->assertEquals(67.5, $result->value()['implementation']); // (90*3 + 0*1) / 4
        $this->assertEquals(4, $result->value()['total']);
    }

    public function test_donor_share_is_diluted_by_measures_the_donor_does_not_contribute_to(): void
    {
        // Donor funds 100% of measure 1 (Sd=100) out of 3 equally-weighted measures.
        // Ad = (1/3) * (100 + 0 + 0) = 33.33, NOT 100.
        $result = new ImplementationAggregate([
            $this->node(80, 1, donors: [['id' => 1, 'name' => 'Australia', 'contribution' => 100.0]]),
            $this->node(40, 1, donors: []),
            $this->node(60, 1, donors: []),
        ]);

        $this->assertEquals(33.33, $result->value()['donors'][0]['contribution']);
    }

    public function test_donors_and_agencies_are_aggregated_independently(): void
    {
        // Both donors and agencies fully cover every measure -> each group must
        // still sum to 100%, independent of the other group existing.
        $result = new ImplementationAggregate([
            $this->node(
                50,
                1,
                agencies: [['id' => 'a1', 'name' => 'Agency 1', 'contribution' => 100.0]],
                donors: [['id' => 'd1', 'name' => 'Donor 1', 'contribution' => 100.0]]
            ),
        ]);

        $this->assertEquals(100.0, $result->value()['agencies'][0]['contribution']);
        $this->assertEquals(100.0, $result->value()['donors'][0]['contribution']);
    }

    public function test_resource_is_a_plain_sum_not_weighted(): void
    {
        $result = new ImplementationAggregate([
            $this->node(0, 1, resource: 1000),
            $this->node(0, 3, resource: 500),
        ]);

        $this->assertEquals(1500.0, $result->value()['resource']);
    }

    public function test_nodes_with_zero_measures_are_skipped(): void
    {
        $result = new ImplementationAggregate([
            $this->node(100, 0),
            $this->node(50, 1),
        ]);

        $this->assertEquals(50.0, $result->value()['implementation']);
        $this->assertEquals(1, $result->value()['total']);
    }

    public function test_empty_nodes_return_zeroed_result(): void
    {
        $result = new ImplementationAggregate([]);

        $this->assertEquals([
            'implementation' => 0.0,
            'total' => 0,
            'resource' => 0.0,
            'beneficiaries' => [],
            'agencies' => [],
            'donors' => [],
        ], $result->value());
    }

    public function test_same_contributor_across_nodes_is_combined(): void
    {
        $result = new ImplementationAggregate([
            $this->node(100, 1, donors: [['id' => 1, 'name' => 'Australia', 'contribution' => 100.0]]),
            $this->node(100, 1, donors: [['id' => 1, 'name' => 'Australia', 'contribution' => 100.0]]),
        ]);

        $this->assertEquals(100.0, $result->value()['donors'][0]['contribution']);
    }
}
