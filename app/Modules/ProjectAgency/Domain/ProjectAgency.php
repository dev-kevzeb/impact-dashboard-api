<?php

namespace App\Modules\ProjectAgency\Domain;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\Project\Domain\Project;
use \App\Modules\Agency\Domain\Agency;

class ProjectAgency extends Model
{
    protected $table = "project_agency";
    protected $fillable = ['project_id', 'agency_id', 'contribution'];

    public static $ERROR_AGENCY_INVALID = 'The agency must be an instance of Agency';
    public static $ERROR_PROJECT_INVALID = 'The project must be an instance of Project';
    public static $ERROR_CONTRIBUTION_NOT_NUMERIC = 'The contribution must be a number';
    public static $ERROR_CONTRIBUTION_OUT_OF_RANGE = 'The contribution must be between 0 and 100';


    public static function at($project, $agency, $contribution): ProjectAgency
    {
        if (!($project instanceof Project)) throw new \RuntimeException(self::$ERROR_PROJECT_INVALID);
        if (!($agency instanceof Agency)) throw new \RuntimeException(self::$ERROR_AGENCY_INVALID);

        $contributionFloat = (float) $contribution;
        
        if ($contributionFloat < 0 || $contributionFloat > 100) throw new \RuntimeException(self::$ERROR_CONTRIBUTION_OUT_OF_RANGE);

        return new self(['project_id' => $project->id,'agency_id' => $agency->id, 'contribution' => $contribution]);
    }


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'id');
    }
}