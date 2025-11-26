<?php

namespace App\Modules\ProjectAgency\Domain;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\Project\Domain\Project;
use \App\Modules\Agency\Domain\Agency;

class ProjectAgency extends Model
{
    protected $table = "project_agency";
    protected $fillable = ['project_id', 'agency_id'];

    public static function at($project, $agency): ProjectAgency
    {
        if (!($project instanceof Project)) throw new \RuntimeException("El proyecto proporcionado es inválido.");

        if (!($agency instanceof Agency)) throw new \RuntimeException("La agencia proporcionada es inválida.");

        return new self(['project_id' => $project->id,'agency_id' => $agency->id]);
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