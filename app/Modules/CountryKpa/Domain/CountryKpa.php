<?php

namespace App\Modules\CountryKpa\Domain;

use App\Modules\StrategicOutput\Domain\StrategicOutput;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Country\Domain\Country;
use App\Modules\Kpa\Domain\Kpa;

class CountryKpa extends Model
{
    protected $table = 'country_kpa';
    protected $fillable = ['id_country', 'id_kpa'];
    protected $appends = ['strategic_outputs_count'];
    public $timestamps = false;

    public static $ERROR_STRATEGIC_OUTPUT_DUPLICATED = 'no se permiten medidas duplicadas en el resultado estratégico';
    public static $ERROR_STRATEGIC_OUTPUT_INVALID_INSTANCE = 'la medida debe ser una instancia de Measure';
    public static $ERROR_STRATEGIC_OUTPUT_NOT_FOUND = 'la medida especificada no existe en este resultado estratégico';

    public function country()
    {
        return $this->belongsTo(Country::class, 'id_country');
    }

    public function kpa()
    {
        return $this->belongsTo(Kpa::class, 'id_kpa');
    }

    public function addStrategicOutput($strageticOutput)
    {
        if(!($strageticOutput instanceof StrategicOutput)) throw new \RuntimeException(self::$ERROR_STRATEGIC_OUTPUT_INVALID_INSTANCE);
        if($this->hasStrategicOutputWithName($strageticOutput->getName())) throw new \RuntimeException(self::$ERROR_STRATEGIC_OUTPUT_DUPLICATED);

        $this->strategicOutputs()->save($strageticOutput);
    }

    public function removeStrategicOutput(string $strategicOutputName): bool
    {
        $strategicOutput = $this->strategicOutputs()->where('name', trim($strategicOutputName))->first();
        if(!$strategicOutput) throw new \RuntimeException(self::$ERROR_STRATEGIC_OUTPUT_NOT_FOUND . ': ' . $strategicOutputName);

        $strategicOutput->delete();
        return true;
    }

    public function hasStrategicOutputWithName(string $name): bool
    {
        return $this->strategicOutputs()->where('name', trim($name))->exists();
    }

    public function hasStrategicOutputs(): bool
    {
        return $this->strategicOutputs()->exists();
    }

    public function getStrategicOutputs()
    {
        return $this->strategicOutputs()->get();
    }

    public function getStrategicOutputsCountAttribute(): int
    {
        return $this->strategicOutputs()->count();
    }

    public function strategicOutputs()
    {
        return $this->hasMany(StrategicOutput::class, 'id_ck', 'id');
    }
}
