<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneVariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pkr_price' => number_format($this->price_modifier_pkr, 0, '.', ','),
            'price_usd' => number_format($this->price_modifier_usd, 0, '.', ','),
            'storage' => $this->storage,
            'ram' => $this->ram
        ];
    }
}
