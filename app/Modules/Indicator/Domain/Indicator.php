<?php

namespace App\Modules\Indicator\Domain;

use App\Modules\IndicatorType\Domain\IndicatorType;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class  Indicator extends Model
{
    // Tabla asociada
    protected $table = 'indicator';

    // Campos permitidos
    protected $fillable = ['name', 'type_id', 'target'];

    // Constantes de error
    public static $ERROR_NAME_EMPTY = 'el nombre del indicador no debe ir vacio';
    public static $ERROR_NAME_MIN_LENGTH = 'el nombre del indicador debe tener al menos 2 caracteres';
    public static $ERROR_NAME_MAX_LENGTH = 'el nombre del indicador no debe exceder 200 caracteres';
    public static $ERROR_TYPE_REQUIRED = 'el tipo de indicador debe ser una instancia de IndicatorType';
    public static $ERROR_TARGET_INVALID = 'el target del indicador debe ser un número positivo';

    /**
     * Constructor opcional (dejarlo sin romper Eloquent)
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Fábrica de creación con validación de dominio
     */
    public static function at(string $name, IndicatorType $type, float $target): Indicator
    {
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        if (strlen(trim($name)) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_MIN_LENGTH);
        }
        if (strlen(trim($name)) > 200) {
            throw new RuntimeException(self::$ERROR_NAME_MAX_LENGTH);
        }
        if (!$type instanceof IndicatorType) {
            throw new RuntimeException(self::$ERROR_TYPE_REQUIRED);
        }
        if (!is_numeric($target) || $target <= 0) {
            throw new RuntimeException(self::$ERROR_TARGET_INVALID);
        }

        return new Indicator([
            'name' => trim($name),
            'type_id' => $type->id,
            'target' => $target,
        ]);
    }

    // Getters
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
        return $this->belongsTo(IndicatorType::class, 'type_id');
    }
}
