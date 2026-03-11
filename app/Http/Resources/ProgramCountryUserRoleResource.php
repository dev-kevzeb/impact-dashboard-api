<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramCountryUserRoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'program_id'           => $this->program_id,
            'country_user_role_id' => $this->country_user_role_id,

            'program' => $this->whenLoaded('program', function () {
                return new ProgramResource($this->program);
            }),

            'country_user_role' => $this->whenLoaded('countryUserRole', function () {
                $cur = $this->countryUserRole;
                $role = ($cur->relationLoaded('userRole') && $cur->userRole?->relationLoaded('role') && $cur->userRole->role)
                    ? ['id' => $cur->userRole->role->id, 'name' => $cur->userRole->role->name]
                    : null;
                return [
                    'id'      => $cur->id,
                    'country' => $cur->relationLoaded('country') ? [
                        'id'   => $cur->country->id,
                        'name' => $cur->country->name,
                    ] : null,
                    'role'    => $role,
                ];
            }),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
