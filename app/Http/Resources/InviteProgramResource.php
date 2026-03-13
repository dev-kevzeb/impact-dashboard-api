<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InviteProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'program_country_user_role_id' => $this->program_country_user_role_id,
            'invited_user_role_id' => $this->invited_user_role_id,

            'program_country_user_role' => $this->whenLoaded('programCountryUserRole', function () {
                return [
                    'id' => $this->programCountryUserRole->id,
                    'program' => new ProgramResource($this->programCountryUserRole->program),
                    'country_user_role' => [
                        'id' => $this->programCountryUserRole->countryUserRole?->id,
                        'country' => $this->programCountryUserRole->countryUserRole?->country ? [
                            'id' => $this->programCountryUserRole->countryUserRole->country->id,
                            'name' => $this->programCountryUserRole->countryUserRole->country->name,
                        ] : null,
                    ],
                ];
            }),

            'invited_user_role' => $this->whenLoaded('invitedUserRole', function () {
                return [
                    'id' => $this->invitedUserRole->id,
                    'user' => $this->invitedUserRole->user ? [
                        'id' => $this->invitedUserRole->user->id,
                        'name' => $this->invitedUserRole->user->name,
                        'email' => $this->invitedUserRole->user->email,
                    ] : null,
                    'role' => $this->invitedUserRole->role ? [
                        'id' => $this->invitedUserRole->role->id,
                        'name' => $this->invitedUserRole->role->name,
                    ] : null,
                ];
            }),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
