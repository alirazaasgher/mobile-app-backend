<?php

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

function update_phone_search_index(
    $storage_type,
    $ram_type,
    $sd_card,
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
    $release_date = $validated['release_date'];

    // Extract commonly used specs

    $capacity = preg_replace('/[^0-9]/', '', $specMap['battery']['capacity']);
    $selfieCam = $specMap['Selfie Camera (MP)'] ?? null;
    $mainCam = getShortCamera($specMap['main_camera'] ?? null);
    $shortChipset = getShortChipset($chipset);
    $cpuString = $specMap['performance']['cpu'];
    $cpuType = "";
    if ($cpuString) {
        preg_match('/^[^(]+/', $cpuString, $match);
        $cpuType = trim($match[0]);
    }

    $main_camera_video = getVideoHighlight($specMap['main_camera']['video']);

    $topSpecs = build_top_specs($specMap, $os, $release_date, $mainCam, $main_camera_video);
    $specsGrid = build_specs_grid($sizeInInches, $specMap, $shortChipset, $cpuType, $mainCam, $main_camera_video);
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
            'ram_type' => $ram_type,
            'sd_card' => $sd_card,
            'available_colors' => json_encode($availableColors),
            'screen_size_inches' => $sizeInInches,
            'battery_capacity_mah' => $capacity,
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

function build_top_specs($specMap, $os, $date, $mainCam, $main_camera_video)
{
    $fornt_camera_video = getVideoHighlight($specMap['selfie_camera']['video']);

    $date = !empty($date) ? Carbon::parse($date)->format('j F, Y') : null;

    $build = $specMap['design']['build'];
    $durability = $specMap['design']['durability'];

    $glassProtection = null;
    $ipRating = null;
    $glassProtection = getGlassProtectionShort($build);
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
            "key" => "released_data",
            "text" => $date ?? "Not Announced Yet",
            "subText" => ""
        ],
        [
            "key" => "glass_protection",
            "text" => $glassProtection ?? "NA",
            "subText" => $ipRating ?? ""
        ],
        [
            "key" => "main_camera",
            "text" => $mainCam ?? "NA",
            "subText" => $main_camera_video ?? ""
        ],
        [
            "key" => "front_camera",
            "text" => $specMap['selfie_camera']['setup'] ?? "NA",
            "subText" => $fornt_camera_video ?? ""
        ],
        [
            "key" => "os",
            "text" => $os,
            "subText" => $shortUpdates ?? ""
        ],
    ];
}

