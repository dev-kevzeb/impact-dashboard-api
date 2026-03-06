<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get countries assigned through user roles
        $countries = [];
        if ($this->relationLoaded('userRoles')) {
            $countries = $this->userRoles
                ->filter(fn($userRole) => $userRole->relationLoaded('countries'))
                ->flatMap(fn($userRole) => $userRole->countries)
                ->unique('id')
                ->map(fn($country) => [
                    'id' => $country->id,
                    'name' => $country->name,
                ])
                ->values()
                ->toArray();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            // All effective permissions (direct + from roles)
            // This matches JWT scopes and shows what user can actually do
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray(),

            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'userState' => new UserStateResource($this->whenLoaded('userState')),
            'countries' => $countries,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
