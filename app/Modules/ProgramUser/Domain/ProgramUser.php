<?php

namespace App\Modules\ProgramUser\Domain;

use App\Modules\Program\Domain\Program;
use App\Modules\CountryKpaUser\Domain\CountryKpaUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramUser extends Model
{
    use HasFactory;
    
    protected $table = 'program_user';
    protected $fillable = ['program_id', 'country_kpa_user_id'];
    
    /**
     * Relationship: ProgramUser belongs to Program
     *
     * @return BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
    
    /**
     * Relationship: ProgramUser belongs to CountryKpaUser
     *
     * @return BelongsTo
     */
    public function countryKpaUser(): BelongsTo
    {
        return $this->belongsTo(CountryKpaUser::class, 'country_kpa_user_id');
    }
    
    /**
     * Laravel Factory integration
     *
     * @return \Database\Factories\ProgramUserFactory
     */
    protected static function newFactory()
    {
        return \Database\Factories\ProgramUserFactory::new();
    }
}
