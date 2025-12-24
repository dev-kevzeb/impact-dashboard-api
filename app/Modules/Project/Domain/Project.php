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
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $table = "project";
    protected $fillable = ['name','description', 'project_url', 'start_date', 'end_date', 
    'progress', 'comments', 'project_budget', 'contact_id', 'beneficiary_id', 'project_state_id', 'program_id'];
    protected $appends = ['donors_count', 'indicators_count', 'agencies_count'];

    public static $ERROR_NAME_EMPTY = 'The project name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'The project name must have at least 3 characters';
    public static $ERROR_NAME_MAX_LENGTH = 'The project name must not exceed 255 characters';

    public static $ERROR_DESCRIPTION_EMPTY = 'The project description must not be empty';
    public static $ERROR_DESCRIPTION_MIN_LENGTH = 'The project description must have at least 10 characters';
    public static $ERROR_DESCRIPTION_MAX_LENGTH = 'The project description must not exceed 2000 characters';

    public static $ERROR_PROJECT_URL_INVALID_FORMAT = 'The project URL must have a valid format';
    public static $ERROR_PROJECT_URL_INVALID_PROTOCOL = 'The project URL must use HTTP or HTTPS protocol';

    public static $ERROR_START_DATE_EMPTY = 'The project start date must not be empty';
    public static $ERROR_START_DATE_INVALID_FORMAT = 'The project start date must have a valid format (YYYY-MM-DD)';

    public static $ERROR_END_DATE_EMPTY = 'The project end date must not be empty';
    public static $ERROR_END_DATE_INVALID_FORMAT = 'The project end date must have a valid format (YYYY-MM-DD)';
    public static $ERROR_END_DATE_BEFORE_START = 'The project end date must be after the start date';

    public static $ERROR_PROGRESS_INVALID = 'The progress must be a number between 0 and 100';
    public static $ERROR_COMMENTS_MAX_LENGTH = 'The comments must not exceed 1000 characters';

    public static $ERROR_PROJECT_BUDGET_INVALID = 'The project budget must be a number greater than 0';

    public static $ERROR_INDICATOR_INVALID = 'The indicator must be an instance of Indicator';
    public static $ERROR_AGENCY_INVALID = 'The agency must be an instance of Agency';
    public static $ERROR_PROJECT_STATE_INVALID = 'The project state must be an instance of ProjectState';
    public static $ERROR_PROJECT_BENEFICIARY_INVALID = 'The beneficiary must be an instance of Beneficiary';
    public static $ERROR_CONTACT_INVALID = 'The contact must be an instance of Contact';

    public static $ERROR_PROJECT_DONORS_INVALID_INSTANCE = 'All donors must be instances of ProjectDonor';
    public static $ERROR_PROJECT_DONORS_DUPLICATED = 'Duplicate donors are not allowed in the project';

    public static function newFactory()
    {
        return ProjectFactory::new();
    }
    public static function at($name, $description, $projectUrl, $startDate, $endDate, $progress, $comments, $projectBudget, $contact, $projectBeneficiary, $projectState): Project {
    
        if (empty(trim($name))) throw new \RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen(trim($name)) < 3) throw new \RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        if (strlen(trim($name)) > 255) throw new \RuntimeException(self::$ERROR_NAME_MAX_LENGTH);

        if (empty(trim($description))) throw new \RuntimeException(self::$ERROR_DESCRIPTION_EMPTY);
        if (strlen(trim($description)) < 10) throw new \RuntimeException(self::$ERROR_DESCRIPTION_MIN_LENGTH);
        if (strlen(trim($description)) > 2000) throw new \RuntimeException(self::$ERROR_DESCRIPTION_MAX_LENGTH);

        if (!empty(trim($projectUrl))) {
            if (!filter_var($projectUrl, FILTER_VALIDATE_URL)) throw new \RuntimeException(self::$ERROR_PROJECT_URL_INVALID_FORMAT);
            $parsedUrl = parse_url($projectUrl);
            if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'], true)) throw new \RuntimeException(self::$ERROR_PROJECT_URL_INVALID_PROTOCOL);
        }

        if (empty(trim($startDate))) throw new \RuntimeException(self::$ERROR_START_DATE_EMPTY);
        if (!self::isValidDate($startDate)) throw new \RuntimeException(self::$ERROR_START_DATE_INVALID_FORMAT);

        if (empty(trim($endDate))) throw new \RuntimeException(self::$ERROR_END_DATE_EMPTY);
        if (!self::isValidDate($endDate)) throw new \RuntimeException(self::$ERROR_END_DATE_INVALID_FORMAT);

        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        if ($endDateTime < $startDateTime) throw new \RuntimeException(self::$ERROR_END_DATE_BEFORE_START);

        if (!is_numeric($progress)) throw new \RuntimeException(self::$ERROR_PROGRESS_INVALID);
        $progressFloat = (float) $progress;
        if ($progressFloat < 0 || $progressFloat > 100) throw new \RuntimeException(self::$ERROR_PROGRESS_INVALID);

        if (strlen(trim($comments)) > 1000) throw new \RuntimeException(self::$ERROR_COMMENTS_MAX_LENGTH);
        if (!is_numeric($projectBudget)) throw new \RuntimeException(self::$ERROR_PROJECT_BUDGET_INVALID);
        $projectBudgetFloat = (float) $projectBudget;
        if ($projectBudgetFloat <= 0) throw new \RuntimeException(self::$ERROR_PROJECT_BUDGET_INVALID);

        if (!($contact instanceof Contact)) throw new \RuntimeException(self::$ERROR_CONTACT_INVALID);
        if (!($projectBeneficiary instanceof Beneficiary)) throw new \RuntimeException(self::$ERROR_PROJECT_BENEFICIARY_INVALID);
        if (!($projectState instanceof ProjectState)) throw new \RuntimeException(self::$ERROR_PROJECT_STATE_INVALID);

        return new self(['name' => trim($name), 'description'=> trim($description), 'project_url'=>trim($projectUrl),
            'start_date'=> $startDate, 'end_date'=> $endDate, 'progress'=>$progressFloat, 'comments'=>trim($comments),
            'project_budget'=> $projectBudgetFloat, 'contact_id'=> $contact->id, 'beneficiary_id'=>$projectBeneficiary->id, 'project_state_id'=>$projectState->id]);
    }

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


    public function agencies()
    {
        return $this->belongsToMany(Agency::class, 'project_agency', 'project_id', 'agency_id');
    }

    public function indicators()
    {
        return $this->belongsToMany(Indicator::class, 'project_indicator', 'project_id', 'indicator_id');
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

    public function getDonorsCountAttribute(): int
    {
        return $this->donors()->count();
    }

    public function getIndicatorsCountAttribute(): int
    {
        return $this->donors()->count();
    }
    public function getAgenciesCountAttribute(): int
    {
        return $this->donors()->count();
    }
    
}