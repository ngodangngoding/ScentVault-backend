<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPerfumeResource extends JsonResource
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
            'brand_name' => $this->brand->name,
            'category_name' => $this->category?->name,
            'concentration' => $this->concentration,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'star_rating' => $this->pivot->star_rating,
            'notes' => PerfumeNoteResource::collection($this->whenLoaded('perfumeNote')),
            'suitability' => new PerfumeSuitabilityResource($this->whenLoaded('suitability')),
            'created_at' => $this->pivot->created_at ?? $this->created_at,
            'deleted_at' => $this->pivot->deleted_at ?? $this->deleted_at
        ];
    }
}
