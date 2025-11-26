<?php

namespace App\Modules\Project\Domain;

use App\Modules\Agency\Domain\Agency;
use App\Modules\Beneficiary\Domain\Beneficiary;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Donor\Domain\Donor;
use App\Modules\Indicator\Domain\Indicator;
use App\Modules\ProjectAgency\Domain\ProjectAgency;
use App\Modules\ProjectIndicator\Domain\ProjectIndicator;
use App\Modules\ProjectState\Domain\ProjectState;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = "project";
    protected $fillable = ['name','description', 'project_url', 'start_date', 'end_date', 'progress', 'comments', 'project_budget', 'contact_id', 'beneficiary_id', 'project_state_id'];
    protected $appends = ['donors_count', 'indicators_count', 'agencies_count'];
    
    // Constantes de mensajes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del proyecto no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del proyecto debe tener al menos 3 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del proyecto no debe exceder 255 caracteres';
    public static $ERROR_DESCRIPTION_EMPTY = 'la descripción del proyecto no debe ir vacio';
    public static $ERROR_DESCRIPTION_MIN_LENGTH = 'la descripción del proyecto debe tener al menos 10 caracteres';
    public static $ERROR_DESCRIPTION_MAX_LENGTH = 'la descripción del proyecto no debe exceder 2000 caracteres';
    public static $ERROR_PROJECT_URL_INVALID_FORMAT = 'la URL del proyecto debe tener un formato válido';
    public static $ERROR_PROJECT_URL_INVALID_PROTOCOL = 'la URL del proyecto debe usar protocolo HTTP o HTTPS';
    public static $ERROR_START_DATE_EMPTY = 'la fecha de inicio del proyecto no debe ir vacio';
    public static $ERROR_START_DATE_INVALID_FORMAT = 'la fecha de inicio debe tener formato válido (YYYY-MM-DD)';
    public static $ERROR_END_DATE_EMPTY = 'la fecha de fin del proyecto no debe ir vacio';
    public static $ERROR_END_DATE_INVALID_FORMAT = 'la fecha de fin debe tener formato válido (YYYY-MM-DD)';
    public static $ERROR_END_DATE_BEFORE_START = 'la fecha de fin debe ser posterior a la fecha de inicio';
    public static $ERROR_PROGRESS_INVALID = 'el progreso debe ser un número entre 0 y 100';
    public static $ERROR_COMMENTS_MAX_LENGTH = 'los comentarios no deben exceder 1000 caracteres';
    public static $ERROR_PROJECT_BUDGET_INVALID = 'el presupuesto del proyecto debe ser un número mayor que 0';
    public static $ERROR_INDICATOR_INVALID = 'el indicador debe ser una instancia de Indicator';
    public static $ERROR_AGENCY_INVALID = 'la agencia debe ser una instancia de Agency';
    public static $ERROR_PROJECT_STATE_INVALID = 'el estado debe ser una instancia de ProjectState';
    public static $ERROR_PROJECT_BENEFICIARY_INVALID = 'el beneficiario debe ser una instancia de Beneficiary';
    public static $ERROR_CONTACT_INVALID = 'el contacto debe ser una instancia de Contact';
    public static $ERROR_PROJECT_DONORS_INVALID_INSTANCE = 'todos los donantes deben ser instancias de ProjectDonor';
    public static $ERROR_PROJECT_DONORS_DUPLICATED = 'no se permiten donantes duplicados en el proyecto';

    public static function at($name, $description, $projectUrl, $startDate, $endDate, $progress, $comments, $projectBudget, $contact, $projectBeneficiary, $projectState): Project {
        // Validaciones del nombre
        if (empty(trim($name))) throw new \RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen(trim($name)) < 3) throw new \RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        if (strlen(trim($name)) > 255) throw new \RuntimeException(self::$ERROR_NAME_MAX_LENGTH);

        // Validaciones de la descripción
        if (empty(trim($description))) throw new \RuntimeException(self::$ERROR_DESCRIPTION_EMPTY);
        if (strlen(trim($description)) < 10) throw new \RuntimeException(self::$ERROR_DESCRIPTION_MIN_LENGTH);
        if (strlen(trim($description)) > 2000) throw new \RuntimeException(self::$ERROR_DESCRIPTION_MAX_LENGTH);

        // Validaciones de projectUrl 
        if (!empty(trim($projectUrl))) {
            if (!filter_var($projectUrl, FILTER_VALIDATE_URL)) throw new \RuntimeException(self::$ERROR_PROJECT_URL_INVALID_FORMAT);
            // Validar que use HTTPS o HTTP solamente
            $parsedUrl = parse_url($projectUrl);
            if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'], true)) throw new \RuntimeException(self::$ERROR_PROJECT_URL_INVALID_PROTOCOL);
        }

        // Validaciones de startDate
        if (empty(trim($startDate))) throw new \RuntimeException(self::$ERROR_START_DATE_EMPTY);
        if (!self::isValidDate($startDate)) throw new \RuntimeException(self::$ERROR_START_DATE_INVALID_FORMAT);

        // Validaciones de endDate
        if (empty(trim($endDate))) throw new \RuntimeException(self::$ERROR_END_DATE_EMPTY);
        if (!self::isValidDate($endDate)) throw new \RuntimeException(self::$ERROR_END_DATE_INVALID_FORMAT);

        // DateTime para comparaciones más precisas
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        if ($endDateTime < $startDateTime) throw new \RuntimeException(self::$ERROR_END_DATE_BEFORE_START);

        // Validaciones de progress
        if (!is_numeric($progress)) throw new \RuntimeException(self::$ERROR_PROGRESS_INVALID);
        $progressFloat = (float) $progress;
        if ($progressFloat < 0 || $progressFloat > 100) throw new \RuntimeException(self::$ERROR_PROGRESS_INVALID);

        // Validaciones de comments
        if (strlen(trim($comments)) > 1000) throw new \RuntimeException(self::$ERROR_COMMENTS_MAX_LENGTH);

        // Validaciones de projectBudget
        if (!is_numeric($projectBudget)) throw new \RuntimeException(self::$ERROR_PROJECT_BUDGET_INVALID);
        $projectBudgetFloat = (float) $projectBudget;
        if ($projectBudgetFloat <= 0) throw new \RuntimeException(self::$ERROR_PROJECT_BUDGET_INVALID);

        // Validaciones de entidades
        if (!($contact instanceof Contact)) throw new \RuntimeException(self::$ERROR_CONTACT_INVALID);

        if (!($projectBeneficiary instanceof Beneficiary)) throw new \RuntimeException(self::$ERROR_PROJECT_BENEFICIARY_INVALID);

        if (!($projectState instanceof ProjectState)) throw new \RuntimeException(self::$ERROR_PROJECT_STATE_INVALID);

        return new self(['name' => trim($name), 'description'=> trim($description), 'project_url'=>trim($projectUrl),
            'start_date'=> $startDate, 'end_date'=> $endDate, 'progress'=>$progressFloat, 'comments'=>trim($comments),
            'project_budget'=> $projectBudgetFloat, 'contact_id'=> $contact->id, 'beneficiary_id'=>$projectBeneficiary->id, 'project_state_id'=>$projectState->id]);
    }

    // RELACIONES
    public function donors()
    {
        return $this->hasMany(Donor::class, 'project_id','id');
    }

    public function projectIndicators()
    {
        return $this->hasMany(ProjectIndicator::class, 'project_id', 'id');
    }

    public function projectAgencies()
    {
        return $this->hasMany(ProjectAgency::class, 'project_id', 'id');
    }

    // DEVOLUCION DE LOS ARREGLOS

    public function agencies()
    {
        return $this->belongsToMany(Agency::class, 'project_agency', 'project_id', 'agency_id');
    }

    public function indicators()
    {
        return $this->belongsToMany(Indicator::class, 'project_agency', 'project_id', 'indicator_id');
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

    public function getProjectUrl(): string
    {
        return $this->projectUrl;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getProgress(): float
    {
        return $this->progress;
    }

    public function getComments(): string
    {
        return $this->comments;
    }

    public function getProjectBudget(): float
    {
        return $this->projectBudget;
    }

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function getProjectBeneficiary(): Beneficiary
    {
        return $this->projectBeneficiary;
    }

    public function getProjectState(): ProjectState
    {
        return $this->projectState;
    }

    public function hasDonorsWithName(string $donorName): bool
    {
        return $this->donors()->where('name', trim($donorName))->exists();
    }

    public function addDonors($donor)
    {
        if(!($donor instanceof Donor)) throw new \RuntimeException(self::$ERROR_PROJECT_DONORS_INVALID_INSTANCE);
        if($this->hasDonorsWithName($donor->getName())) throw new \RuntimeException(self::$ERROR_PROJECT_DONORS_DUPLICATED);
        
        $this->donors()->save($donor);
    }    

    private static function isValidDate(string $date): bool
    {
        $date = trim($date);
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        $errors = \DateTime::getLastErrors();
        
        if ($errors && ($errors['error_count'] > 0 || $errors['warning_count'] > 0)) {
            return false;
        }
        
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }

    
}