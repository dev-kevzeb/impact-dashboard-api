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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            // All effective permissions (direct + from roles)
            // This matches JWT scopes and shows what user can actually do
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray(),

            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'userState' => new UserStateResource($this->whenLoaded('userState')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
