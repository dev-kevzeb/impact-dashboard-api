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
            'user_role' => [
                'id' => $this->userRole->id,
                'user' => [
                    'id' => $this->userRole->user->id,
                    'name' => $this->userRole->user->getName(),
                    'email' => $this->userRole->user->getEmail(),
                    'state' => $this->userRole->user->userState?->getName() ?? 'N/A',
                ],
                'role' => [
                    'id' => $this->userRole->role->id,
                    'name' => $this->userRole->role->getName(),
                ],
            ],
        ];
    }
}
