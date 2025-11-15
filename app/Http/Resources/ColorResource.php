<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ColorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'hex' => $this->hex_code,
            'images' => PhoneImageResource::collection($this->whenLoaded('images')),
        ];
    }
}