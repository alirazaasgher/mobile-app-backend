<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneImageResource extends JsonResource
{
    public function toArray($request)
    {
        $baseUrl = config('app.cdn_asset_url');
        return [
            'id' => $this->id,
            'url' => $this->image_url
                ? $baseUrl . '/' . ltrim($this->image_url, '/')
                : null,
            'sort' => $this->sort,
        ];
    }

}
