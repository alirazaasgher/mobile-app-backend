<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PhoneResource extends JsonResource
{
    public function toArray($request)
    {
        $baseUrl = config('app.url');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'slug' => $this->slug,
            'release_date' => $this->release_date,
            'status' => $this->status,
                ? Carbon::parse($this->release_date)->format('Y-m-d')
                : null,
            'tagline' => $this->tagline,
            'primary_image' => $this->primary_image ? $baseUrl . '/storage/' . ltrim($this->primary_image, '/')
                : null,
            'is_popular' => $this->is_popular,
            'variants' => PhoneVariantResource::collection($this->whenLoaded('variants')),
            'colors' => ColorResource::collection($this->whenLoaded('colors')),
            'specifications' => PhoneSpecificationResource::collection($this->whenLoaded('specifications')),
            'searchIndex' => new PhoneSearchResource($this->whenLoaded('searchIndex')),
        ];
    }
}
