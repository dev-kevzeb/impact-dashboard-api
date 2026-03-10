<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'banner_img' => $this->banner_img,
            'program_url' => $this->program_url,
            'projects_count' => $this->when(isset($this->projects_count), $this->projects_count),
            'program_summary' => $this->when(isset($this->program_summary), $this->program_summary),
            
            // Relaciones cargadas (si existen)
            'contact' => $this->whenLoaded('contact', function () {
                return new ContactResource($this->contact);
            }),
            
            'program_state' => $this->whenLoaded('programState', function () {
                return new ProgramStateResource($this->programState);
            }),
            
            'sdgs' => $this->whenLoaded('sdgs', function () {
                return SdgResource::collection($this->sdgs);
            }),
            
            'projects' => $this->whenLoaded('projects', function () {
                return ProjectResource::collection($this->projects);
            }),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'program',
                'version' => '1.0',
            ],
        ];
    }
}
