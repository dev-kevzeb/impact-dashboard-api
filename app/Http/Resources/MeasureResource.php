<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeasureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name, 
            'numbering' => $this->when(isset($this->numbering), $this->numbering),

            'indicators' => IndicatorResource::collection($this->whenLoaded('indicators')),
            'indicators_count' => $this->when(isset($this->indicators_count), $this->indicators_count),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'measure',
                'version' => '1.0',
            ],
        ];
    }
}
