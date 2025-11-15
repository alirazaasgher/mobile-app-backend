<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneColorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'hex_code' => $this->hex_code,
            'price' => $this->price,
            'is_available' => $this->is_available,
            'images' => $this->whenLoaded('images', function() {
                return $this->images->pluck('image_url');
            }),
        ];
    }
}