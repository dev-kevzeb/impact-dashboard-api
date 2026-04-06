<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicatorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            'name' => $this->name,
            'target' => $this->target,
            'actual_value' => $this->actual_value,
            'measure_id' => $this->measure_id,
            'type'=> [
                'id' => $this->type->id,
                'name' => $this->type->name,
                'is_bottom_up' => (bool) $this->type->is_bottom_up,
            ],
        ];
    }

     public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'indicator',
                'version' => '1.0',
            ],
        ];
    }
}