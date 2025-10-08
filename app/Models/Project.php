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
    private bool $shared;
    private Contact $contact;
    public static $INVALID_NAME = 'El nombre del proyecto debe ser una cadena de al menos 3 caracteres';
    public static $INVALID_DESCRIPTION = 'La descripción del proyecto debe ser una cadena de al menos 10 caracteres';
    public static $INVALID_BUDGET = 'El presupuesto total debe ser un número mayor que 0';
    public static $INVALID_BUDGET_SPENT = 'El presupuesto gastado debe ser >= 0 y no puede exceder el presupuesto total';
    public static $INVALID_EXPECTED_IMPACT = 'El impacto esperado debe ser un número entre 0 y 100';
    public static $INVALID_DATES = 'Las fechas deben tener el formato YYYY-MM-DD y ser válidas';
    public static $INVALID_INDICATOR = 'El indicador debe ser una instancia válida de Indicator con nombre de al menos 3 caracteres';
    public static $INVALID_COUNTRY = 'El país debe ser una instancia válida de Country';
    public static $INVALID_AGENCY = 'La agencia debe ser una instancia válida de Agency';
    public static $INVALID_PROJECT_STATE = 'El estado del proyecto debe ser una instancia válida de ProjectState';
    public static $INVALID_CONTACT = 'El contacto debe ser una instancia válida de Contact';
    public static $INVALID_SHARED = 'El campo shared debe ser booleano (true o false)';
    public static $MAJOR_DATE = 'La fecha de inicio no puede ser posterior a la fecha de fin';
    public static $ACTUAL_END_DATE = 'La fecha de fin real no puede ser anterior a la fecha de inicio';

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
        bool $shared,
        Contact $contact
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
        $this->shared = $shared;
        $this->contact = $contact;
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

  

    public function isShared(): bool
    {
        return $this->shared;
    }
    public function getContact(): Contact
    {
        return $this->contact;
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
        $shared,
        $contact
    ): Project {
        if (!self::isString($name)) {
            throw new \RuntimeException(self::$INVALID_NAME);
        }

        if (!self::isString($description)) {
            throw new \RuntimeException(self::$INVALID_DESCRIPTION);
        }
        
        if (!($projectState instanceof ProjectState)) {
            throw new \RuntimeException(self::$INVALID_PROJECT_STATE);
        }

        if (!($agency instanceof Agency)) {
            throw new \RuntimeException(self::$INVALID_AGENCY);
        }

        if (!($country instanceof Country)) {
            throw new \RuntimeException(self::$INVALID_COUNTRY);
        }

        if (!($indicator instanceof Indicator)) {
            throw new \RuntimeException(self::$INVALID_INDICATOR);
        }
        if (!($contact instanceof Contact)) {
            throw new \RuntimeException(self::$INVALID_CONTACT);
        }
        if (strlen(trim($name)) === 0) {
            throw new \RuntimeException(self::$INVALID_NAME);
        }

        if (strlen(trim($name)) < 3) {
            throw new \RuntimeException(self::$INVALID_NAME);
        }

        if (strlen(trim($description)) < 10) {
            throw new \RuntimeException(self::$INVALID_DESCRIPTION);
        }
        if (!self::validateDateString($startDate)) {
            throw new \RuntimeException(self::$INVALID_DATES);
        }

        if (!self::validateDateString($endDate)) {
            throw new \RuntimeException(self::$INVALID_DATES);
        }

        if (!self::validateDateString($actualEndDate)) {
            throw new \RuntimeException(self::$INVALID_DATES);
        }

        $startDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
        $endDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $endDate);
        $actualEndDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $actualEndDate);

        if ($startDateObj > $endDateObj) {
            throw new \RuntimeException();
        }

        if ($actualEndDateObj < $startDateObj) {
            throw new \RuntimeException(self::$ACTUAL_END_DATE);
        }

        if (!self::isNumeric($budget)) {
            throw new \RuntimeException(self::$INVALID_BUDGET);
        }

        $budgetFloat = (float) $budget;

        if ($budgetFloat <= 0) {
            throw new \RuntimeException(self::$INVALID_BUDGET);
        }

        if (!self::isNumeric($budgetSpent)) {
            throw new \RuntimeException(self::$INVALID_BUDGET_SPENT);
        }

        $budgetSpentFloat = (float) $budgetSpent;

        if ($budgetSpentFloat < 0) {
            throw new \RuntimeException(self::$INVALID_BUDGET_SPENT);
        }

        if ($budgetSpentFloat > $budgetFloat) {
            throw new \RuntimeException(self::$INVALID_BUDGET_SPENT);
        }

        if (strlen(trim($indicator->getName())) < 3) {
            throw new \RuntimeException(self::$INVALID_INDICATOR);
        }

        if (!self::isNumeric($expectedImpact)) {
            throw new \RuntimeException(self::$INVALID_EXPECTED_IMPACT);
        }

        $expectedImpactFloat = (float) $expectedImpact;

        if ($expectedImpactFloat < 0 || $expectedImpactFloat > 100) {
            throw new \RuntimeException(self::$INVALID_EXPECTED_IMPACT);
        }
       
        if (!is_bool($shared)) {
            throw new \RuntimeException(self::$INVALID_SHARED);
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
            $shared,
            $contact
        );
    }
}