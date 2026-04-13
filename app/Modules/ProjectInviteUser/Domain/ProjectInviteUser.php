<?php

namespace App\Modules\ProjectInviteUser\Domain;

use App\Modules\CountryUserRole\Domain\CountryUserRole;
use App\Modules\Project\Domain\Project;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProjectInviteUser extends Model
{
    protected $table = 'project_invite_user';

    protected $fillable = [
        'project_id',
        'country_user_role_id',
    ];

    public static $ERROR_PROJECT_INVALID = 'The project must be an instance of Project';
    public static $ERROR_COUNTRY_USER_ROLE_INVALID = 'The country user role must be an instance of CountryUserRole';

    public static function at(Project $project, CountryUserRole $countryUserRole): self
    {
        if (!($project instanceof Project)) {
            throw new RuntimeException(self::$ERROR_PROJECT_INVALID);
        }

        if (!($countryUserRole instanceof CountryUserRole)) {
            throw new RuntimeException(self::$ERROR_COUNTRY_USER_ROLE_INVALID);
        }

        return new self([
            'project_id' => $project->id,
            'country_user_role_id' => $countryUserRole->id,
        ]);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function countryUserRole()
    {
        return $this->belongsTo(CountryUserRole::class, 'country_user_role_id');
    }
}