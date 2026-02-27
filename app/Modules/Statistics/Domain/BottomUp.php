<?php

namespace App\Modules\Statistics\Domain;
use RuntimeException;

class BottomUp
{
    public static string $ERROR_PROJECTS_EMPTY = 'Projects collection must not be empty';
    public static string $ERROR_INVALID_RATE = 'Implementation rate must be between 0 and 1';
    public static string $ERROR_INVALID_WEIGHT = 'Weight must be between 0 and 1';
    public static string $ERROR_WEIGHTS_SUM = 'The sum of weights must be equal to 1';

    private float $result;

    private function __construct(float $result)
    {
        $this->result = $result;
    }

    /**
     * Factory method
     *
     * @param array $projects
     * [
     *   [
     *     'implementation' => float (0-1),
     *     'weight' => float (0-1)
     *   ]
     * ]
     */
    public static function calculate(array $projects): BottomUp
    {
        if (empty($projects)) throw new RuntimeException(self::$ERROR_PROJECTS_EMPTY);

        $total = 0;
        $weightSum = 0;

        foreach ($projects as $project) {

            $rate = $project['implementation'] ?? null;
            $weight = $project['weight'] ?? null;

            if (!is_numeric($rate) || $rate < 0 || $rate > 1) throw new RuntimeException(self::$ERROR_INVALID_RATE);
            if (!is_numeric($weight) || $weight < 0 || $weight > 1) throw new RuntimeException(self::$ERROR_INVALID_WEIGHT);
            
            $total += $rate * $weight;
            $weightSum += $weight;
        }

        if (abs($weightSum - 1) > 0.0001) throw new RuntimeException(self::$ERROR_WEIGHTS_SUM);

        return new BottomUp(round($total * 100, 2));
    }

    public function value(): float
    {
        return $this->result;
    }
}