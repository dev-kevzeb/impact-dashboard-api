<?php

namespace App\Modules\ProgramCountryUserRole\Domain;

use App\Modules\Program\Domain\Program;
use App\Modules\CountryUserRole\Domain\CountryUserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramCountryUserRole extends Model
{
    use HasFactory;
    protected $table = 'program_country_user_role';
    protected $fillable = ['program_id', 'country_user_role_id'];

    /**
     * Relationship: belongs to Program
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Relationship: belongs to CountryUserRole
     */
    public function countryUserRole(): BelongsTo
    {
        return $this->belongsTo(CountryUserRole::class, 'country_user_role_id');
    }

    protected static function newFactory()
    {
        return \Database\Factories\ProgramCountryUserRoleFactory::new();
    }
}
