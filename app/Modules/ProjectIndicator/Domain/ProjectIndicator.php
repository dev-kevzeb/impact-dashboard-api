<?php

namespace App\Modules\ProjectIndicator\Domain;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Project\Domain\Project;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ProjectIndicator extends Model
{
    protected $table = "project_indicator";
    protected $fillable = ['project_id', 'indicator_id'];


    public static function at($project, $indicator):ProjectIndicator
    {
        if (!($project instanceof Project)) throw new RuntimeException("The project must be an instance of Project.");
        if (!($indicator instanceof Indicator)) throw new RuntimeException("The indicator must be an instance of Indicator.");

        return new self(['project_id'  => $project->id, 'indicator_id' => $indicator->id]);
    }
    public function project(){
        return $this->belongsTo(Project::class, 'project_id','id');
    }

    public function indicators()
    {
        return $this->belongsTo(Indicator::class,'indicator_id','id');
    }
}