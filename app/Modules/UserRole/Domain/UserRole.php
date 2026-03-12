<?php

namespace App\Modules\UserRole\Domain;

use App\Modules\Country\Domain\Country;
use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_role';
    protected $fillable = ['user_id', 'role_id'];

    // Timestamps enabled by default for audit trail
    public $timestamps = true;

    /**
     * Relationship: UserRole belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: UserRole belongs to a Role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relationship: UserRole has many Countries (many-to-many via country_user_role)
     *
     * @return BelongsToMany
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(
            Country::class,
            'country_user_role',
            'user_role_id',
            'country_id'
        )->withTimestamps();
    }

    /**
     * Relationship: UserRole has one CountryUserRole record
     *
     * @return HasOne
     */
    public function countryUserRole(): HasOne
    {
        return $this->hasOne(\App\Modules\CountryUserRole\Domain\CountryUserRole::class, 'user_role_id');
    }

    /**
     * Check if this user role has access to a specific country
     *
     * @param int $countryId
     * @return bool
     */
    public function hasAccessToCountry(int $countryId): bool
    {
        return $this->countries()->where('country_id', $countryId)->exists();
    }

    /**
     * Laravel Factory integration
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserRoleFactory::new();
    }
}
