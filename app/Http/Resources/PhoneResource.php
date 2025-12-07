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
            'slug' => $this->slug,
            'status' => $this->status,
            'primary_image' => $this->primary_image ? $baseUrl . '/storage/' . ltrim($this->primary_image, '/')
                : null,
            'variants' => PhoneVariantResource::collection($this->whenLoaded('variants')),
            'colors' => ColorResource::collection($this->whenLoaded('colors')),
            'specifications' => PhoneSpecificationResource::collection($this->whenLoaded('specifications')),
            'searchIndex' => new PhoneSearchResource($this->whenLoaded('searchIndex')),
        ];
    }
}
