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
        if(!self::isString($state)){
            throw new \InvalidArgumentException(self::$ERROR_STATE_INVALID_TYPE);
        }
        if(empty(trim($state))){
            throw new \InvalidArgumentException(self::$ERROR_STATE_EMPTY);
        }
        if(strlen(trim($state)) < 3){
            throw new \InvalidArgumentException(self::$ERROR_STATE_MIN_LENGTH);
        }
        if(strlen(trim($state)) > 100){
            throw new \InvalidArgumentException(self::$ERROR_STATE_MAX_LENGTH);
        }
        $state = trim($state);
        $state = mb_strtolower($state);
        return new self(['state' => trim($state)]);
    }

    // getters
    public function getName(): string
    {
        return $this->name;
    }
    public static function isString($value):bool
    {
        return is_string($value);
    }
}
