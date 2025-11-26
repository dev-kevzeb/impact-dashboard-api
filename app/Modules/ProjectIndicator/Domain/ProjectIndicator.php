<?php

namespace App\Modules\ProjectIndicator\Domain;

use App\Modules\Indicator\Domain\Indicator;
use App\Modules\Project\Domain\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectIndicator extends Model
{
    protected $table = "project_indicator";
    protected $fillable = ['project_id', 'indicator_id'];

    public function project(){
        return $this->belongsTo(Project::class, 'project_id','id');
    }

    public function indicator()
    {
        return $this->belongsTo(Indicator::class,'indicator_id','id');
    }
}