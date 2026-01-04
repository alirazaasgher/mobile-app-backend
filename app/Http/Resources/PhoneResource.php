<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PhoneResource extends JsonResource
{
    public static $hideDetails;
    protected $includeSpec = false;
    public function __construct($resource, $includeSpec = false)
    {
        parent::__construct($resource);
        $this->includeSpec = $includeSpec;
    }
    public function toArray($request)
    {
        $baseUrl = config('app.cdn_asset_url');

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'slug' => $this->slug,
            'updated_at' => !empty($this->updated_at) ? $this->updated_at->toDateString() : null,
            'release_date' => !empty($this->release_date) ? Carbon::parse($this->release_date)->format('j F, Y') : null,
            'status' => $this->status,
            'primary_image' => $this->primary_image ? $baseUrl . '/' . ltrim($this->primary_image, '/')
                : null,
            'searchIndex' => new PhoneSearchResource($this->whenLoaded('searchIndex'), hideDetails: false),
        ];
        if (self::$hideDetails) {
            if ($this->includeSpec) {
                $data['specs'] = $this->compare_specs;
            }

            $data['searchIndex'] = new PhoneSearchResource($this->whenLoaded('searchIndex'), hideDetails: true, fromCompare: true);
            $data['competitors'] = CompetitorResource::collection($this->whenLoaded('competitors'));
        } else {
            $data['variants'] = PhoneVariantResource::collection($this->whenLoaded('variants'));
            $data['colors'] = ColorResource::collection($this->whenLoaded('colors'));
            $data['specifications'] = PhoneSpecificationResource::collection($this->whenLoaded('specifications'));
        }
        if ($request->query('details') || $request->routeIs('phones.show')) {
            $data['competitors'] = CompetitorResource::collection($this->whenLoaded('competitors'));
            $data['description'] = $this->description;
        }

        return $data;
    }
}
