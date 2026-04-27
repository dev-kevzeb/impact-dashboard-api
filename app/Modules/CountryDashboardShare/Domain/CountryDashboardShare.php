<?php

namespace App\Modules\CountryDashboardShare\Domain;

use App\Modules\Country\Domain\Country;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryDashboardShare extends Model
{
    use HasFactory;

    protected $table = 'country_dashboard_share';
    protected $fillable = ['country_id', 'owner_country_user_role_id', 'shared_user_role_id'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function ownerCountryUserRole(): BelongsTo
    {
        return $this->belongsTo(CountryUserRole::class, 'owner_country_user_role_id');
    }

    public function sharedUserRole(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'shared_user_role_id');
    }
}
