<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Phone;
use App\Models\PhoneVariant;
use App\Models\PhoneSpecification;
use Artisan;

class PhoneSeeder extends Seeder
{
    public function run()
    {
        // Create Samsung Galaxy S24 Ultra
        $phone = Phone::create([
            'brand' => 'Samsung',
            'model' => 'Galaxy S24 Ultra',
            'name' => 'Samsung Galaxy S24 Ultra',
            'slug' => 'samsung-galaxy-s24-ultra',
            'tagline' => 'The ultimate Android flagship with AI-powered performance',
            'primary_image' => '/images/samsung-s24-ultra.jpg',
            'status' => 'active',
            'announced_date' => '2024-01-17',
            'release_date' => '2024-01-31',
            'popularity_score' => 95
        ]);

        // Add colors
        $colors = [
            ['color_name' => 'Nebula Black', 'color_slug' => 'nebula_black', 'color_hex' => '#282F38',"storage_size" => '256GB', "storage_gb" => 256, 'price' => 0, "sku" => "S24U-NebulaBlack-256GB"],
            ['color_name' => 'Aurora White', 'color_slug' => 'aurora_white', 'color_hex' => '#efefef',"storage_size" => '64GB', "storage_gb" => 64, 'price' => 0, "sku" => "S24U-AuroraWhite-256GB"],
            ['color_name' => 'Moon Titanium', 'color_slug' => 'moon_titanium', 'color_hex' => '#c3c0b8', 'price' => 50,"storage_size" => '128GB', "storage_gb" => 128, "sku" => "S24U-MoonTitanium-256GB"],
            ['color_name' => 'Tundra Green', 'color_slug' => 'tundra_green', 'color_hex' => '#d1e7c4', 'price' => 0,"storage_size" => '1TB', "storage_gb" => 1024, "sku" => "S24U-TundraGreen-256GB"],
        ];

        foreach ($colors as $colorData) {
            PhoneVariant::create(array_merge(['phone_id' => $phone->id], $colorData));
        }

        // Add specifications
        $this->addPhoneSpecifications($phone);
        // Update search index
        Artisan::call('phones:update-search-index', ['--phone_id' => $phone->id]);
    }

    private function addPhoneSpecifications($phone)
    {
        $specifications = [
            'general' => [
                'announced' => '2024, January 17',
                'status' => 'Available',
                'price_range' => '$1199 - $1499'
            ],
            'network' => [
                'technology' => 'GSM / CDMA / HSPA / EVDO / LTE / 5G',
                '5g_bands' => 'Sub-6GHz & mmWave'
            ],
            'display' => [
                'type' => 'Dynamic AMOLED 2X',
                'size_inches' => 6.8,
                'resolution' => 'QHD+ (1440 × 3120)',
                'refresh_rate' => '120Hz adaptive'
            ],
            'memory' => [
                'ram_gb' => 12,
                'storage_options' => [256, 512, 1024]
            ],
            'battery' => [
                'capacity_mah' => 5000,
                'wired_charging_watts' => 45,
                'wireless_charging_watts' => 15
            ],
            'connectivity' => [
                'wifi' => 'Wi-Fi 7',
                'bluetooth' => '5.3',
                'nfc' => true,
                'usb' => 'USB Type-C 3.2'
            ]
        ];

     foreach ($specifications as $category => $specs) {
    $searchableText = collect($specs)
        ->map(function ($value, $key) {
            if (is_array($value)) {
                $value = implode(' ', $value); // join array values into string
            } elseif (is_bool($value)) {
                $value = $value ? 'yes' : 'no'; // handle booleans
            }
            return "$key $value";
        })
        ->implode(', ');

    PhoneSpecification::create([
        'phone_id'        => $phone->id,
        'category'        => $category,
        'specifications'  => json_encode($specs),
        'searchable_text' => $searchableText,
    ]);
}
    }

    private function createPhoneVariants($phone)
    {
        $colors = $phone->colors;
        $storageOptions = $phone->storageOptions;

        foreach ($colors as $color) {
            foreach ($storageOptions as $storage) {
                PhoneVariant::create([
                    'phone_id' => $phone->id,
                    'color_id' => $color->id,
                    'storage_id' => $storage->id,
                    'sku' => "S24U-{$color->slug}-{$storage->size_gb}GB",
                ]);
            }
        }
    }
}
