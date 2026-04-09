<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this['key'],
            'title' => $this['title'],
            'status' => $this['status'],
            'connected' => $this['connected'],
            'source' => $this['source'],
            'detail' => $this['detail'],
        ];
    }
}
