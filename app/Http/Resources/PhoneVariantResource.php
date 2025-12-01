<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneVariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pkr_price' => number_format($this->pkr_price, 0, '.', ','),
            'usd_price' => number_format($this->usd_price, 0, '.', ','),
            'storage' => $this->storage,
            'ram' => $this->ram
        ];
    }
}
