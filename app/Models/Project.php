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
    private Indicator $indicator;
    private float $expectedImpact;
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
        Indicator $indicator,
        float $expectedImpact,
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
        $this->indicator = $indicator;
        $this->expectedImpact = $expectedImpact;
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

    public function getIndicator(): Indicator
    {
        return $this->indicator;
    }

    public function getExpectedImpact(): float
    {
        return $this->expectedImpact;
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
    public static function at($name, $description,$country, $agency, $state,  $startDate, $endDate, $actualEndDate, $budget, $budgetSpent,$indicator, $expectedImpact, $manager, $shared): Project
    {

        // name validation: tests expect different messages for NULL vs empty string
        if ($name === null) {
            throw new \RuntimeException('el nombre del proyecto no debe ser null o menor a 3 caracteres');
        }
        if (strlen((string)$name) === 0) {
            throw new \RuntimeException('el nombre del proyecto no debe ser null');
        }
        if (strlen((string)$name) < 3) {
            throw new \RuntimeException('el nombre del proyecto no debe ser null o menor a 3 caracteres');
        }

        if($agency== null ) throw new \RuntimeException('la agencia no debe ser null y debe tener un nombre valido');
        if($country == null) throw new \RuntimeException(Country::$INVALIDNAME);
        if( strlen($state) < 3 ) throw new \RuntimeException('el estado no debe ser null y debe tener un nombre valido');

        // description validation
        if(strlen((string)$description) < 10){
            throw new \RuntimeException('la descripcion del proyecto no debe ser null o menor a 10 caracteres');
        }

        // date validations
        if($endDate == null || $startDate == null   ){
            throw new \RuntimeException('Las fechas no deben ser null');
        }
        if($startDate > $endDate){
            throw new \RuntimeException('la fecha de inicio no puede ser mayor a la fecha de fin');
        }
        if($actualEndDate == null){
            throw new \RuntimeException('Las fechas no deben ser null');
        }

        // budget validations
        if($budget === null || $budget <= 0){
            throw new \RuntimeException('El presupuesto total no debe ser null o menor a 0');
        }
        if($budgetSpent === null || $budgetSpent < 0){
            throw new \RuntimeException('el presupuesto gastado no debe ser null o menor a 0');
        }

        // indicator: must be an Indicator instance with a valid name
        if(!($indicator instanceof Indicator)){
            throw new \RuntimeException('El indicador no debe ser null o menor a 3 caracteres');
        }
        if(strlen($indicator->getName()) < 3){
            throw new \RuntimeException('El indicador no debe ser null o menor a 3 caracteres');
        }

        // expected impact
        if($expectedImpact === null || $expectedImpact < 0){
            throw new \RuntimeException('el expectedImpact no debe ser null o menor a 0');
        }
        if($expectedImpact > 100){
            throw new \RuntimeException('el impacto esperado debe estar entre 0 y 100');
        }

        // manager
        if($manager === null || strlen((string)$manager) < 3) {
            throw new \RuntimeException('El proyecto debe tener un manager asignado');
        }

        // shared
        if($shared === null){
            throw new \RuntimeException('shared no debe ser null o vacio');
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
            $indicator,
            $expectedImpact,
            $manager,
            $shared
        );
    }
}