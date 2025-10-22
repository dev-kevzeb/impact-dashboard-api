<?php

namespace App\Modules\Kpa\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class KpaModel extends Model
{
    protected $table = 'kpas';
    protected $fillable = ['name', 'implementation'];

    public function countries()
    {
        return $this->belongsToMany(\App\Modules\Country\Domain\Entities\CountryModel::class, 'country_kpas', 'id_kpa', 'id_country');
    }
}
