<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryKpaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_ck' => $this->id,
            'id_kpa' => $this->kpa->id,
            'name' => $this->kpa->name,
            'implementation' => (float) $this->kpa->implementation,
            'strategic_outputs_count' => $this->strategic_outputs_count,
            'measures_count' => $this->measures_count,
            'indicators_count' => $this->indicators_count,
        ];
    }
}
