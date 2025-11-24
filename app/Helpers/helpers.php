<?php

use Illuminate\Support\Facades\DB;

function update_phone_search_index(
    $ramOptions,
    $storageOptions,
    $priceList,
    $availableColors,
    $specMap,
    $validated,
    $phoneId
) {


    // Calculate price range
    $minPrice = !empty($priceList) ? min($priceList) : $priceList[0];
    $maxPrice = !empty($priceList) ? max($priceList) : 0;

    // Extract commonly used specs
    $screenSize = $specMap['size'] ?? null;
    preg_match('/([\d.]+)\s*inches?/i', $screenSize, $matches);
    $sizeInInches = $matches[1] ?? null;
    $battery = $specMap['Battery Capacity (mAh)'] ?? null;
    $mainCam = $specMap['Main Camera (MP)'] ?? null;
    $selfieCam = $specMap['Selfie Camera (MP)'] ?? null;
    $os = $specMap['os'] ?? null;
    $chipset = $specMap['chipset'] ?? null;
    $refreshRate = $specMap['Refresh Rate (Hz)'] ?? 60;
    $displayType = $specMap['type'] ?? null;
    $ipRating = $specMap['IP Rating'] ?? null;
    $weight = $specMap['weight'] ?? null;
    preg_match('/([\d.]+)\s*g?/i', $weight, $matches);
    $weightGs = $matches[1] ?? null;
    // Boolean features
    $has5G = isset($specMap['5G']) && strtolower($specMap['5G']) === 'yes' ? 1 : 0;
    $hasNfc = isset($specMap['NFC']) && strtolower($specMap['NFC']) === 'yes' ? 1 : 0;
    $hasFastCharging = isset($specMap['Fast Charging']) && strtolower($specMap['Fast Charging']) === 'yes' ? 1 : 0;
    $hasWirelessCharging = isset($specMap['Wireless Charging']) && strtolower($specMap['Wireless Charging']) === 'yes' ? 1 : 0;

    // Search content
    $searchContent = implode(' ', [
        $validated['name'],
        $chipset,
        $os,
        $sizeInInches,
        $battery,
        $mainCam,
        $selfieCam,
    ]);

    $topSpecs = build_top_specs($validated, $weightGs, $os, $chipset);
    $specsGrid = build_specs_grid($sizeInInches, $specMap, $mainCam, $battery);


    // ✅ Insert into phone_search_indices
    DB::table('phone_search_indices')->updateOrInsert(
        ['phone_id' => $phoneId],
        [
            'brand' => $validated['brand'],
            'model' => $validated['name'],
            'name' => $validated['name'],
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'ram_options' => json_encode(array_unique($ramOptions)),
            'storage_options' => json_encode(array_unique($storageOptions)),
            'available_colors' => json_encode($availableColors),
            'screen_size_inches' => $sizeInInches,
            'battery_capacity_mah' => $battery,
            'main_camera_mp' => $mainCam,
            'selfie_camera_mp' => $selfieCam,
            'os' => $os,
            'chipset' => $chipset,
            'has_5g' => $has5G,
            'has_nfc' => $hasNfc,
            'has_fast_charging' => $hasFastCharging,
            'has_wireless_charging' => $hasWirelessCharging,
            'refresh_rate_max' => $refreshRate,
            'display_type' => $displayType,
            'ip_rating' => $ipRating,
            'weight_grams' => $weightGs,
            'search_content' => $searchContent,
            'top_specs' => json_encode($topSpecs),
            'specs_grid' => json_encode($specsGrid),
            'updated_at' => now(),
        ]
    );
}
function build_phone_tags(array $specMap): array
{
    $tags = [];

    // 5G
    if (isset($specMap['5G']) && stripos($specMap['5G'], 'yes') !== false) {
        $tags[] = '5G';
    }

    // Chipset
    if (!empty($specMap['chipset'])) {
        $tags[] = trim($specMap['chipset']);
    }

    // Display
    $screenSize = null;
    if (!empty($specMap['Display Size (inches)'])) {
        // Extract number like 6.67 from "6.67 inches, 107.4 cm2 ..."
        preg_match('/([\d.]+)\s*inches?/i', $specMap['Display Size (inches)'], $m);
        $screenSize = $m[1] ?? null;
    }

    $displayType = $specMap['Display Type'] ?? null;

    if ($screenSize && $displayType) {
        $tags[] = "{$screenSize} {$displayType}";
    } elseif ($screenSize) {
        $tags[] = "{$screenSize}\"";
    } elseif ($displayType) {
        $tags[] = $displayType;
    }

    // Battery
    if (!empty($specMap['Battery Capacity (mAh)'])) {
        $tags[] = $specMap['Battery Capacity (mAh)'] . 'mAh';
    }

    // OS
    if (!empty($specMap['os'])) {
        $tags[] = $specMap['os'];
    }

    // Fast / Wireless Charging
    if (isset($specMap['Fast Charging']) && stripos($specMap['Fast Charging'], 'yes') !== false) {
        $tags[] = 'Fast Charging';
    }
    if (isset($specMap['Wireless Charging']) && stripos($specMap['Wireless Charging'], 'yes') !== false) {
        $tags[] = 'Wireless Charging';
    }

    // Main Camera
    if (!empty($specMap['Main Camera (MP)'])) {
        $tags[] = $specMap['Main Camera (MP)'] . 'MP Camera';
    }

    return array_values(array_unique(array_filter($tags)));
}

