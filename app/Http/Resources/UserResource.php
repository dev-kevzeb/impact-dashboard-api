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
        $adminUserRoleId = $this->userRoles()
            ->whereHas('role', fn($q) => $q->where('name', 'admin'))
            ->value('id');

        // Get country_user_role from userRoles relation
        $countryUserRole = null;
        if ($this->relationLoaded('userRoles')) {
            foreach ($this->userRoles as $userRole) {
                if ($userRole->relationLoaded('countryUserRole') && $userRole->countryUserRole) {
                    $cur = $userRole->countryUserRole;
                    $role = ($cur->relationLoaded('userRole') && $cur->userRole?->relationLoaded('role') && $cur->userRole->role)
                        ? ['id' => $cur->userRole->role->id, 'name' => $cur->userRole->role->name]
                        : null;
                    $countryUserRole = [
                        'id'      => $cur->id,
                        'country' => ($cur->relationLoaded('country') && $cur->country) ? [
                            'id'   => $cur->country->id,
                            'name' => $cur->country->name,
                        ] : null,
                        'role'    => $role,
                    ];
                    break;
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'user_role_id' => (int) $adminUserRoleId,

            // All effective permissions (direct + from roles)
            // This matches JWT scopes and shows what user can actually do
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray(),

            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'userState' => new UserStateResource($this->whenLoaded('userState')),
            'country_user_role' => $countryUserRole,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
