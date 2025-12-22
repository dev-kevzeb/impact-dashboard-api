<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRoleResource extends JsonResource
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
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->getName(),
                'email' => $this->user->getEmail(),
                'state' => $this->user->userState?->getName() ?? 'N/A',
                'state_id' => $this->user->userState?->id,
            ],
            'role' => [
                'id' => $this->role->id,
                'name' => $this->role->getName(),
            ],
        ];
    }
}
