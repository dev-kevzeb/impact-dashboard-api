<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    // attributes
    private string $name;
    private string $measure;
    private IndicatorType $type;
    private int $target;

    // constructor
    public function __construct(string $name, string $measure, IndicatorType $type, int $target)
    {   
        $this->name = $name;
        $this->measure = $measure;
        $this->type = $type;
        $this->target = $target;
    }
    // getters
    public function getName(): string
    {
        return $this->name;
    }
    public function getMeasure(): string
    {
        return $this->measure;
    }
    public function getTarget(): int
    {
        return $this->target;
    }
    public function getType(): IndicatorType
    {
        return $this->type;
    }

    public static function at($name, $measure, $type, $target): Indicator 
    {
        if(strlen($name) == 0 || strlen($name) < 3)throw new \InvalidArgumentException('el nombre del indicador no debe ser null o menor a 3 caracteres');
        if(strlen($measure) == 0 || strlen($measure) < 1)throw new \InvalidArgumentException('la medida del indicador no debe ser null o menor a 1 caracter');
        if(!$type instanceof IndicatorType) throw new \InvalidArgumentException('el tipo de indicador debe ser una instancia de IndicatorType');
        return new Indicator($name, $measure, $type, $target);
    }
}
