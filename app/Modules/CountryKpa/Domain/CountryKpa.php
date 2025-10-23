<?php

namespace App\Modules\CountryKpa\Domain;

use Illuminate\Database\Eloquent\Model;

class CountryKpa extends Model
{
    protected $table = 'country_kpa';
    protected $fillable = ['id_country', 'id_kpa'];
    public $timestamps = false;
}
