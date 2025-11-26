<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectAgencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'agency_id' => $this->agency_id,

            // 🔥 Retorna el Project si viene cargado
            'project' => new ProjectResource(
                $this->whenLoaded('project')
            ),

            // 🔥 Retorna la Agency si viene cargada
            'agency' => new AgencyResource(
                $this->whenLoaded('agency')
            ),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'project_agency',
                'version' => '1.0',
            ],
        ];
    }
}
