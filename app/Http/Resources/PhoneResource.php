<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneResource extends JsonResource
{
    public function toArray($request)
    {
        $baseUrl = config('app.url');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'model' => $this->model,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'primary_image' => $this->primary_image  ? $baseUrl . '/storage/' . ltrim($this->primary_image, '/')
                : null,
            'status' => $this->status,
            'announced_date' => $this->announced_date?->format('Y-m-d'),
            'release_date' => $this->release_date?->format('Y-m-d'),
            'popularity_score' => $this->popularity_score,
            'variants' => PhoneVariantResource::collection($this->whenLoaded('variants')),
            'colors' => ColorResource::collection($this->whenLoaded('colors')),
            'specifications' => PhoneSpecificationResource::collection($this->whenLoaded('specifications')),
            'searchIndex' => new PhoneSearchResource($this->whenLoaded('searchIndex')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
