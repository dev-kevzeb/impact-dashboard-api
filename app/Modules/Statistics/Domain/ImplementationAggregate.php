<?php

namespace App\Modules\Statistics\Domain;

class ImplementationAggregate
{
    private float $implementation;
    private int $total;
    private float $resource;
    private array $beneficiaries;
    private array $agencies;
    private array $donors;

    public function __construct(iterable $nodes)
    {
        $weightedImplementation = 0.0;
        $totalMeasures = 0;
        $resource = 0.0;
        $beneficiaries = [];
        $agencyTotals = [];
        $donorTotals = [];

        foreach ($nodes as $node) {
            $weight = (int) ($node['total'] ?? 0);
            if ($weight <= 0) continue;

            $weightedImplementation += (float) $node['implementation'] * $weight;
            $totalMeasures += $weight;
            $resource += (float) $node['resource'];

            foreach ($node['beneficiaries'] as $beneficiary) {
                $beneficiaries[data_get($beneficiary, 'id')] = $beneficiary;
            }

            self::accumulate($agencyTotals, $node['agencies'], $weight);
            self::accumulate($donorTotals, $node['donors'], $weight);
        }

        $this->implementation = $totalMeasures > 0 ? round($weightedImplementation / $totalMeasures, 2) : 0.0;
        $this->total = $totalMeasures;
        $this->resource = $resource;
        $this->beneficiaries = array_values($beneficiaries);
        $this->agencies = self::finalize($agencyTotals, $totalMeasures);
        $this->donors = self::finalize($donorTotals, $totalMeasures);
    }

    private static function accumulate(array &$totals, iterable $contributors, int $weight): void
    {
        foreach ($contributors as $contributor) {
            $id = $contributor['id'];
            $totals[$id] ??= ['id' => $id, 'name' => $contributor['name'], 'weighted' => 0.0];
            $totals[$id]['weighted'] += (float) $contributor['contribution'] * $weight;
        }
    }

    private static function finalize(array $totals, int $totalMeasures): array
    {
        return array_values(array_map(static function (array $entry) use ($totalMeasures) {
            return [
                'id' => $entry['id'],
                'name' => $entry['name'],
                'contribution' => $totalMeasures > 0 ? round($entry['weighted'] / $totalMeasures, 2) : 0.0,
            ];
        }, $totals));
    }

    public function value(): array
    {
        return [
            'implementation' => $this->implementation,
            'total' => $this->total,
            'resource' => $this->resource,
            'beneficiaries' => $this->beneficiaries,
            'agencies' => $this->agencies,
            'donors' => $this->donors,
        ];
    }
}
