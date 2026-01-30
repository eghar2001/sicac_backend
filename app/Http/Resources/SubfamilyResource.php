<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubfamilyResource extends JsonResource
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
            'family_id' => $this->family_id,
            'name' => $this->name,
            'work_cost_points' => $this->work_cost_points,
            'family' => FamilyResource::make($this->whenLoaded('family')),
        ];
    }
}
