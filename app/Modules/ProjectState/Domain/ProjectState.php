<?php

namespace App\Modules\ProjectState\Domain;

use Database\Factories\ProjectStateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProjectState extends Model
{
    use HasFactory;

    protected $table = 'project_state';
    protected $fillable = ['state'];

    public static $ERROR_STATE_INVALID_TYPE = 'Project state is invalid';
    public static $ERROR_STATE_EMPTY = 'The project state name should not be empty';  
    public static $ERROR_STATE_MIN_LENGTH = 'Project state must be at least 3 characters';
    public static $ERROR_STATE_MAX_LENGTH = 'project state name must not exceed 100 characters';
    
    public static function newFactory()
    {
        return ProjectStateFactory::new();
    }
    
    public static function at($state): ProjectState
    {
        if (empty(trim($state))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($state)) < 3) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($state)) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        return new ProjectState(['state' => trim($state)]);
    }

    public function getState(): string
    {
        return $this->state;
    }

}
