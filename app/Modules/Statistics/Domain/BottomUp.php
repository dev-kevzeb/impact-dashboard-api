<?php

namespace App\Modules\Statistics\Domain;
use RuntimeException;

class BottomUp
{
    public const ERROR_PROJECTS_EMPTY = 'Projects collection must not be empty';
    public const ERROR_INVALID_RATE = 'Implementation rate must be between 0 and 1';
    public const ERROR_INVALID_WEIGHT = 'Weight must be between 0 and 1';

    private float $result;

    /**
     * @param array $projects  [ ['implementation' => float (0-1), 'weight' => float (0-1)], ... ]
     */
    public function __construct(array $projects)
    {
        if (empty($projects)) throw new RuntimeException(self::ERROR_PROJECTS_EMPTY);

        $total = 0;

        foreach ($projects as $project) {
            $rate   = $project['implementation'] ?? null;
            $weight = $project['weight'] ?? null;

            if (!is_numeric($rate) || $rate < 0 || $rate > 1) throw new RuntimeException(self::ERROR_INVALID_RATE);
            if (!is_numeric($weight) || $weight < 0 || $weight > 1) throw new RuntimeException(self::ERROR_INVALID_WEIGHT);

            $total += $rate * $weight;
        }

        $this->result = round($total * 100, 2);
    }

    public function value(): float
    {
        return $this->result;
    }
}