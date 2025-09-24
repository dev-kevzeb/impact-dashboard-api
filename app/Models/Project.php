<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeImmutable;

use App\Models\Agency;
use App\Models\Country;

class Project extends Model
{
    private string $name;
    private string $description;
    private Country $country;
    private Agency $agency;
    private string $state;
    private ?DateTimeImmutable $startDate;
    private ?DateTimeImmutable $endDate;
    private ?DateTimeImmutable $actualEndDate;
    private float $budget;
    private float $budgetSpent;
    private string $indicators;
    private float $expectedImpact;
    private string $documents;
    private string $manager;
    private bool $shared;
    static $INVALIDNAME = 'el nombre del proyecto no debe ser null o menor a 3 caracteres';


    public function __construct(
        string $name,
        string $description,
        Country $country,
        Agency $agency,
        string $state,
        ?DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate,
        ?DateTimeImmutable $actualEndDate,
        float $budget,
        float $budgetSpent,
        string $indicators,
        float $expectedImpact,
        string $documents,
        string $manager,
        bool $shared
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->country = $country;
        $this->agency = $agency;
        $this->state = $state;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->actualEndDate = $actualEndDate;
        $this->budget = $budget;
        $this->budgetSpent = $budgetSpent;
        $this->indicators = $indicators;
        $this->expectedImpact = $expectedImpact;
        $this->documents = $documents;
        $this->manager = $manager;
        $this->shared = $shared;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getAgency(): Agency
    {
        return $this->agency;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getActualEndDate(): ?DateTimeImmutable
    {
        return $this->actualEndDate;
    }

    public function getBudget(): float
    {
        return $this->budget;
    }

    public function getBudgetSpent(): float
    {
        return $this->budgetSpent;
    }

    public function getIndicators(): string
    {
        return $this->indicators;
    }

    public function getExpectedImpact(): float
    {
        return $this->expectedImpact;
    }

    public function getDocuments(): string
    {
        return $this->documents;
    }

    public function getManager(): string
    {
        return $this->manager;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }
  
    // 
    public static function at($name, $description,$country, $agency, $state,  $startDate, $endDate, $actualEndDate, $budget, $budgetSpent,$indicators, $expectedImpact, $documents, $manager, $shared): Project
    {

        if(strlen($name ) < 3){
            throw new \RuntimeException('el nombre del proyecto no debe ser null o menor a 3 caracteres');
        }   
        if($agency== null ) throw new \RuntimeException('la agencia no debe ser null y debe tener un nombre valido');
        if($country == null) throw new \RuntimeException(Country::$INVALIDNAME);
        if( strlen($state) < 3 ) throw new \RuntimeException('el estado no debe ser null y debe tener un nombre valido');
        if(strlen($indicators) < 10){
            throw new \RuntimeException('los indicadores del proyecto no deben ser null o menores a 10 caracteres');
        }
        if( strlen($manager) < 3 ) {
            throw new \RuntimeException('el manager no debe ser null y debe tener un nombre valido');
        }

        if(strlen($description) < 10){
            throw new \RuntimeException('la descripcion del proyecto no debe ser null o menor a 10 caracteres');
        }
        if($endDate == NULL){
            throw new \RuntimeException('las fechas de inicio y fin no deben ser null');
        }
        if($startDate > $endDate){
            throw new \RuntimeException('la fecha de inicio no puede ser mayor a la fecha de fin');
        }
        if($actualEndDate == NULL){
            throw new \RuntimeException('la fecha de fin o conclusion real no debe ser null');
        }
        if($budget <= 0){
            throw new \RuntimeException('el presupuesto debe ser mayor a 0');
        }
        if($budgetSpent < 0){
            throw new \RuntimeException('el presupuesto gastado no debe ser negativo');
        }
        if($expectedImpact < 0 || $expectedImpact > 100){
            throw new \RuntimeException('el impacto esperado debe estar entre 0 y 100');
        }
        return new Project(
            $name,
            $description,
            $country,
            $agency,
            $state,
            $startDate,
            $endDate,
            $actualEndDate,
            $budget,
            $budgetSpent,
            $indicators,
            $expectedImpact,
            $documents,
            $manager,
            $shared
        );
    }
}