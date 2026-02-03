<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            'currency' => [
                'id' => $this->currency->id,
                'code' => $this->currency->code,
            ],
            'kpas_count' => $this->when(isset($this->kpas_count), $this->kpas_count),
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
                'resource_type' => 'country',
                'version' => '1.0',
            ],
        ];
    }
}
