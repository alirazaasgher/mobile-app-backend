<?php

namespace App\Services;

use App\Models\Phone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PhoneFilterService
{
    /**
     * Get all available filter options
     */
    public function getAllFilterOptions(): array
    {
        return Cache::remember('all_filter_options', 3600, function () {
            return [
                'brands' => $this->getBrandsWithCounts(),
                'price_range' => $this->getPriceRange(),
                'ram_options' => $this->getRamOptions(),
                'storage_options' => $this->getStorageOptions(),
                'screen_sizes' => $this->getScreenSizes(),
                'battery_ranges' => $this->getBatteryRanges(),
                'features' => $this->getFeatureOptions(),
                'camera_options' => $this->getCameraOptions(),
                'os_options' => $this->getOSOptions(),
                'years' => $this->getYearOptions()
            ];
        });
    }

    /**
     * Get brands with phone counts
     */
    public function getBrandsWithCounts(): array
    {
        return Cache::remember('brands_with_counts', 1800, function () {
            return Phone::select('brand', DB::raw('count(*) as count'))
                ->where('status', 'active')
                ->groupBy('brand')
                ->orderBy('count', 'desc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get current price range
     */
    public function getPriceRange(): array
    {
        return Cache::remember('price_range', 1800, function () {
            return [
                'min' => Phone::join('phone_search_index', 'phones.id', '=', 'phone_search_index.phone_id')
                    ->where('phones.status', 'active')
                    ->min('phone_search_index.min_price'),
                'max' => Phone::join('phone_search_index', 'phones.id', '=', 'phone_search_index.phone_id')
                    ->where('phones.status', 'active')
                    ->max('phone_search_index.max_price')
            ];
        });
    }

    /**
     * Get specification-based filters
     */
    public function getSpecificationFilters(): array
    {
        return Cache::remember('spec_filters', 1800, function () {
            return [
                'ram' => $this->getRamOptions(),
                'storage' => $this->getStorageOptions(),
                'screen_sizes' => $this->getScreenSizes(),
                'refresh_rates' => [60, 90, 120, 144, 165],
                'display_types' => $this->getDisplayTypes(),
                'operating_systems' => $this->getOSOptions(),
                'connectivity' => $this->getConnectivityOptions()
            ];
        });
    }

    /**
     * Get RAM options
     */
    private function getRamOptions(): array
    {
        return DB::table('phone_search_index')
            ->join('phones', 'phone_search_index.phone_id', '=', 'phones.id')
            ->where('phones.status', 'active')
            ->distinct()
            ->orderBy('ram_gb')
            ->pluck('ram_gb')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get storage options
     */
    private function getStorageOptions(): array
    {
        return DB::table('phone_storage_options')
            ->join('phones', 'phone_storage_options.phone_id', '=', 'phones.id')
            ->where('phones.status', 'active')
            ->where('phone_storage_options.is_available', true)
            ->distinct()
            ->orderBy('size_gb')
            ->pluck('size_gb')
            ->toArray();
    }

    /**
     * Get screen sizes
     */
    private function getScreenSizes(): array
    {
        return DB::table('phone_search_index')
            ->join('phones', 'phone_search_index.phone_id', '=', 'phones.id')
            ->where('phones.status', 'active')
            ->distinct()
            ->orderBy('screen_size')
            ->pluck('screen_size')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get battery ranges
     */
    private function getBatteryRanges(): array
    {
        return [
            '3000-4000' => '3000-4000 mAh',
            '4000-5000' => '4000-5000 mAh',
            '5000-6000' => '5000-6000 mAh',
            '6000+' => '6000+ mAh'
        ];
    }

    /**
     * Get feature options
     */
    private function getFeatureOptions(): array
    {
        return [
            'has_5g' => '5G Support',
            'has_nfc' => 'NFC',
            'has_wireless_charging' => 'Wireless Charging',
            'has_fast_charging' => 'Fast Charging',
            'has_fingerprint' => 'Fingerprint Scanner',
            'has_face_unlock' => 'Face Unlock',
            'water_resistant' => 'Water Resistant',
            'dual_sim' => 'Dual SIM',
            'expandable_storage' => 'Expandable Storage'
        ];
    }

    /**
     * Get camera options
     */
    private function getCameraOptions(): array
    {
        return [
            'megapixels' => [12, 48, 50, 64, 108, 200],
            'features' => [
                'ultra_wide' => 'Ultra Wide',
                'telephoto' => 'Telephoto',
                'macro' => 'Macro',
                'depth' => 'Depth Sensor',
                'periscope' => 'Periscope Zoom',
                'ois' => 'Optical Image Stabilization'
            ]
        ];
    }

    /**
     * Get OS options
     */
    private function getOSOptions(): array
    {
        return DB::table('phone_specifications')
            ->join('phones', 'phone_specifications.phone_id', '=', 'phones.id')
            ->where('phones.status', 'active')
            ->where('phone_specifications.category', 'platform')
            ->distinct()
            ->get()
            ->pluck('spec_data')
            ->map(function ($data) {
                $decoded = json_decode($data, true);
                return $decoded['os'] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get display types
     */
    private function getDisplayTypes(): array
    {
        return [
            'AMOLED' => 'AMOLED',
            'Super AMOLED' => 'Super AMOLED',
            'Dynamic AMOLED' => 'Dynamic AMOLED',
            'OLED' => 'OLED',
            'IPS LCD' => 'IPS LCD',
            'LCD' => 'LCD'
        ];
    }

    /**
     * Get connectivity options
     */
    private function getConnectivityOptions(): array
    {
        return [
            'wifi_standards' => ['Wi-Fi 6', 'Wi-Fi 6E', 'Wi-Fi 7'],
            'bluetooth_versions' => ['5.0', '5.1', '5.2', '5.3', '5.4'],
            'usb_types' => ['USB-C', 'Lightning', 'Micro USB']
        ];
    }

    /**
     * Get year options
     */
    private function getYearOptions(): array
    {
        return Phone::whereNotNull('release_date')
            ->where('status', 'active')
            ->selectRaw('YEAR(release_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }
}