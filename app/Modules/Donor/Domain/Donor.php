<?php

namespace App\Modules\Donor\Domain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use \App\Modules\Project\Domain\Project;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Donor extends Model
{
    use HasFactory;
    
    protected $table = 'donor';
    protected $fillable = ['name', 'contribution', 'project_id'];

    public static $ERROR_NAME_EMPTY = 'The donor name must not be empty';
    public static $ERROR_NAME_MIN_LENGTH = 'The donor name must have at least 2 characters';

    public static $ERROR_CONTRIBUTION_NOT_NUMERIC = 'The contribution must be a number';
    public static $ERROR_CONTRIBUTION_OUT_OF_RANGE = 'The contribution must be between 0 and 100';

    public static $ERROR_PROJECT_INVALID = 'The associated project is invalid';


    protected static function newFactory()
    {
        return \Database\Factories\DonorFactory::new();
    }
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public static function at($name, $contribution, $project): Donor  
    {
        if (empty(trim($name))) throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        if (strlen(trim($name)) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);

        if(!is_numeric($contribution)) throw new RuntimeException(self::$ERROR_CONTRIBUTION_NOT_NUMERIC);
        $contributionFloat = (float) $contribution;
        if ($contributionFloat < 0 || $contributionFloat > 100) throw new RuntimeException(self::$ERROR_CONTRIBUTION_OUT_OF_RANGE);

        if(!($project instanceof Project)) throw new RuntimeException(self::$ERROR_PROJECT_INVALID);
        
        return new Donor(['name' => trim($name), 'contribution' => $contributionFloat, 'project_id' => $project->id]);
    }
    
    public function validateName(): bool
    {
        return strlen($this->name) >= 2;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}