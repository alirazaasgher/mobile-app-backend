<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PhoneResource extends JsonResource
{
    public static $hideDetails;

    public function __construct($resource)
    {
        parent::__construct($resource);
    }
    public function toArray($request)
    {
        $baseUrl = "https://cdn.mobile42.com";
        //$baseUrl = "http://127.0.0.1:8000";
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'slug' => $this->slug,
            'updated_at' => !empty($this->updated_at) ? $this->updated_at->toDateString() : null,
            'release_date' => !empty($this->release_date) ? Carbon::parse($this->release_date)->format('j F, Y') : null,
            'status' => $this->status,
            'primary_image' => $this->primary_image ? $baseUrl . '/storage/' . ltrim($this->primary_image, '/')
                : null,
            // 'variants' => PhoneVariantResource::collection($this->whenLoaded('variants')),
            // 'colors' => ColorResource::collection($this->whenLoaded('colors')),
            // 'specifications' => PhoneSpecificationResource::collection($this->whenLoaded('specifications')),
            'searchIndex' => new PhoneSearchResource($this->whenLoaded('searchIndex'), hideDetails: false),
        ];
        if (self::$hideDetails) {
            $data['specs'] = $this->compare_specs;
            $data['searchIndex'] = new PhoneSearchResource($this->whenLoaded('searchIndex'), hideDetails: true, fromCompare: true);
        } else {
            $data['variants'] = PhoneVariantResource::collection($this->whenLoaded('variants'));
            $data['colors'] = ColorResource::collection($this->whenLoaded('colors'));
            $data['specifications'] = PhoneSpecificationResource::collection($this->whenLoaded('specifications'));
            $data['searchIndex'] = new PhoneSearchResource($this->whenLoaded('searchIndex'), hideDetails: false);
        }
        if ($request->query('details') || $request->routeIs('phones.show')) {
            $data['competitors'] = CompetitorResource::collection($this->whenLoaded('competitors'));
        }

        return $data;
    }
}
