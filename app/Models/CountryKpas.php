<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryKpas extends Model
{
    /**
     * Tabla pivot entre countries y kpas.
     */
    protected $table = 'country_kpas';

    // Solo los campos FK en la pivot
    protected $fillable = ['id_country', 'id_kpa'];

    // La tabla pivot no usa timestamps por defecto
    public $timestamps = false;
}
