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
    $selfieCam = $specMap['Selfie Camera (MP)'] ?? null;
    $mainCam = $specMap['main_camera']['setup'] ?? null;
    if ($mainCam && strpos($mainCam, ',') !== false) {

        $camera = strtolower($mainCam);
        $parts = array_map('trim', explode(',', $camera));

        $labelsPriority = [
            'ultrawide' => 'Ultra-Wide',
            'ultra wide' => 'Ultra-Wide',
            'telephoto' => 'Telephoto',
            'macro' => 'Macro',
            'depth' => 'Depth',
            'periscope' => 'Periscope Telephoto',
        ];

        $final = [];

        // First camera → always the first MP
        preg_match('/(\d+(\.\d+)?)\s*mp/i', $parts[0], $mpMatch);
        $final[] = $mpMatch[1] . 'MP';

        // Second camera → choose based on priority
        $second = null;
        foreach ($labelsPriority as $key => $labelName) {
            foreach ($parts as $part) {
                if (strpos($part, $key) !== false) {
                    preg_match('/(\d+(\.\d+)?)\s*mp/i', $part, $mpMatch);
                    if ($mpMatch) {
                        $second = $mpMatch[1] . 'MP ' . $labelName;
                        break 2;
                    }
                }
            }
        }

        // If no priority label found, take second camera if exists
        if (!$second && isset($parts[1])) {
            preg_match('/(\d+(\.\d+)?)\s*mp/i', $parts[1], $mpMatch);
            $second = $mpMatch ? $mpMatch[1] . 'MP' : null;
        }

        if ($second) {
            $final[] = $second;
        }

        $mainCam = implode(' + ', $final);
    }
    // Search content
    $searchContent = implode(' ', [
        $validated['name'],
        $chipset,
        $os,
        $sizeInInches,
        $battery,
        // $mainCam,
        // $selfieCam,
    ]);

    $shortChipset = getShortChipset($chipset);
    $cpuString = $specMap['performance']['cpu'];
    $cpuType = "";
    if ($cpuString) {
        preg_match('/^[^(]+/', $cpuString, $match);
        $cpuType = trim($match[0]);
    }

    $topSpecs = build_top_specs($specMap, $weightGs, $os, $shortChipset, $cpuType);
    $specsGrid = build_specs_grid($sizeInInches, $specMap, $shortChipset, $mainCam, $cpuType);
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
            'ram_options' => json_encode(value: array_map('intval', $ramOptions)),
            'storage_options' => json_encode(array_map('intval', $storageOptions)),
            'storage_type' => $storage_type,
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
            'specs_grid' => json_encode($specsGrid, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]
    );
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

function build_top_specs($specMap, $weightGs, $os, $shortChipset, $cpuType)
{

    $build = $specMap['design']['build'];
    $durability = $specMap['design']['durability'];

    $glassProtection = null;
    $ipRating = null;
    if (preg_match('/(Gorilla\s+Glass\s+[A-Za-z0-9+]+(?:\s*\d*)?|Ceramic\s+Shield(?:\s*\d*)?)/i', $build, $match)) {
        $glassProtection = trim($match[0]);
    }



    // Matches IP ratings like IP68, IP67, IP54, IPX8, etc.
    if (preg_match('/IP(?:\d|X){2}/i', $durability, $match)) {
        $ipRating = strtoupper($match[0]) . ' Water Resistant';
    }

    $updates = $specMap['security']['software_updates'] ?? "";
    if ($updates) {
        $shortUpdates = str_ireplace('updates', '', $updates);
        $shortUpdates = trim($shortUpdates);
    }


    return [
        [
            "key" => "protection",
            "text" => $glassProtection ?? "N/A",
            "subText" => $ipRating ?? "N/A"
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
            "subText" => $cpuType ?? ""
        ],
    ];
}

