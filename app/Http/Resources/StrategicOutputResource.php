<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StrategicOutputResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,

            'country_kpa' => [
                'id' => $this->countryKpa->id,

                'country' => [
                    'id'   => $this->countryKpa->country->id,
                    'name' => $this->countryKpa->country->name,
                ],

                'kpa' => [
                    'id'             => $this->countryKpa->kpa->id,
                    'name'           => $this->countryKpa->kpa->name,
                    'implementation' => $this->countryKpa->kpa->implementation,
                ],
            ],

            // Igual que MeasureResource maneja counters
            'measures'        => MeasureResource::collection($this->whenLoaded('measures')),
            'measures_count'  => $this->when(isset($this->measures_count), $this->measures_count),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'strategic_output',
                'version' => '1.0',
            ],
        ];
    }
}
