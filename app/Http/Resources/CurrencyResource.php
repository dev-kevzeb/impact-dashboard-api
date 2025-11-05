<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
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
            'code' => $this->code,
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
                'resource_type' => 'currency',
                'version' => '1.0',
            ],
        ];
    }
}
