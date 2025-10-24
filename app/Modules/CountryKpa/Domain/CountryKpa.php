<?php

namespace App\Modules\CountryKpa\Domain;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Country\Domain\Country;
use App\Modules\Kpa\Domain\Kpa;

class CountryKpa extends Model
{
    protected $table = 'country_kpa';
    protected $fillable = ['id_country', 'id_kpa'];
    public $timestamps = false;

    public function country()
    {
        return $this->belongsTo(Country::class, 'id_country');
    }

    public function kpa()
    {
        return $this->belongsTo(Kpa::class, 'id_kpa');
    }
}
