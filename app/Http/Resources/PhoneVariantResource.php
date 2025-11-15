<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneVariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'price' => number_format($this->price, 0, '.', ','),
            'storage' => $this->storage,
            'ram' => $this->ram
        ];
    }
}