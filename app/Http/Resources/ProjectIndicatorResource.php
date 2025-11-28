<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectIndicatorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'indicator_id' => $this->indicator_id,

            'project' => new ProjectResource(
                $this->whenLoaded('project')
            ),

            'indicator' => new IndicatorResource(
                $this->whenLoaded('indicator')
            ),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'project_indicator',
                'version' => '1.0',
            ],
        ];
    }
}