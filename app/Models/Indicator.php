<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    // attributes
    private string $name;
    private Measure $measure;
    private IndicatorType $type;
    private int $target;

    static $NAME_MIN_LENGTH = "el nombre del tipo de indicador no debe ser null o menor a 3 caracteres";
    static $INSTANCE_OF_MEASURE = "la medida del indicador debe ser una instancia de Measure o no debe ser null";
    static $INSTANCE_OF_INDICATORTYPE = "el tipo de indicador debe ser una instancia de IndicatorType o no debe ser null";
    // constructor
    public function __construct(string $name, Measure $measure, IndicatorType $type, int $target)
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
    public function getMeasure(): Measure
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
        if(strlen($name) == 0 || strlen($name) < 3)throw new \InvalidArgumentException(self::$NAME_MIN_LENGTH);
        if(!$measure instanceof Measure) throw new \InvalidArgumentException(self::$INSTANCE_OF_MEASURE);
        if(!$type instanceof IndicatorType) throw new \InvalidArgumentException(self::$INSTANCE_OF_INDICATORTYPE);
        return new Indicator($name, $measure, $type, $target);
    }
}
