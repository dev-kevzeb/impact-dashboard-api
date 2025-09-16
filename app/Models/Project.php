<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    private  $name;
    private  $description;
    private  $programId;
    private  $countryId;
    private  $agencyId;
    private  $state;
    private  $startDate;
    private  $endDate;
    private  $actualEndDate;
    private  $budget;
    private  $budgetSpent;
    private  $indicators;
    private  $expectedImpact;
    private  $documents;
    private  $managerId;
    private  $shared;

    public function __construct(
        string $name,
        string $description,
        int $programId,
        int $countryId,
        int $agencyId,
        string $state,
        ?string $startDate,
        ?string $endDate,
        ?string $actualEndDate,
        float $budget,
        float $budgetSpent,
        array $indicators,
        string $expectedImpact,
        array $documents,
        int $managerId,
        bool $shared
    ) {
        $this->name           = $name;
        $this->description    = $description;
        $this->programId      = $programId;
        $this->countryId      = $countryId;
        $this->agencyId       = $agencyId;
        $this->state          = $state;
        $this->startDate      = $startDate;
        $this->endDate        = $endDate;
        $this->actualEndDate  = $actualEndDate;
        $this->budget         = $budget;
        $this->budgetSpent    = $budgetSpent;
        $this->indicators     = $indicators;
        $this->expectedImpact = $expectedImpact;
        $this->documents      = $documents;
        $this->managerId      = $managerId;
        $this->shared         = $shared;
    }
    // getters
    public function getExternalId(): string{
        return $this->externalId;
    }
    public function getName(): string{
        return $this->name;
    }
    public function getDescription(): string{
        return $this->description;
    }
    public function getProgramId(): int{
        return $this->programId;
    }
    public function getCountryId(): int{
        return $this->countryId;
    }
    public function getAgencyId(): int{
        return $this->agencyId;
    }
    public function getState(): string{
        return $this->state;
    }
}