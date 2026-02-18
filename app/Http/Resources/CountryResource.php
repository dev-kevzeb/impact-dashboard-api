<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,

            'currency' => [
                'id'   => $this->currency->id,
                'code' => $this->currency->code,
            ],

            'kpas_count' => $this->when( isset($this->country_kpas_count),$this->country_kpas_count),
            'strategic_outputs_count' => $this->when(isset($this->strategic_outputs_count),$this->strategic_outputs_count),
            'measures_count' => $this->when( isset($this->measures_count), $this->measures_count),
            'indicators_count' => $this->when( isset($this->indicators_count), $this->indicators_count),
        ];
    }

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
