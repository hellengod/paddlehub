<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\River */
class RiverResource extends JsonResource
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
            'city' => $this->city,
            'state' => $this->state,
            'difficultyClass' => $this->difficulty_class,
            'description' => $this->description,
            'startLatitude' => (float) $this->start_latitude,
            'startLongitude' => (float) $this->start_longitude,
            'endLatitude' => $this->end_latitude !== null ? (float) $this->end_latitude : null,
            'endLongitude' => $this->end_longitude !== null ? (float) $this->end_longitude : null,
            'extensionKm' => $this->resource->extensionKm(),
            'createdBy' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
