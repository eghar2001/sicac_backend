<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'external_id' => $this->external_id,
            'name' => $this->name,
            'brand_id' => $this->brand_id,
            'subfamily_id' => $this->subfamily_id,
            'category_id' => $this->category_id,
            'model_sku' => $this->model_sku,
            'price_ars' => $this->price_ars,
            'description' => $this->description,
            'technical_specs' => $this->technical_specs,
            'source_specs' => $this->source_specs,
            'brand' => BrandResource::make($this->whenLoaded('brand')),
            'subfamily' => SubfamilyResource::make($this->whenLoaded('subfamily')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
