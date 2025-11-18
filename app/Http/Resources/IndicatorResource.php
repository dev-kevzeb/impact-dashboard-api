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
            'type'=> [
                'id' => $this->type->id,
                'name' => $this->type->name,
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