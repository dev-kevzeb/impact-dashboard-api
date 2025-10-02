<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeImmutable;

use App\Models\Agency;
use App\Models\Country;
use DateTime;

class Project extends Model
{
    private string $name;
    private string $description;
    private Country $country;
    private Agency $agency;
    private ProjectState $projectState;
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
        ProjectState $projectState,
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
        $this->projectState = $projectState;
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

    public function getState(): ProjectState
    {
        return $this->projectState;
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

    public static function validateDateString(?string $date, string $format = 'Y-m-d'): bool
    {
        if ($date === null) {
            return false;
        }
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function isNumeric($value): bool
    {
        return is_numeric($value);
    }

    public static function isString($value): bool
    {
        return is_string($value);
    }

    public static function at(
        $name,
        $description,
        $country,
        $agency,
        $projectState,
        $startDate,
        $endDate,
        $actualEndDate,
        $budget,
        $budgetSpent,
        $indicator,
        $expectedImpact,
        $manager,
        $shared
    ): Project {
        if (!self::isString($name)) {
            throw new \RuntimeException('el nombre del proyecto debe ser una cadena de texto valida');
        }

        if (!self::isString($description)) {
            throw new \RuntimeException('la descripcion del proyecto debe ser una cadena de texto valida');
        }
        
        if (!($projectState instanceof ProjectState)) {
            throw new \RuntimeException('El estado del proyecto debe ser una instancia del modelo ProjectState');
        }

        if (!($agency instanceof Agency)) {
            throw new \RuntimeException('La agencia debe ser una instancia del modelo Agencia');
        }

        if (!($country instanceof Country)) {
            throw new \RuntimeException('El pais debe ser una instancia del modelo Pais');
        }

        if (!($indicator instanceof Indicator)) {
            throw new \RuntimeException('El indicador debe ser una instancia valida de Indicator');
        }

        if (strlen(trim($name)) === 0) {
            throw new \RuntimeException('el nombre del proyecto no debe estar vacio');
        }

        if (strlen(trim($name)) < 3) {
            throw new \RuntimeException('el nombre del proyecto debe tener al menos 3 caracteres');
        }

        if (strlen(trim($description)) < 10) {
            throw new \RuntimeException('la descripcion del proyecto debe tener al menos 10 caracteres');
        }
        if (!self::validateDateString($startDate)) {
            throw new \RuntimeException('la fecha de inicio no es valida');
        }

        if (!self::validateDateString($endDate)) {
            throw new \RuntimeException('la fecha de fin no es valida');
        }

        if (!self::validateDateString($actualEndDate)) {
            throw new \RuntimeException('la fecha actual final no es valida');
        }

        $startDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
        $endDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $endDate);
        $actualEndDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $actualEndDate);

        if ($startDateObj > $endDateObj) {
            throw new \RuntimeException('la fecha de inicio no puede ser mayor a la fecha de fin');
        }

        if ($actualEndDateObj < $startDateObj) {
            throw new \RuntimeException('la fecha actual final no puede ser anterior a la fecha de inicio');
        }

        if (!self::isNumeric($budget) || !self::isNumeric($budgetSpent)) {
            throw new \RuntimeException('El presupuesto y el presupuesto gastado deben ser numeros validos');
        }

        $budgetFloat = (float) $budget;
        $budgetSpentFloat = (float) $budgetSpent;

        if ($budgetFloat <= 0) {
            throw new \RuntimeException('El presupuesto total debe ser mayor a 0');
        }

        if ($budgetSpentFloat < 0) {
            throw new \RuntimeException('el presupuesto gastado no puede ser negativo');
        }

        if ($budgetSpentFloat > $budgetFloat) {
            throw new \RuntimeException('el presupuesto gastado no puede ser mayor al presupuesto total');
        }

        if (strlen(trim($indicator->getName())) < 3) {
            throw new \RuntimeException('El nombre del indicador debe tener al menos 3 caracteres');
        }

        if (!self::isNumeric($expectedImpact)) {
            throw new \RuntimeException('El impacto esperado debe ser un numero valido');
        }

        $expectedImpactFloat = (float) $expectedImpact;

        if ($expectedImpactFloat < 0 || $expectedImpactFloat > 100) {
            throw new \RuntimeException('el impacto esperado debe estar entre 0 y 100');
        }
        if (!self::isString($manager) || strlen(trim($manager)) < 3) {
            throw new \RuntimeException('El proyecto debe tener un manager asignado con al menos 3 caracteres');
        }
        if (!is_bool($shared)) {
            throw new \RuntimeException('shared debe ser un valor booleano (true o false)');
        }

        return new Project(
            trim($name),
            trim($description),
            $country,
            $agency,
            $projectState,
            $startDateObj,
            $endDateObj,
            $actualEndDateObj,
            $budgetFloat,
            $budgetSpentFloat,
            $indicator,
            $expectedImpactFloat,
            trim($manager),
            $shared
        );
    }
}