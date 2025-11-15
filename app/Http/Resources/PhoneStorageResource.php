<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneStorageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'size_gb' => $this->size_gb,
            'price' => $this->price,
            'is_available' => $this->is_available,
        ];
    }
}