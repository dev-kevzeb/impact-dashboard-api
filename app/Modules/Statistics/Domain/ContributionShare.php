<?php

namespace App\Modules\Statistics\Domain;

use RuntimeException;

class ContributionShare
{
    public const ERROR_INVALID_IMPLEMENTATION = 'Implementation rate must be zero or greater';

    private array $result;

    
    public function __construct(array $rawContributions, float $implementation)
    {
        if ($implementation < 0) throw new RuntimeException(self::ERROR_INVALID_IMPLEMENTATION);

        $grouped = [];
        foreach ($rawContributions as $row) {
            $id = $row['id'];
            $grouped[$id] ??= ['id' => $id, 'name' => $row['name'], 'sum' => 0.0];
            $grouped[$id]['sum'] += (float) $row['contribution'];
        }

        $this->result = array_values(array_map(static function (array $entry) use ($implementation) {
            return [
                'id' => $entry['id'],
                'name' => $entry['name'],
                'contribution' => $implementation > 0 ? round(($entry['sum'] / $implementation) * 100, 2) : 0.0,
            ];
        }, $grouped));
    }

    public function value(): array
    {
        return $this->result;
    }
}
