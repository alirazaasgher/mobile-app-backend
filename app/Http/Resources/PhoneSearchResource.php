<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneSearchResource extends JsonResource
{
    public function toArray($request)
    {
          $ramOptions = json_decode($this->ram_options, true) ?: [];
    $storageOptions = json_decode($this->storage_options, true) ?: [];
        return [
          
            // 'available_colors' => $this->available_colors,
             'ram' => min($ramOptions),           // minimum RAM
        'storage' => min($storageOptions), 
            'min_price' => number_format($this->min_price, 0, '.', ','),
        ];
    }
}