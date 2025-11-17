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
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'program_url' => $this->program_url,
            
            // Relaciones cargadas (si existen)
            'contact' => $this->whenLoaded('contact', function () {
                return new ContactResource($this->contact);
            }),
            
            'beneficiary' => $this->whenLoaded('beneficiary', function () {
                return new BeneficiaryResource($this->beneficiary);
            }),
            
            'program_state' => $this->whenLoaded('programState', function () {
                return new ProgramStateResource($this->programState);
            }),
            
            'country' => $this->whenLoaded('country', function () {
                return new CountryResource($this->country);
            }),
            
            'agency' => $this->whenLoaded('agency', function () {
                return new AgencyResource($this->agency);
            }),
            
            'sdgs' => $this->whenLoaded('sdgs', function () {
                return SdgResource::collection($this->sdgs);
            }),
            
            'donors' => $this->whenLoaded('donors', function () {
                return DonorResource::collection($this->donors);
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
