<?php

namespace App\Modules\Indicator\Domain;

use App\Modules\IndicatorType\Domain\IndicatorType;
use App\Modules\Measure\Domain\Measure;
use App\Modules\ProjectIndicator\Domain\ProjectIndicator;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class  Indicator extends Model
{
    // Tabla asociada
    protected $table = 'indicator';

    // Campos permitidos
    protected $fillable = ['name', 'type_id', 'target', 'measure_id'];

    // Constantes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del indicador no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del indicador debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del indicador no debe exceder 200 caracteres';
    public static $ERROR_TYPE_REQUIRED = 'el tipo de indicador debe ser una instancia de IndicatorType';
    public static $ERROR_TARGET_INVALID = 'el target del indicador debe ser un número positivo';
    public static $ERROR_MEASURE_REQUIRED = 'la meta debe ser una instancia de Measure';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $name, $type, $target, $measure): Indicator
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        
        if (strlen(trim($name)) > 200) throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        
        if (!$type instanceof IndicatorType) throw new RuntimeException(self::$ERROR_TYPE_REQUIRED);

        if( !$measure instanceof Measure) throw new RuntimeException(self::$ERROR_MEASURE_REQUIRED);
        
        if (!is_numeric($target) || $target <= 0) throw new RuntimeException(self::$ERROR_TARGET_INVALID);
        
        return new Indicator(['name' => trim($name), 'type_id' => $type->id, 'target' => $target, 'measure_id'=> $measure->id]); 
    }

    public function getName(): string
    {   
        return $this->name;
    }

    public function getTarget(): float
    {
        return $this->target;
    }

    public function getType(): IndicatorType
    {
        return $this->type;
    }

    public function type()
    {
        return $this->belongsTo(IndicatorType::class, 'type_id','id');
    }

    public function measure()
    {
        return $this->belongsTo( Measure::class,  'measure_id', 'id');
    }

    public function projectIndicators()
    {
        return $this->hasMany(ProjectIndicator::class,'indicator_id','id');
    }
}
