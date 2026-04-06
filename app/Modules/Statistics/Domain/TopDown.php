<?php

namespace App\Modules\Statistics\Domain;

use RuntimeException;

class TopDown
{
    public const ERROR_TARGET_ZERO = 'Target must be greater than zero';
    public const ERROR_NEGATIVE_VALUES = 'Values cannot be negative';

    private float $result;

    /**
     * @param float $actualValue αₓ (current observed value)
     * @param float $target Tₓ (target value)
     */
    public function __construct(float $actualValue, float $target)
    {
        if ($target <= 0) throw new RuntimeException(self::ERROR_TARGET_ZERO);
        if ($actualValue < 0) throw new RuntimeException(self::ERROR_NEGATIVE_VALUES);

        $this->result = round(($actualValue / $target) * 100, 2);
    }

    public function value(): float
    {
        return $this->result;
    }
}
