<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerfumeSuitabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'perfume_id' => $this->perfume_id,
            'ideal_temperature' => $this->ideal_temperature,
            'ideal_time' => $this->ideal_time,
            'ideal_environment' => $this->ideal_environment
        ];
    }
}
