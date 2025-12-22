<?php

namespace App\Modules\CountryKpaUser\Domain;

use App\Modules\CountryKpa\Domain\CountryKpa;
use App\Modules\User\Domain\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryKpaUser extends Model
{
    use HasFactory;
    
    protected $table = 'country_kpa_user';
    protected $fillable = ['country_kpa_id', 'user_id'];
    
    /**
     * Relationship: CountryKpaUser belongs to CountryKpa
     *
     * @return BelongsTo
     */
    public function countryKpa(): BelongsTo
    {
        return $this->belongsTo(CountryKpa::class, 'country_kpa_id');
    }
    
    /**
     * Relationship: CountryKpaUser belongs to User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Laravel Factory integration
     *
     * @return \Database\Factories\CountryKpaUserFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\CountryKpaUserFactory::new();
    }
}
