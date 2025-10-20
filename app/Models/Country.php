<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Country extends Model
{
    protected $table = 'countries';
    protected $fillable = ['id', 'name', 'currency_id', 'created_at', 'updated_at'];

  

    // Constantes para mensajes de validación
    public static $ERROR_NAME_EMPTY = 'el nombre del país no debe ir vacio';
    public static $ERROR_NAME_TOO_SHORT = 'el nombre del país debe tener al menos 2 caracteres';
    public static $ERROR_NAME_TOO_LONG = 'el nombre del país no debe exceder 100 caracteres';
    public static $ERROR_NAME_INVALID_CHARACTERS = 'el nombre del país contiene caracteres no válidos';
    public static $ERROR_CURRENCY_INVALID = 'la moneda debe ser una instancia de Currency';
    public static $ERROR_KPA_MUST_BE_ARRAY = 'los KPAs deben estar en un array';
    private ?string $name = null;
    private ?Currency $currency = null;
    // private array $kpa = [];
    static $INVALIDNAME = 'el nombre del país no debe ir vacio';
    /**
     * Constructor de dominio.
     * los parametros son opcionales para que eloquent pueda instanciar el modelo
     * sin argumentos cuando hidrata registros desde la base de datos.
     *
     * Cuando se pasan los parámetros (nombre, moneda y kpas) se inicializa la
     * entidad de dominio; en caso contrario se deja que Eloquent maneje la
     * instanciación normal del modelo.
     *
     * NOTA: No modificamos la lógica del modelo aquí, solo permitimos que el
     * contenedor y Eloquent creen la instancia sin requerir parámetros.
     */
    public function __construct(string $name = '', ?Currency $currency = null, array $kpa = [])
    {
        if ($name !== '' && $currency !== null && is_array($kpa)) {
            $this->name = $name;
            $this->currency = $currency;
            $this->kpa = $kpa;
        }

        // Permitir que Eloquent inicialice el modelo normalmente
        parent::__construct();
    }

    public static function at($name, $currency, $kpa): Country
    {
        
        if (empty(trim($name))) {
            throw new RuntimeException(self::$ERROR_NAME_EMPTY);
        }
        
        $trimmedName = trim($name);
        
        if (strlen($trimmedName) < 2) {
            throw new RuntimeException(self::$ERROR_NAME_TOO_SHORT);
        }
        
        if (strlen($trimmedName) > 100) {
            throw new RuntimeException(self::$ERROR_NAME_TOO_LONG);
        }
        
        // Validar que contenga solo letras, espacios, guiones, apostrofes y caracteres unicode válidos
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', $trimmedName)) {
            throw new RuntimeException(self::$ERROR_NAME_INVALID_CHARACTERS);
        }
        
        if (!($currency instanceof Currency)) {
            throw new RuntimeException(self::$ERROR_CURRENCY_INVALID);
        }
        if (!is_array($kpa)) {
            throw new RuntimeException(self::$ERROR_KPA_MUST_BE_ARRAY);
        }

        if (count($kpa) === 0) {
            throw new RuntimeException('debe haber al menos un KPA en el array de KPAs');
        }
        foreach ($kpa as $item) {
            if (!($item instanceof Kpa)) {
                throw new RuntimeException('cada KPA debe ser una instancia de Kpa');
            }
        }
      $seen = [];
        foreach ($kpa as $kp) {
            $name = mb_strtolower(trim($kp->getName()));
            if ($name === "") {
                throw new RuntimeException("El KPA tiene un nombre vacío");
            }
            if (isset($seen[$name])) {
                throw new RuntimeException("no puede haber KPAs con el mismo nombre en un país");
            }
            $seen[$name] = true;
        }
        
        return new Country($trimmedName, $currency, $kpa);
    }

    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getCurrency(): Currency
    {
        return $this->currency;
    }
    
    public function getCurrencyCode(): string
    {
        return $this->currency->getCode();
    }
    
    public function getKpas(): array
    {
        return $this->kpa;
    }
      public function currency(){
        // define la relacion con el modelo Currency
        // 1 pais pertenece a 1 moneda
        return $this->belongsTo(Currency::class);
    }
    public function kpas(){
        // define la relacion con el modelo Kpa
        // 1 pais tiene muchos KPAs
        return $this->hasMany(Kpa::class);
    }

}
