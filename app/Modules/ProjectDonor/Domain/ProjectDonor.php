<?php

namespace App\Modules\ProjectDonor\Domain;

use App\Modules\Donor\Domain\Donor;
use App\Modules\Project\Domain\Project;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProjectDonor extends Model
{

    protected $table = "project_donor";
    protected $fillable = ['project_id', 'donor_id', 'contribution'];

    public static $ERROR_DONOR_INVALID = 'The donor must be an instance of Donor';
    public static $ERROR_PROJECT_INVALID = 'The project must be an instance of Project';
    public static $ERROR_CONTRIBUTION_NOT_NUMERIC = 'The contribution must be a number';
    public static $ERROR_CONTRIBUTION_OUT_OF_RANGE = 'The contribution must be between 0 and 100';

    
    public static function at($project, $donor, $contribution): ProjectDonor
    {
        if (!($project instanceof Project)) throw new RuntimeException(self:: $ERROR_PROJECT_INVALID);
        if (!($donor instanceof Donor)) throw new RuntimeException(self::$ERROR_DONOR_INVALID);
        
        if (!is_numeric($contribution)) throw new RuntimeException(self::$ERROR_CONTRIBUTION_NOT_NUMERIC);
        
        
        $contributionFloat = (float) $contribution;
        
        if ($contributionFloat < 0 || $contributionFloat > 100) throw new RuntimeException(self::$ERROR_CONTRIBUTION_OUT_OF_RANGE);
        return new self(['project_id' => $project->id, 'donor_id' => $donor->id, 'contribution'=> $contribution]);
    }

    public function project(){
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class, 'donor_id', 'id');
    }
    
}
