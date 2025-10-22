<?php

namespace App\Modules\Currency\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class CurrencyModel extends Model
{
    protected $table = 'currencies';
    protected $fillable = ['code'];

    public function countries()
    {
        return $this->hasMany(\App\Modules\Country\Domain\Entities\CountryModel::class, 'currency_id');
    }
}