function build_specs_grid($sizeInInches, $specMap, $shortChipset, $cpuType, $mainCam, $main_camera_video)
{

    $resolutionFull = $specMap['display']['resolution'] ?? null;
    $refreshRate = $specMap['display']['refresh_rate'] ?? null;
    // Find all occurrences like "120Hz", "165 Hz", etc.
    preg_match_all('/(\d+)\s*Hz/i', $refreshRate, $matches);
    // Extract numbers
    $rates = array_map('intval', $matches[1]);
    if (!empty($rates)) {
        $highest = max($rates);
        $highestWithHz = $highest . 'Hz';
    } else {
        $highestWithHz = null;
    }
    $brightness = $specMap['display']['brightness'] ?? null;
    // Extract nits (only number)
    preg_match('/(\d+)\s*nits/i', $brightness, $matches);
    $brightnessShort = $matches[1] ?? null;
    $displayTypeShort = $specMap['display']['type'];
    $displayTypeShort = getShortDisplay($displayTypeShort);

    $resolution = null;
    if ($resolutionFull) {
        // Match "number x number" at the start
        if (preg_match('/\d+\s*x\s*\d+/', $resolutionFull, $matches)) {
            $resolution = $matches[0]; // remove spaces → "2868x1320"
        }
    }

    $subvalueParts = [
        $resolution,
        $highestWithHz,
        $brightnessShort ? $brightnessShort . " nits" : null
    ];


    // Charging speeds short form
    $chargingSpec = $specMap['battery']['charging_speed'] ?? '';
    $wirlessCharging = $specMap['battery']['wireless'] ?? '';
    $reverceCharging = $specMap['battery']['reverse'] ?? '';
    $convertWirlessCharging = null;
    $convertReverceCharging = null;
    $chargingSpec = shortChargingSpec($chargingSpec, $wirlessCharging, $reverceCharging);
    return [
        [
            "key" => "display",
            "value" => $sizeInInches . '" ' . $displayTypeShort,
            "subvalue" => implode(' • ', array_filter($subvalueParts))
        ],
        [
            "key" => "chipset",
            "value" => $shortChipset,
            "subvalue" => $cpuType ?? "",
            "hide_on_details_page" => true
        ],
        [
            "key" => "main_camera",
            "value" => $mainCam,
            "subvalue" => $main_camera_video,
        ],
        [
            "key" => "battery",
            "value" => $specMap['battery']['capacity'] ?? "N/A",
            "subvalue" => [
                "wired" => $chargingSpec['fastCharging'] ?? null,
                "wireless" => $chargingSpec['convertWirlessCharging'] ?? null,
                "reverse" => $chargingSpec['convertReverceCharging'] ?? null
            ]
        ]
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

function getShortDisplay($type)
{
    if (!$type)
        return null;

    $t = strtolower($type);

    // Extract tag like "2X", "3X", "144Hz", "120Hz" etc.
    preg_match('/\b(\d+x|\d+hz)\b/i', $type, $tagMatch);
    $tag = isset($tagMatch[0]) ? ' ' . strtoupper($tagMatch[0]) : '';

    // Check for display types
    $hasLTPO = str_contains($t, 'ltpo');
    $hasFoldable = str_contains($t, 'foldable');
    $hasDynamic = str_contains($t, 'dynamic');
    $hasRetina = str_contains($t, 'retina') || str_contains($t, 'xdr');
    $hasAMOLED = str_contains($t, 'amoled');
    $hasOLED = str_contains($t, 'oled');
    $hasLCD = str_contains($t, 'lcd');
    $hasMiniLED = preg_match('/\bmini[- ]?led\b/i', $t);
    $hasMicroLED = preg_match('/\bmicro[- ]?led\b/i', $t);

    // Priority-based matching

    // Mini/Micro LED (check first before generic LED)
    if ($hasMiniLED)
        return 'Mini LED';
    if ($hasMicroLED)
        return 'Micro LED';

    // Foldable combinations
    if ($hasFoldable) {
        if ($hasAMOLED)
            return "Foldable AMOLED$tag";
    }

    // LTPO combinations
    if ($hasLTPO) {
        if ($hasRetina && $hasOLED)
            return 'LTPO OLED';
        if ($hasAMOLED)
            return "LTPO AMOLED$tag";
        if ($hasOLED)
            return 'LTPO OLED';
    }

    // Dynamic AMOLED
    if ($hasDynamic && $hasAMOLED) {
        return "Dynamic AMOLED$tag";
    }

    // Basic AMOLED/OLED
    if ($hasAMOLED)
        return 'AMOLED';
    if ($hasOLED)
        return 'OLED';

    // LCD (IPS, TFT, etc. all become LCD)
    if ($hasLCD || preg_match('/\b(ips|tft|pls|ltps)\b/i', $t)) {
        return 'LCD';
    }

    // Return original if no pattern matched
    return trim($type);
}

function getVideoHighlight($video)
{
    // 1️⃣ Extract resolutions (e.g., 1080p, 720p, 4K, 8K)
    preg_match_all('/(\d{3,4}p|[48]K)/i', $video, $resMatches);

    $resolutions = [];
    foreach ($resMatches[0] as $r) {
        $rUpper = strtoupper($r);
        if ($rUpper === '4K')
            $resolutions[$r] = 4000;
        elseif ($rUpper === '8K')
            $resolutions[$r] = 8000;
        else
            $resolutions[$r] = (int) rtrim($r, 'p');
    }

    // Sort resolutions descending
    arsort($resolutions);

    // Keep top 2 resolutions only (optional: change to 1 if needed)
    $topRes = array_slice(array_keys($resolutions), 0, 2);

    // 2️⃣ Extract key features (HDR, Dolby Vision, 10-bit, etc.)
    preg_match_all('/HDR|Dolby Vision|HDR10\+?|10-bit|12-bit/i', $video, $featMatches);

    $features = array_map('ucwords', array_unique($featMatches[0]));

    // 3️⃣ Combine resolutions + features
    $highlight = array_merge($topRes, $features);

    // Remove duplicates and return as comma-separated string
    return implode(', ', array_unique($highlight));
}

function getGlassProtectionShort($build)
{
    $out = [];

    // Gorilla Glass / Ceramic Shield
    if (preg_match('/(Gorilla\s+Glass\s+[A-Za-z0-9+]+|Ceramic\s+Shield)/i', $build, $m)) {
        $out[] = trim($m[0]) . " (front)";
    }

    // Glass front
    if (stripos($build, 'glass front') !== false) {
        if (!preg_match('/Gorilla|Ceramic/i', $build)) {
            $out[] = "Glass front";
        }
    }

    // Glass back
    if (stripos($build, 'glass back') !== false) {
        $out[] = "Glass back";
    }

    return implode(', ', array_unique($out));
}

function getShortCamera($cameraData, $style = 'compact')
{
    if (!is_array($cameraData)) return '';

    $setup = $cameraData['setup'] ?? '';
    if (empty($setup)) return '';

    $labels = [
        'periscope telephoto' => 'Periscope',
        'periscope' => 'Periscope',
        'telephoto' => 'Telephoto',
        'ultrawide' => 'UW',
        'ultra wide' => 'UW',
        'ultra-wide' => 'UW',
        'wide' => 'Main',
        'macro' => 'Macro',
        'depth' => 'Depth',
    ];

    $parts = array_map('trim', explode(',', strtolower($setup)));
    $cameras = [];

    foreach ($parts as $part) {
        if (empty($part)) continue;

        // Extract MP
        preg_match('/(\d+)\s*mp/i', $part, $mpMatch);
        $mp = $mpMatch ? $mpMatch[1] . 'MP' : '';

        // Find label
        $label = '';
        foreach ($labels as $key => $shortLabel) {
            if (strpos($part, $key) !== false) {
                $label = $shortLabel;
                break;
            }
        }

        if ($mp && $label) {
            $cameras[] = $mp . ' (' . $label . ')';
        }
    }

    if (empty($cameras)) return '';

    switch ($style) {
        case 'compact':
            // Triple 50MP Main + 48MP Periscope + 48MP UW
            $count = count($cameras);
            $countName = ['Single', 'Dual', 'Triple', 'Quad', 'Penta'][$count - 1] ?? $count;
            return $countName . ' ' . implode(' + ', $cameras);

        case 'simple':
            // 50MP Main + 48MP Periscope + 48MP UW (no count prefix)
            return implode(' + ', $cameras);

        case 'detailed':
            // Triple Camera: 50MP Main, 48MP Periscope, 48MP UW
            $count = count($cameras);
            $countName = ['Single', 'Dual', 'Triple', 'Quad', 'Penta'][$count - 1] ?? $count;
            return $countName . ' Camera: ' . implode(', ', $cameras);

        case 'badge':
            // 50MP • 48MP • 48MP with camera types
            return implode(' • ', $cameras);
    }

    return implode(' + ', $cameras);
}

// Alternative: Get individual highlight badges
function getCameraBadges($cameraData)
{
    $badges = [];

    // Main MP badge
    if (!empty($cameraData['main_sensor'])) {
        preg_match('/(\d+)\s*MP/i', $cameraData['main_sensor'], $match);
        if ($match) {
            $badges[] = [
                'text' => $match[1] . 'MP',
                'type' => 'primary',
                'icon' => '📷'
            ];
        }
    }

    // Zoom badge
    if (!empty($cameraData['other_sensors'])) {
        preg_match('/(\d+)x\s*optical/i', $cameraData['other_sensors'], $match);
        if ($match) {
            $badges[] = [
                'text' => $match[1] . 'x Zoom',
                'type' => 'success',
                'icon' => '🔍'
            ];
        }
    }

    // OIS badge
    if (stripos($cameraData['main_sensor'] ?? '', 'OIS') !== false) {
        $badges[] = [
            'text' => 'OIS',
            'type' => 'info',
            'icon' => '📹'
        ];
    }

    // Video badge
    if (!empty($cameraData['video'])) {
        if (stripos($cameraData['video'], '8K') !== false) {
            $badges[] = [
                'text' => '8K',
                'type' => 'warning',
                'icon' => '🎥'
            ];
        }
    }

    return $badges;
}


function shortChargingSpec($chargingSpec, $wirlessCharging, $reverceCharging)
{
    $fastCharging = null;
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
        'fastCharging' => $fastCharging,
        'convertWirlessCharging' => $convertWirlessCharging,
        'convertReverceCharging' => $convertReverceCharging
    ];
}

function format_ip_rating($text)
{
    if (!$text) return null;

    // Extract IP rating
    preg_match('/IP\s?(\d{2})/i', $text, $ip);

    // Extract depth (meters)
    preg_match('/maximum depth of (\d+(\.\d+)?) meters?/i', $text, $depth);

    // Extract time (minutes)
    preg_match('/up to (\d+)\s*minutes?/i', $text, $time);

    if (empty($ip)) return null;

    $parts = ['IP' . $ip[1]];

    if (!empty($depth[1])) {
        $parts[] = "Maximum depth {$depth[1]} meters";
    }

    if (!empty($time[1])) {
        $parts[] = "up to {$time[1]} minutes";
    }

    return implode(', ', $parts);
}
