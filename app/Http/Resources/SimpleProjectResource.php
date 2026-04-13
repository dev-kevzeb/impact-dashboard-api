<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use App\Http\Resources\ProjectStateResource;

class SimpleProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'project_url' => $this->project_url,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'progress' => $this->progress,
            'comments'=> $this->comments,
            'budget' => $this->project_budget,
            'weight' => $this->weight,
            
            'project_state' => new ProjectStateResource($this->whenLoaded('projectState')),

            'program_id' => $this->program_id,
            'can_edit' => (bool) ($this->can_edit ?? false),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'project',
                'version' => '1.0',
            ],
        ];
    }
}