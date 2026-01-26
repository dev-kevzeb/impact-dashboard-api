<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ContactResource;
use App\Http\Resources\BeneficiaryResource;
use App\Http\Resources\ProjectStateResource;
use App\Http\Resources\DonorResource;
use App\Http\Resources\AgencyResource;
use App\Http\Resources\IndicatorResource;

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
            'budget' => $this->project_budget,

            'contact' => new ContactResource($this->whenLoaded('contact')),
            'beneficiary' => new BeneficiaryResource($this->whenLoaded('beneficiary')),
            'project_state' => new ProjectStateResource($this->whenLoaded('projectState')),

            'donors' => DonorResource::collection($this->whenLoaded('donors')),
            'agencies' => AgencyResource::collection($this->whenLoaded('agencies')),
            'indicators' => IndicatorResource::collection($this->whenLoaded('indicators')),

            'measure' => new MeasureResource($this->indicators->first()?->measure),
            'strategic_output' => new StrategicOutputResource($this->indicators->first()?->measure?->strategicOutput),
            'kpa' => new KpaResource($this->indicators->first()?->measure?->strategicOutput?->countryKpa?->kpa),

            'program_id' => $this->program_id
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
