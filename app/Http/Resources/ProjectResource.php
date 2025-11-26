<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'project_budget' => $this->project_budget,
            'contact_id' => $this->contact_id,
            'beneficiary_id' => $this->beneficiary_id,
            'project_state_id' => $this->project_state_id,
            'donors' => DonorResource::collection(
                $this->whenLoaded('donors')
            ),
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