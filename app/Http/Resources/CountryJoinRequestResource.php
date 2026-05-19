<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryJoinRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'country_id' => $this->country_id,
            'requester_user_role_id' => $this->requester_user_role_id,
            'status' => $this->status,
            'country' => $this->whenLoaded('country', function () {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                    'active' => (bool) $this->country->active,
                ];
            }),
            'requester_user_role' => $this->whenLoaded('requesterUserRole', function () {
                return [
                    'id' => $this->requesterUserRole->id,
                    'role' => $this->requesterUserRole->role ? [
                        'id' => $this->requesterUserRole->role->id,
                        'name' => $this->requesterUserRole->role->name,
                    ] : null,
                    'user' => $this->requesterUserRole->user ? [
                        'id' => $this->requesterUserRole->user->id,
                        'name' => $this->requesterUserRole->user->name,
                        'email' => $this->requesterUserRole->user->email,
                    ] : null,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
