<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorType extends Model
{
    // attributes
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
    public static function at($name) : IndicatorType
    {
        if(strlen($name) == 0 || strlen($name) < 3)throw new \InvalidArgumentException('el nombre del tipo de indicador no debe ser null o menor a 3 caracteres');
        return new IndicatorType($name);

    }
}
