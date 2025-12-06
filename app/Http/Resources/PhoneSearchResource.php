<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneSearchResource extends JsonResource
{
    public function toArray($request)
    {
        $ramOptions = json_decode($this->ram_options, true) ?: [];
        $storageOptions = json_decode($this->storage_options, true) ?: [];

        $numericValues = array_map(function ($value) {

            // If value is <= 10, treat as TB → convert to GB
            if ($value <= 10) {
                return $value * 1024;
            }

            // Otherwise treat as normal GB
            return (int) $value;
        }, $storageOptions);

        $minNumeric = min($numericValues);

        $data = [
            'ram' => !empty($ramOptions) ? min($ramOptions) : null,
            'storage' => $storageOptions[array_search($minNumeric, $numericValues)],
            'min_price' => !empty($this->min_price_pkr)
                ? number_format(min($this->min_price_pkr), 0, '.', ',')
                : null,  // or 'NA'
            'specs_grid' => json_decode($this->specs_grid, true),
        ];

        // Only include top_specs and specs_grid if request has details page flag
        if ($request->query('details') || $request->routeIs('phones.show')) {
            $data['top_specs'] = json_decode($this->top_specs, true);
            $data['specs_grid'] = json_decode($this->specs_grid, true);
        }

        return $data;
    }
}
