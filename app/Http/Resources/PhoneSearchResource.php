<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneSearchResource extends JsonResource
{

    protected $hideDetails;
    protected $fromCompare;

    public function __construct($resource, $hideDetails = false, $fromCompare = false)
    {
        parent::__construct($resource);
        $this->hideDetails = $hideDetails;
        $this->fromCompare = $fromCompare;
    }
    public function toArray($request)
    {
        $ramOptions = json_decode($this->ram_options, true) ?: [];
        $storageOptions = json_decode($this->storage_options, true) ?: [];
        $numericValues = array_map(function ($value) {
            // If value is <= 10, treat as TB → convert to GB
            if ($value <= 10) {
                return $value * 1024;
            }
            return (int) $value; // Otherwise treat as GB
        }, $storageOptions ?? []);

        // Get min value safely
        $minNumeric = !empty($numericValues) ? min($numericValues) : null;

        // Find original storage value safely
        $minIndex = $minNumeric !== null ? array_search($minNumeric, $numericValues, true) : false;
        $minStorage = $minIndex !== false ? $storageOptions[$minIndex] : null;

        $data = [
            'ram' => !empty($ramOptions) ? min($ramOptions) : null,
            'storage' => $minStorage,
            'min_price_usd' => $this->min_price_usd,
        ];
        if (!$this->fromCompare) {
            $data['specs_grid'] = json_decode($this->specs_grid, true);
            $data['min_price'] = isset($this->min_price_pkr) && $this->min_price_pkr > 0
                ? number_format($this->min_price_pkr, 0, '.', ',')
                : null;
        }
        // !$this->hideDetails &&
        // Only include top_specs and specs_grid if request has details page flag
        if (!$this->hideDetails && ($request->query('details') || $request->routeIs('phones.show'))) {
            $data['top_specs'] = json_decode($this->top_specs, true);

        }

        return $data;
    }
}
