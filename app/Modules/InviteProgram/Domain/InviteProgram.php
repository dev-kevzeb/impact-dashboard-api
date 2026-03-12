<?php

namespace App\Modules\InviteProgram\Domain;

use App\Modules\ProgramCountryUserRole\Domain\ProgramCountryUserRole;
use App\Modules\UserRole\Domain\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InviteProgram extends Model
{
    use HasFactory;

    protected $table = 'invite_program';
    protected $fillable = ['program_country_user_role_id', 'invited_user_role_id'];

    public function programCountryUserRole(): BelongsTo
    {
        return $this->belongsTo(ProgramCountryUserRole::class, 'program_country_user_role_id');
    }

    public function invitedUserRole(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'invited_user_role_id');
    }

    protected static function newFactory()
    {
        return \Database\Factories\InviteProgramFactory::new();
    }
}
