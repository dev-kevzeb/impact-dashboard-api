<?php

namespace App\Modules\Statistics\Domain;

use RuntimeException;

class TopDown
{
    public static string $ERROR_TARGET_ZERO = 'Target must be greater than zero';
    public static string $ERROR_NEGATIVE_VALUES = 'Values cannot be negative';

    private float $result;

    private function __construct(float $result)
    {
        $this->result = $result;
    }

    /**
     * Factory method for Top-Down calculation
     *
     * @param float $actualValue  αₓ  (current observed value)
     * @param float $target       Tₓ  (target value)
     */
    public static function calculate(float $actualValue, float $target): TopDown
    {
        if ($target <= 0) throw new RuntimeException(self::$ERROR_TARGET_ZERO);
        if ($actualValue < 0) throw new RuntimeException(self::$ERROR_NEGATIVE_VALUES);
        
        $implementation = ($actualValue / $target) * 100;
        return new TopDown(round($implementation, 2));
    }

    public function value(): float
    {
        return $this->result;
    }
}
