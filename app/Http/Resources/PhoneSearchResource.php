<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneSearchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
          
            'available_colors' => $this->available_colors,
            'ram' => $this->ram_options,
            'storage' => $this->storage_options,
            'min_price' => number_format($this->min_price, 0, '.', ','),
        ];
    }
}