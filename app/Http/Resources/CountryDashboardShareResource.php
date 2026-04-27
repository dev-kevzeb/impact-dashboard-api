<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryDashboardShareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'country_id' => $this->country_id,
            'owner_country_user_role_id' => $this->owner_country_user_role_id,
            'shared_user_role_id' => $this->shared_user_role_id,
            'country' => $this->whenLoaded('country', function () {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                ];
            }),
            'owner_country_user_role' => $this->whenLoaded('ownerCountryUserRole', function () {
                return [
                    'id' => $this->ownerCountryUserRole->id,
                    'country' => $this->ownerCountryUserRole->country ? [
                        'id' => $this->ownerCountryUserRole->country->id,
                        'name' => $this->ownerCountryUserRole->country->name,
                    ] : null,
                    'user' => $this->ownerCountryUserRole->userRole && $this->ownerCountryUserRole->userRole->user ? [
                        'id' => $this->ownerCountryUserRole->userRole->user->id,
                        'name' => $this->ownerCountryUserRole->userRole->user->name,
                        'email' => $this->ownerCountryUserRole->userRole->user->email,
                    ] : null,
                ];
            }),
            'shared_user_role' => $this->whenLoaded('sharedUserRole', function () {
                return [
                    'id' => $this->sharedUserRole->id,
                    'role' => $this->sharedUserRole->role ? [
                        'id' => $this->sharedUserRole->role->id,
                        'name' => $this->sharedUserRole->role->name,
                    ] : null,
                    'user' => $this->sharedUserRole->user ? [
                        'id' => $this->sharedUserRole->user->id,
                        'name' => $this->sharedUserRole->user->name,
                        'email' => $this->sharedUserRole->user->email,
                    ] : null,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
