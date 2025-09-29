<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectState extends Model
{
    //
    private string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    // getters
    public function getName(): string
    {
        return $this->name;
    }
    public function isString($value):bool
    {
        return is_string($value);
    }
    public static function at($state): ProjectState
    {
        if(!ProjectState::isString($state)){
            throw new \InvalidArgumentException("El estado del proyecto no es valido");
        }
        if(strlen($state) == 0 || strlen($state) < 3){
            throw new \InvalidArgumentException('el nombre del estado del proyecto no debe ser null o menor a 3 caracteres');

        }
        return new ProjectState($state);
    }
}
