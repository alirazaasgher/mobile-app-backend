<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneImageResource extends JsonResource
{
    public function toArray($request)
    {
        //$baseUrl = "http://127.0.0.1:8000";
        $baseUrl = "https://cdn.mobile42.com";
        return [
            'id' => $this->id,
            'url' => $this->image_url
                ? $baseUrl . '/storage/' . ltrim($this->image_url, '/')
                : null,
            'sort' => $this->sort,
        ];
    }

}
