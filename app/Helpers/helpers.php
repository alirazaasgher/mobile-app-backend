<?php

use Illuminate\Support\Facades\DB;

function update_phone_search_index(
    $storage_type,
    $ramOptions,
    $storageOptions,
    $priceList,
    $availableColors,
    $specMap,
    $validated,
    $phoneId
) {



    // Calculate price range
    foreach ($priceList as $price) {
        if (isset($price['pkr']) && is_numeric($price['pkr'])) {
            $pkrValues[] = $price['pkr'];
        }
        if (isset($price['usd']) && is_numeric($price['usd'])) {
            $usdValues[] = $price['usd'];
        }
    }

    // Get min and max
    $minPricePKR = !empty($pkrValues) ? min($pkrValues) : 0;
    $maxPricePKR = !empty($pkrValues) ? max($pkrValues) : 0;

    $minPriceUSD = !empty($usdValues) ? min($usdValues) : 0;
    $maxPriceUSD = !empty($usdValues) ? max($usdValues) : 0;

    $displayType = $specMap['display']['type'] ?? null;
    $screenSize = $specMap['display']['size'] ?? null;
    preg_match('/([\d.]+)\s*inches?/i', $screenSize, $matches);
    $sizeInInches = $matches[1] ?? null;

    $os = $specMap['performance']['os'] ?? null;
    $chipset = $specMap['performance']['chipset'] ?? null;

    // Refresh rate
    $refreshRate = $specMap['display']['refresh_rate'] ?? '60Hz';
    preg_match('/([\d.]+)/', $refreshRate, $matches);
    $refreshRateHz = $matches[1] ?? 60;

    // IP rating / durability
    $ipRating = $specMap['design']['durability'] ?? null;

    if ($ipRating) {
        preg_match('/IP\d{2}/i', $ipRating, $matches);
        $onlyIp = $matches[0] ?? null;
    }
    // Weight
    $weight = $specMap['design']['weight'] ?? null;
    preg_match('/([\d.]+)\s*g/i', $weight, $matches);
    $weightGs = $matches[1] ?? null;

    // Boolean features
    $has5G = isset($specMap['network']['technology']) && str_contains(strtolower($specMap['network']['technology']), '5g') ? 1 : 0;
    $hasNfc = isset($specMap['connectivity']['nfc']) && strtolower($specMap['connectivity']['nfc']) === 'yes' ? 1 : 0;
    $hasFastCharging = isset($specMap['battery']['charging_speed']) && preg_match('/\d+\s*W/i', $specMap['battery']['charging_speed']) ? 1 : 0;
    $hasWirelessCharging = isset($specMap['battery']['wireless']) && strtolower($specMap['battery']['wireless']) === 'yes' ? 1 : 0;

    // Extract commonly used specs

    $battery = $specMap['Battery Capacity (mAh)'] ?? null;
    $mainCam = $specMap['Main Camera (MP)'] ?? null;
    $selfieCam = $specMap['Selfie Camera (MP)'] ?? null;
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

    $shortChipset = null;

    if ($chipset) {
        // Match Snapdragon / MediaTek / Exynos / Apple / etc. and the version
        if (preg_match('/(Snapdragon|MediaTek|Exynos|Apple\s\w+)\s[\w\s]+/i', $chipset, $matches)) {
            $shortChipset = trim($matches[0]);
        }
    }

    $topSpecs = build_top_specs($specMap, $weightGs, $os, $shortChipset);
    $specsGrid = build_specs_grid($sizeInInches, $specMap, $mainCam, $shortChipset);


    // ✅ Insert into phone_search_indices
    DB::table('phone_search_indices')->updateOrInsert(
        ['phone_id' => $phoneId],
        [
            'brand' => $validated['brand'],
            'model' => $validated['name'],
            'name' => $validated['name'],
            'min_price_pkr' => $minPricePKR,
            'max_price_pkr' => $maxPricePKR,
            'min_price_usd' => $minPriceUSD,
            'max_price_usd' => $maxPriceUSD,
            'ram_options' => json_encode($ramOptions),
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
            'refresh_rate_max' => $refreshRateHz,
            'display_type' => $displayType,
            'ip_rating' => $onlyIp ?? "",
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

function build_top_specs($specMap, $weightGs, $os, $shortChipset)
{

    $cpu = $specMap['performance']['cpu'] ?? null;
    if ($cpu) {
        $parts = explode(' ', $cpu);
        $coreType = $parts[0] . (isset($parts[1]) && strpos($parts[1], '-') !== false ? ' ' . $parts[1] : '');
    }
    $updates = $specMap['security']['software_updates'] ?? "";

    if ($updates) {
        $shortUpdates = str_ireplace('updates', '', $updates);
        $shortUpdates = trim($shortUpdates);
    }


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
            "subText" => $shortUpdates ?? ""
        ],
        [
            "key" => "chipset",
            "text" => $shortChipset,
            "subText" => $coreType ?? ""
        ],
    ];
}

function build_specs_grid($sizeInInches, $specMap, $mainCam, $shortChipset)
{
    // echo "<pre>";
    // print_r($specMap);
    // exit;
    $resolutionFull = $specMap['display']['resolution'] ?? null;
    $refreshRate = $specMap['display']['refresh_rate'] ?? null;
    $brightness = $specMap['display']['brightness'] ?? null;

    // Extract nits (only number)
    preg_match('/(\d+)\s*nits/i', $brightness, $matches);
    $brightnessShort = $matches[1] ?? null;
    $displayTypeShort = $specMap['display']['type'];
    // Short display type
    $replacements = [
        '/LTPO.*(AMOLED|OLED)/i' => 'LTPO OLED',
        '/Dynamic.*AMOLED/i' => 'AMOLED',
        '/Super.*AMOLED/i' => 'AMOLED',
        '/AMOLED/i' => 'AMOLED',
        '/OLED/i' => 'OLED',
        '/Foldable.*AMOLED/i' => 'Foldable AMOLED',
        '/(IPS|TFT|PLS|LTPS|IGZO).*LCD/i' => 'LCD',
        '/Mini[- ]?LED/i' => 'Mini LED',
        '/Micro[- ]?LED/i' => 'Micro LED',
    ];

    foreach ($replacements as $pattern => $replacement) {
        if (preg_match($pattern, $displayTypeShort)) {
            $displayTypeShort = preg_replace($pattern, $replacement, $displayTypeShort);
            break; // 🔥 stop after first valid replacement
        }
    }

    $resolution = null;

    if ($resolutionFull) {
        // Match "number x number" at the start
        if (preg_match('/\d+\s*x\s*\d+/', $resolutionFull, $matches)) {
            $resolution = $matches[0]; // remove spaces → "2868x1320"
        }
    }

    $subvalueParts = [
        $resolution,
        $refreshRate,
        $brightnessShort ? $brightnessShort . " nits" : null
    ];

    // Charging speeds short form
    preg_match('/(\d+)W/i', $specMap['battery']['charging_speed'] ?? '', $fast);
    $fastCharging = isset($fast[1]) ? "Fast {$fast[1]}W" : null;

    return [
        [
            "key" => "main_camera",
            "value" => $mainCam ?? "",
            "subvalue" => "8K" // auto convert logic later
        ],
        [
            "key" => "battery",
            "value" => ($specMap['battery']['capacity'] ?? "N/A"),
            "subvalue" => $fastCharging ?? "N/A"
        ],
        [
            "key" => "chipset",
            "value" => $shortChipset,
            "subText" => $coreType ?? ""
        ],

        [
            "key" => "display",
            "value" => $sizeInInches . '" ' . $displayTypeShort,
            "subvalue" => implode(' • ', array_filter($subvalueParts))
        ],
    ];
}