function build_specs_grid($sizeInInches, $specMap, $shortChipset, $mainCam, $cpuType)
{

    $resolutionFull = $specMap['display']['resolution'] ?? null;
    $refreshRate = $specMap['display']['refresh_rate'] ?? null;
    $brightness = $specMap['display']['brightness'] ?? null;
    $video = $specMap['main_camera']['video'];
    // Extract nits (only number)
    preg_match('/(\d+)\s*nits/i', $brightness, $matches);
    $brightnessShort = $matches[1] ?? null;
    $displayTypeShort = $specMap['display']['type'];
    // Short display type
    $shortTypes = ['AMOLED', 'OLED', 'LTPO OLED', 'Foldable AMOLED', 'IPS LCD', 'Mini LED', 'Micro LED'];

    $replacements = [
        // Dynamic + LTPO + main type → preserve main type + trailing
        '/Foldable\s+Dynamic\s+LTPO\s+(AMOLED|OLED)(.*)/i' => 'LTPO $1$2',
        '/Dynamic\s+LTPO\s+(AMOLED|OLED)(.*)/i' => 'LTPO $1$2',
        '/LTPO\s+(AMOLED|OLED)(.*)/i' => 'LTPO $1$2',

        // Dynamic / Super AMOLED → preserve main type + trailing
        '/Foldable\s+Dynamic\s+(AMOLED)(.*)/i' => 'Foldable $1$2',
        '/Dynamic\s+(AMOLED)(.*)/i' => '$1$2',
        '/Super\s+(AMOLED)(.*)/i' => '$1$2',

        // Simple types
        '/AMOLED/i' => 'AMOLED',
        '/OLED/i' => 'OLED',
        '/Foldable.*AMOLED(.*)/i' => 'Foldable AMOLED$1',
        '/(IPS|TFT|PLS|LTPS|IGZO).*LCD/i' => 'LCD',
        '/Mini[- ]?LED/i' => 'Mini LED',
        '/Micro[- ]?LED/i' => 'Micro LED',
    ];

    if (!in_array($displayTypeShort, $shortTypes)) {
        foreach ($replacements as $pattern => $replacement) {
            $newDisplay = preg_replace($pattern, $replacement, $displayTypeShort, 1);
            if ($newDisplay !== $displayTypeShort) {
                $displayTypeShort = $newDisplay;
                break;
            }
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
    $chargingSpec = $specMap['battery']['charging_speed'] ?? '';
    $wirlessCharging = $specMap['battery']['wireless'] ?? '';
    $reverceCharging = $specMap['battery']['reverse'] ?? '';
    $convertWirlessCharging = null;
    $convertReverceCharging = null;

    // 1. Try Android-style (digits + W)
    if (preg_match('/(\d+)\s*W/i', $chargingSpec, $match)) {
        $fastCharging = "$match[1]W";
    }
    // 2. Try iPhone-style (PD + AVS + time)
    elseif (preg_match('/PD\s*([\d\.]+).*?(?:\(?(\d+% in \d+ min)\)?)/i', $chargingSpec, $match)) {
        $fastCharging = "PD{$match[1]}";
    }


    // Wireless charging
    if (preg_match('/(\d+(\.\d+)?)\s*W\s*(wireless)?/i', $wirlessCharging, $match)) {
        $convertWirlessCharging = "$match[1]W";
    } elseif (preg_match('/(PD[\d\.]+|MagSafe|Qi2)/i', $wirlessCharging, $match)) {
        $convertWirlessCharging = $match[0];
    }

    // Reverse charging
    if (preg_match('/(\d+(\.\d+)?)\s*W\s*(reverse\s*wired)?/i', $reverceCharging, $match)) {
        $convertReverceCharging = "$match[1]W";
    } elseif (preg_match('/(PD[\d\.]+|MagSafe|Qi2)/i', $reverceCharging, $match)) {
        $convertReverceCharging = $match[0];
    }

    return [
        [
            "key" => "main_camera",
            "value" => $mainCam ?? "",
            "subvalue" => "" // auto convert logic later
        ],
        [
            "key" => "battery",
            "value" => $specMap['battery']['capacity'] ?? "N/A",
            "subvalue" => [
                "wired" => $fastCharging ?? null,
                "wireless" => $convertWirlessCharging ?? null,
                "reverse" => $convertReverceCharging ?? null
            ]
        ],
        [
            "key" => "chipset",
            "value" => $shortChipset,
            "subvalue" => $cpuType ?? "",
            "hide_on_details_page" => true
        ],

        [
            "key" => "display",
            "value" => $sizeInInches . '" ' . $displayTypeShort,
            "subvalue" => implode(' • ', array_filter($subvalueParts))
        ],
    ];
}

function getShortChipset($chipset)
{
    if (!$chipset) {
        return null;
    }

    // If already short (chipset + 1-2 words + optional nm), return as-is
    if (preg_match('/^(Snapdragon|Dimensity|A\d+|Exynos|Tensor|Kirin)\s+[\w\+\-]+(?:\s+[\w\+\-]+)?(?:\s*\(\d+\s*nm\))?$/i', $chipset)) {
        return $chipset;
    }

    // Extract nm if present
    preg_match('/\((\d+\s*nm)\)/i', $chipset, $nmMatch);
    $nm = $nmMatch[1] ?? null;

    // Extract main chipset keyword and following words
    if (preg_match('/\b(Snapdragon|Dimensity|A\d+|Exynos|Tensor|Kirin)\s+([\w\+\-]+(?:\s+[\w\+\-]+)?)/i', $chipset, $chipMatch)) {
        $chip = $chipMatch[1];
        $suffix = $chipMatch[2];

        // Stop at common filler words
        $suffix = preg_replace('/\s+(Mobile|Platform|Processor|for|with|Edition|Series).*/i', '', $suffix);

        $shortChip = $chip . ' ' . trim($suffix);
    } elseif (preg_match('/\b(Snapdragon|Dimensity|A\d+|Exynos|Tensor|Kirin)\b/i', $chipset, $chipMatch)) {
        // Just the chipset name without suffix
        $shortChip = $chipMatch[1];
    } else {
        // No recognized chipset, return first 2-3 words
        preg_match('/^(\S+(?:\s+\S+){0,2})/', $chipset, $fallbackMatch);
        $shortChip = $fallbackMatch[1] ?? $chipset;
    }

    // Append nm if available and not already present
    if ($nm && !preg_match('/\(\d+\s*nm\)/i', $shortChip)) {
        $shortChip .= " ($nm)";
    }

    return $shortChip;
}






