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
            'updated_at' => $this->updated_at ?
                Carbon::parse($this->updated_at)->format('Y-m-d')
                : null,
            'release_date' => !empty($this->release_date) ? Carbon::parse($this->release_date)->format('j F, Y') : null,
            'status' => $this->status,
            'primary_color' => $this->primary_color ?? null,
            'primary_image' => $this->primary_image ? $baseUrl . '/' . ltrim($this->primary_image, '/')
                : null,
            'searchIndex' => $this->searchIndex
        ];
        if (self::$hideDetails) {
            if ($this->includeSpec) {
                $data['specs'] = $this->compare_specs;
            }
            // $data['competitors'] = CompetitorResource::collection($this->whenLoaded('competitors'));
        }
        if ($request->query('details') || $request->routeIs('phones.show')) {
            $data['competitors'] = CompetitorResource::collection($this->whenLoaded('competitors'));
            $data['searchIndex'] = new PhoneSearchResource($this->whenLoaded('searchIndex'), hideDetails: false);
            $data['variants'] = PhoneVariantResource::collection($this->whenLoaded('variants'));
            $data['colors'] = ColorResource::collection($this->whenLoaded('colors'));
            $data['specifications'] = PhoneSpecificationResource::collection($this->whenLoaded('specifications'));
        }

        return $data;
    }
}
