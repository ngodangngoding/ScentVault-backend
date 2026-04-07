<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerfumeResource extends JsonResource
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
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'concentration' => $this->concentration,
            'description' => $this->description,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'star_rating' => $this->star_rating,
            'notes' => PerfumeNoteResource::collection($this->whenLoaded('perfumeNote')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
