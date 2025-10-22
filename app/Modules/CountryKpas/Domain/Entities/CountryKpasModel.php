<?php

namespace App\Modules\CountryKpas\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class CountryKpasModel extends Model
{
    protected $table = 'country_kpas';
    protected $fillable = ['id_country', 'id_kpa'];
    public $timestamps = false;
}
