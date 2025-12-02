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
            if (str_contains($value, 'TB')) {
                return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT) * 1024; // Convert TB → GB
            }
            return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        }, $storageOptions);

        $minNumeric = min($numericValues);
        $data = [
            'ram' => !empty($ramOptions) ? min($ramOptions) : null,
            'storage' => $storageOptions[array_search($minNumeric, $numericValues)],
            'min_price' => number_format($this->min_price_pkr, 0, '.', ','),
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
