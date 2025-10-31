<?php

namespace App\Modules\ProjectState\Domain;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProjectState extends Model
{

    protected $table = 'project_state';
    protected $fillable = ['state'];
    // Constantes de mensajes de error
    public static $ERROR_STATE_INVALID_TYPE = 'El estado del proyecto no es valido';
    public static $ERROR_STATE_EMPTY = 'el nombre del estado del proyecto no debe ir vacio';  
    public static $ERROR_STATE_MIN_LENGTH = 'el nombre del estado del proyecto debe tener al menos 3 caracteres';
    public static $ERROR_STATE_MAX_LENGTH = 'el nombre del estado del proyecto no debe exceder 100 caracteres';
    
  
    
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
