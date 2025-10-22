<?php

namespace App\Modules\Country\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class CountryModel extends Model
{
    protected $table = 'countries';
    protected $fillable = ['name', 'currency_id'];

    public function kpas()
    {
        return $this->belongsToMany(\App\Modules\Kpa\Domain\Entities\KpaModel::class, 'country_kpas', 'id_country', 'id_kpa');
    }

    public function currency()
    {
        return $this->belongsTo(\App\Modules\Currency\Domain\Entities\CurrencyModel::class, 'currency_id');
    }
}