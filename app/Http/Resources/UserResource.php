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

        // Resolve country_user_role via direct query (same as getCountryUserRole()).
        // This works for all user types regardless of which relations were eager-loaded.
        $countryUserRole = null;
        $cur = \App\Modules\CountryUserRole\Domain\CountryUserRole::whereHas(
            'userRole', fn($q) => $q->where('user_id', $this->id)
        )->with(['country', 'userRole.role'])->first();

        if ($cur) {
            $role = ($cur->userRole && $cur->userRole->role)
                ? ['id' => $cur->userRole->role->id, 'name' => $cur->userRole->role->name]
                : null;

            $countryUserRole = [
                'id'      => $cur->id,
                'country' => $cur->country ? [
                    'id'     => $cur->country->id,
                    'name'   => $cur->country->name,
                    'active' => (bool) $cur->country->active,
                ] : null,
                'role' => $role,
            ];
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
