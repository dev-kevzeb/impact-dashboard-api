<?php

namespace App\Modules\ProjectDonor\Domain;

use App\Modules\Donor\Domain\Donor;
use RuntimeException;

class ProjectDonor
{
    // Constantes de mensajes de error
    public static $ERROR_DONOR_INVALID = 'the donor must be an instance of Donor';
    public static $ERROR_CONTRIBUTION_NOT_NUMERIC = 'the contribution must be a number';
    public static $ERROR_CONTRIBUTION_OUT_OF_RANGE = 'the contribution must be between 0 and 100';


    private Donor $donor;
    private float $contribution;

    public function __construct(Donor $donor, float $contribution)
    {
        $this->donor = $donor;
        $this->contribution = $contribution;
    }

    public static function at($donor, $contribution): ProjectDonor
    {
        if (!($donor instanceof Donor)) {
            throw new RuntimeException(self::$ERROR_DONOR_INVALID);
        }

        if (!is_numeric($contribution)) {
            throw new RuntimeException(self::$ERROR_CONTRIBUTION_NOT_NUMERIC);
        }

        $contributionFloat = (float) $contribution;

        if ($contributionFloat < 0 || $contributionFloat > 100) {
            throw new RuntimeException(self::$ERROR_CONTRIBUTION_OUT_OF_RANGE);
        }

        return new ProjectDonor($donor, $contributionFloat);
    }

    public function getDonor(): Donor
    {
        return $this->donor;
    }

    public function getContribution(): float
    {
        return $this->contribution;
    }

    public function getDonorName(): string
    {
        return $this->donor->getName();
    }
}