function hasNonEmptyValue(array $arr): bool
{
    foreach ($arr as $v) {
        if (is_array($v)) {
            if (hasNonEmptyValue($v))
                return true;
            continue;
        }

        // Keep numeric 0 and boolean false if you consider them "non-empty"
        // Here we consider non-empty if:
        // - it's not null AND
        // - if string: trimmed string is not empty
        // - otherwise: not null
        if (is_string($v)) {
            if (trim($v) !== '')
                return true;
        } else {
            if (!is_null($v))
                return true;
        }
    }
    return false;
}

// helper to recursively remove empty values (so DB only stores meaningful keys)
function filterSpecs(array $arr): array
{
    $out = [];
    foreach ($arr as $k => $v) {
        if (is_array($v)) {
            $vFiltered = filterSpecs($v);
            if (!empty($vFiltered))
                $out[$k] = $vFiltered;
            continue;
        }

        if (is_string($v)) {
            $trim = trim($v);
            if ($trim !== '')
                $out[$k] = $trim;
            continue;
        }

        // keep non-null non-string values (adjust if you want to drop false/0)
        if (!is_null($v))
            $out[$k] = $v;
    }
    return $out;
}

function build_top_specs($validated, $weightGs, $os, $chipset)
{
    return [
        [
            "key" => "release_date",
            "text" => $validated['release_date'] ?? "N/A",
            "subText" => "Official launch date"
        ],
        [
            "key" => "body",
            "text" => "{$weightGs}g, 8mm thickness",
            "subText" => "Weight & Thickness"
        ],
        [
            "key" => "os",
            "text" => $os,
            "subText" => "OS Version"
        ],
        [
            "key" => "chipset",
            "text" => $chipset,
            "subText" => "Processor"
        ],
    ];
}

function build_specs_grid($sizeInInches, $specMap, $mainCam, $battery)
{
    return [
        [
            "key" => "display",
            "value" => $sizeInInches . "\"",
            "subvalue" => $specMap['resolution'] ?? null
        ],
        [
            "key" => "main_camera",
            "value" => $mainCam . "MP",
            "subvalue" => "4320p"
        ],
        [
            "key" => "battery",
            "value" => $battery . "mAh",
            "subvalue" => $specMap['Fast Charging (W)'] ?? "Fast Charging"
        ],
    ];
}
