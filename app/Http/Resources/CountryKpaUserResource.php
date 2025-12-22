<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryKpaUserResource extends JsonResource
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
            'country_kpa' => [
                'id' => $this->countryKpa->id,
                'country' => $this->countryKpa->country?->getName() ?? 'N/A',
                'country_id' => $this->countryKpa->country?->id,
                'kpa' => $this->countryKpa->kpa?->getName() ?? 'N/A',
                'kpa_id' => $this->countryKpa->kpa?->id,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->getName(),
                'email' => $this->user->getEmail(),
                'role' => $this->user->role?->getName() ?? 'N/A',
                'role_id' => $this->user->role?->id,
                'state' => $this->user->userState?->getName() ?? 'N/A',
                'state_id' => $this->user->userState?->id,
            ],
            'assigned_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
