<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDashboardRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mainIndicator = $this->indicators->first();
        $measure = $mainIndicator?->measure;
        $country = $measure?->strategicOutput?->countryKpa?->country;

        $leadManagerName = trim(implode(' ', array_filter([
            $this->contact?->first_name,
            $this->contact?->last_name,
        ])));

        $leadAgency = $this->agencies
            ->sortByDesc(fn($agency) => (float) ($agency->pivot?->contribution ?? 0))
            ->first();

        return [
            'id' => $this->id,
            'country' => $country?->name,
            'measure' => $measure?->name,
            'program_title' => $this->program?->name,
            'project_title' => $this->name,
            'lead_project_manager' => $leadManagerName !== '' ? $leadManagerName : null,
            'lead_implementing_agency' => $leadAgency?->name,
            'budget' => (float) $this->project_budget,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'progress' => (float) $this->progress,
            'weight' => (float) ($this->weight ?? 0),
            'comment' => $this->comments,
            'can_edit' => (bool) ($this->can_edit ?? false),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'project_dashboard_row',
                'version' => '1.0',
            ],
        ];
    }
}
