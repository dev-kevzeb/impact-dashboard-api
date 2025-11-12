<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'title' => $this->title,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }

    /**
     * Customize the response for a collection of resources
     *
     * @param Request $request
     * @param mixed $paginated
     * @param mixed $default
     * @return array
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'contact',
                'version' => '1.0',
            ],
        ];
    }
}
