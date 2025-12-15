<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PhoneResource extends JsonResource
{
    public function toArray($request)
    {
        $baseUrl = "https://cdn.mobile42.com";
        //$baseUrl = "http://127.0.0.1:8000";
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'slug' => $this->slug,
            'updated_at' => $this->updated_at->toDateString() ?? null,
            'release_date' => !empty($this->release_date) ? Carbon::parse($this->release_date)->format('j F, Y') : null,
            'status' => $this->status,
            'primary_image' => $this->primary_image ? $baseUrl . '/storage/' . ltrim($this->primary_image, '/')
                : null,
            // 'is_popular' => $this->is_popular,
            'variants' => PhoneVariantResource::collection($this->whenLoaded('variants')),
            'colors' => ColorResource::collection($this->whenLoaded('colors')),
            'specifications' => PhoneSpecificationResource::collection($this->whenLoaded('specifications')),
            'searchIndex' => new PhoneSearchResource($this->whenLoaded('searchIndex'), hideDetails: false),
        ];
        if ($request->query('details') || $request->routeIs('phones.show')) {
            $data['competitors'] = CompetitorResource::collection($this->whenLoaded('competitors'));
        }

        return $data;
    }
}
