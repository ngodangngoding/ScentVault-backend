<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerfumeCollectionCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     id: int,
     *     name: string,
     *     user_perfumes_count: int,
     *     is_selected: bool
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'user_perfumes_count' => (int) $this->user_perfumes_count,
            'is_selected' => (bool) $this->is_selected,
        ];
    }
}