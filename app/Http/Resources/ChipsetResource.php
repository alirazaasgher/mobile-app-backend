<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChipsetResource extends JsonResource
{
    public function toArray($request)
    {
        $baseUrl = config('app.cdn_asset_url');
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'primary_image' => $this->primary_image ? $baseUrl . '/' . ltrim($this->primary_image, '/')
                : null,
            'tier' => $this->tier,
            'announced_year' => $this->announced_year,
            'brand' => $this->brand->name,
            'brand_slug' => $this->brand->slug,
        ];
    }
}
