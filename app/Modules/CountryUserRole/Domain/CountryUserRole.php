<?php

namespace App\Modules\CountryUserRole\Domain;

use App\Modules\Country\Domain\Country;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryUserRole extends Model
{
    use HasFactory;

    protected $table = 'country_user_role';
    protected $fillable = ['country_id', 'user_role_id'];

    /**
     * Relationship: CountryUserRole belongs to Country
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Relationship: CountryUserRole belongs to UserRole
     *
     * @return BelongsTo
     */
    public function userRole(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }

    /**
     * Laravel Factory integration
     *
     * @return \Database\Factories\CountryUserRoleFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\CountryUserRoleFactory::new();
    }
}
