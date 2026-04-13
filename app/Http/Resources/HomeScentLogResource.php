<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeScentLogResource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     weather: string,
     *     title: string|null,
     *     occasion: array{
     *         id: int|null,
     *         name: string|null
     *     },
     *     input_date: string|null,
     *     notes_review: string|null
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'weather' => (string) $this->weather,
            'title' => $this->perfume?->name,
            'occasion' => [
                'id' => $this->occasion?->id,
                'name' => $this->occasion?->name,
            ],
            'input_date' => $this->created_at?->toISOString(),
            'notes_review' => $this->notes_review,
        ];
    }
}
