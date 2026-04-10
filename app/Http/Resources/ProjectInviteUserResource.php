<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectInviteUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'country_user_role_id' => $this->country_user_role_id,
            'project' => new SimpleProjectResource($this->whenLoaded('project')),
            'country_user_role' => $this->whenLoaded('countryUserRole', function () {
                return [
                    'id' => $this->countryUserRole->id,
                    'country' => $this->countryUserRole->country ? [
                        'id' => $this->countryUserRole->country->id,
                        'name' => $this->countryUserRole->country->name,
                    ] : null,
                ];
            }),
        ];
    }
}