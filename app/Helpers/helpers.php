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
    $ipRating = $specMap['build']['ip_rating'] ?? null;
    $ipRating = shortIPRating($ipRating);
    // Weight
    $weight = $specMap['build']['weight'] ?? null;
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
    $selfieCam = $specMap['selfie_camera']['setup'] ?? null;
    $selfieCammp = preg_match('/\(\s*(\d+)\s*mp\s*\)/i', (string) $selfieCam, $matches)
        ? $matches[1]
        : '';
    $mainCam = $specMap['main_camera']['setup'] ?? null;
    $mainCam = getShortCamera($mainCam);

    $shortChipset = getShortChipset($chipset);
    $cpuString = $specMap['performance']['cpu'];
    $cpuType = cpuType($cpuString);
    $setup = isset($specMap['main_camera']['setup']) && !empty($specMap['main_camera']['setup'])
        ? explode(" ", $specMap['main_camera']['setup'])[0]
        : '';
    $main_camera_video = getVideoHighlight($specMap['main_camera']['video']);
    $topSpecs = build_top_specs($specMap, $os, $release_date, $mainCam, $main_camera_video, $ipRating);
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
            'main_camera_mp' => $setup,
            'selfie_camera_mp' => $selfieCammp,
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

function build_top_specs($specMap, $os, $date, $mainCam, $main_camera_video, $ipRating)
{
    $fornt_camera_video = getVideoHighlight($specMap['selfie_camera']['video']);

    $date = !empty($date) ? Carbon::parse($date)->format('j F, Y') : null;
    $updates = $specMap['security']['software_updates'] ?? "";
    if ($updates) {
        $shortUpdates = str_ireplace('updates', '', $updates);
        $shortUpdates = trim($shortUpdates);
    }
    $glassProtection = getGlassProtectionShort($specMap['build']['build']);

    return [
        [
            "key" => "released_data",
            "text" => $date ?? "Not Announced Yet",
            "subText" => ""
        ],
        [
            "key" => "glass_protection",
            "text" => $glassProtection ?? "",
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

    // Extract nm process once
    $nm = null;
    if (preg_match('/\((\d+\s*nm)\)/i', $chipset, $nmMatch)) {
        $nm = $nmMatch[1];
    }

    // Check if already short format (Brand + Chipset + Model + optional nm)
    // Examples: "Google Tensor G5 (3 nm)", "MediaTek Dimensity 6300"
    if (preg_match('/^(Google|MediaTek|Apple|Samsung|Huawei|Qualcomm)\s+(Snapdragon|Dimensity|A\d+|Exynos|Tensor|Kirin)\s+[\w\+\-]+(?:\s+[\w\+\-]+)?(?:\s*\(\d+\s*nm\))?$/i', $chipset)) {
        return $chipset; // Already short, return as-is
    }

    // Apple A-series (special handling)
    if (preg_match('/\bApple\s+(A\d+(?:\s+\w+)?)/i', $chipset, $m)) {
        $result = 'Apple ' . trim($m[1]);
        return $nm && !str_contains($result, 'nm') ? "$result ($nm)" : $result;
    }

    // Main chipset extraction - capture up to 4 words after chipset name
    if (preg_match('/\b(Snapdragon|Dimensity|A\d+|Exynos|Tensor|Kirin)\s+([\w\+\-]+(?:\s+[\w\+\-]+){0,3})/i', $chipset, $m)) {
        $chip = $m[1];
        $suffix = $m[2];

        // Remove common filler words (but NOT manufacturer names at this stage)
        $suffix = preg_replace('/\s+(Mobile|Platform|Processor|for|with|Edition|Series|Galaxy).*$/i', '', $suffix);

        $result = "$chip " . trim($suffix);
        return $nm && !str_contains($result, 'nm') ? "$result ($nm)" : $result;
    }

    // Just chipset name without suffix
    if (preg_match('/\b(Snapdragon|Dimensity|A\d+|Exynos|Tensor|Kirin)\b/i', $chipset, $m)) {
        $result = $m[1];
        return $nm ? "$result ($nm)" : $result;
    }

    // Fallback: first 2-3 words
    if (preg_match('/^(\S+(?:\s+\S+){0,2})/', $chipset, $m)) {
        return $m[1];
    }

    return $chipset;
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
    if (empty($build)) {
        return null;
    }

    $front = [];
    $back = [];
    $frame = [];

    // Common materials pattern
    $materials = 'glass|plastic|aluminum\s+alloy|fiber-reinforced\s+plastic|' .
        'eco\s+leather|silicone\s+polymer|ceramic-glass\s+fiber-reinforced\s+polymer';


    /* ---------- FRONT ---------- */
    if (preg_match_all('~(glass|plastic)\s+front(?:\s*\(([^)]+)\))?~ix', $build, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $material = !empty($m[2]) ? trim($m[2]) : ucfirst(strtolower($m[1]));
            $front[] = $material . ' (Front)';
        }
    }

    /* ---------- BACK ---------- */
    if (
        preg_match_all(
            "~($materials)(?:\s*\(([^)]+)\))?\s+back(?:\s*\(([^)]+)\))?(?:\s*(?:or|/)\s*($materials)(?:\s*\(([^)]+)\))?\s+back(?:\s*\(([^)]+)\))?)?~ix",
            $build,
            $matches,
            PREG_SET_ORDER
        )
    ) {

        foreach ($matches as $m) {
            $backMaterials = [];

            // First back material
            // Check if there's branding in parentheses within the material (m[2]) or after "back" (m[3])
            if (!empty($m[3])) {
                $backMaterials[] = ucfirst(trim($m[3]));
            } elseif (!empty($m[2])) {
                $backMaterials[] = ucfirst(trim($m[2]));
            } else {
                $backMaterials[] = ucfirst(strtolower($m[1]));
            }

            // Second back material (if exists)
            if (!empty($m[4])) {
                if (!empty($m[6])) {
                    $backMaterials[] = ucfirst(trim($m[6]));
                } elseif (!empty($m[5])) {
                    $backMaterials[] = ucfirst(trim($m[5]));
                } else {
                    $backMaterials[] = ucfirst(strtolower($m[4]));
                }
            }

            $back[] = implode(' or ', $backMaterials) . ' (Back)';
        }
    }

    /* ---------- FRAME ---------- */
    if (
        preg_match_all(
            '~(titanium|aluminum\s+alloy|aluminum|aluminium|stainless\s+steel|plastic)\s+frame(?:\s*\(([^)]+)\))?~ix',
            $build,
            $matches,
            PREG_SET_ORDER
        )
    ) {

        foreach ($matches as $m) {
            $material = ucfirst(strtolower(str_replace('aluminium', 'aluminum', $m[1])));
            if (!empty($m[2])) {
                $material .= ' (' . trim($m[2]) . ')';
            }
            $frame[] = $material . ' Frame';
        }
    }

    /* ---------- SMART CLEANUP ---------- */
    $brandedGlassPattern = '/(Gorilla Glass|Ceramic Shield|Dragon|Victus|Crystal Shield|Corning|Armor)/i';

    foreach (['Front' => &$front, 'Back' => &$back] as $side => &$list) {
        if (empty($list))
            continue;

        $hasBranded = false;
        foreach ($list as $item) {
            if (preg_match($brandedGlassPattern, $item)) {
                $hasBranded = true;
                break;
            }
        }

        if ($hasBranded) {
            $list = array_values(array_filter($list, fn($v) => !preg_match('/^Glass \(' . $side . '\)$/i', $v)));
        }
    }
    unset($list);

    /* ---------- MERGE SAME FRONT + BACK ---------- */
    if (!empty($front) && !empty($back)) {
        $frontMap = [];
        $backMap = [];

        foreach ($front as $f) {
            if (preg_match('/^(.*)\s+\(Front\)$/', $f, $m)) {
                $frontMap[strtolower($m[1])] = $m[1];
            }
        }
        foreach ($back as $b) {
            if (preg_match('/^(.*)\s+\(Back\)$/', $b, $m)) {
                $backMap[strtolower($m[1])] = $m[1];
            }
        }

        $common = array_intersect_key($frontMap, $backMap);
        if (!empty($common)) {
            $label = reset($common);
            $front = array_values(array_filter($front, fn($v) => stripos($v, $label) === false));
            $back = array_values(array_filter($back, fn($v) => stripos($v, $label) === false));
            array_unshift($front, $label . ' (Front, Back)');
        }
    }

    /* ---------- PICK ONLY ONE PER SIDE (premium first) ---------- */
    $frontHighlight = !empty($front) ? reset($front) : null;

    $backHighlight = null;
    if (!empty($back)) {
        foreach ($back as $b) {
            if (preg_match($brandedGlassPattern, $b)) {
                $backHighlight = $b;
                break;
            }
        }
        $backHighlight = $backHighlight ?: reset($back);
    }

    $frameHighlight = !empty($frame) ? reset($frame) : null;

    /* ---------- FINAL OUTPUT ---------- */
    $parts = array_filter([$frontHighlight, $backHighlight, $frameHighlight]);
    return !empty($parts) ? implode(' · ', $parts) : null;
}









function getShortCamera(string $mainCam): string
{
    if (!$mainCam || !str_contains($mainCam, ',')) {
        return $mainCam;
    }

    $parts = array_map('trim', explode(',', strtolower($mainCam)));

    $map = [
        'periscope telephoto' => 'Periscope',
        'telephoto' => 'Telephoto',
        'ultra-wide' => 'Ultrawide',
        'ultrawide' => 'Ultrawide',
        'ultra wide' => 'Ultrawide',
        'wide' => 'Wide',
        'macro' => 'Macro',
        'depth' => 'Depth',
    ];

    $cameras = [];

    foreach ($parts as $part) {
        if (!preg_match('/(\d+(?:\.\d+)?)\s*mp/', $part, $mp)) {
            continue;
        }

        $mp_value = (float) $mp[1];
        $label = '';

        foreach ($map as $key => $short) {
            if (str_contains($part, $key)) {
                $label = $short;
                break;
            }
        }

        // Skip macro and depth sensors
        if (in_array($label, ['Macro', 'Depth'])) {
            continue;
        }

        $cameras[] = [
            'text' => $label ? "{$mp[1]}MP($label)" : "{$mp[1]}MP",
            'mp' => $mp_value,
            'label' => $label ?: 'Wide',
        ];
    }

    if (empty($cameras)) {
        return $mainCam;
    }
    // If exactly 2 cameras, return both
    if (count($cameras) === 2) {
        return implode(',', array_column($cameras, 'text'));
    }

    // Start with main camera
    $result = [$cameras[0]];

    // Check for premium secondary cameras (≥48MP)
    foreach ($cameras as $camera) {
        if ($camera['label'] === 'Wide') {
            continue; // Skip main, already added
        }

        // Add Periscope (always premium)
        if ($camera['label'] === 'Periscope') {
            $result[] = $camera;
            continue;
        }

        // Add Telephoto or Ultrawide only if ≥48MP
        if (in_array($camera['label'], ['Telephoto', 'Ultrawide']) && $camera['mp'] >= 48) {
            $result[] = $camera;
        }
    }

    // Limit to maximum 3 cameras
    $result = array_slice($result, 0, 3);

    return implode(',', array_column($result, 'text'));
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

function shortIPRating($ipRating)
{
    if (empty($ipRating)) {
        return null;
    }

    $ratings = [];

    // First, match standard IP ratings (IP66, IP68, IP69K, etc.)
    if (preg_match_all('/IP\d{2}K?/i', $ipRating, $matches)) {
        $ratings = array_merge($ratings, $matches[0]);
    }

    // Then, match shorthand formats like "IP66/68/69" or "IP66/68/69K"
    if (preg_match('/IP(\d{2}(?:\/\d{2}K?)+)/i', $ipRating, $matches)) {
        // Extract the base "IP" and all the numbers
        preg_match_all('/\d{2}K?/', $matches[1], $numbers);

        foreach ($numbers[0] as $num) {
            $ratings[] = 'IP' . $num;
        }
    }

    if (empty($ratings)) {
        return null;
    }

    // Normalize to uppercase
    $ratings = array_map('strtoupper', $ratings);

    // Remove duplicates while preserving order
    $ratings = array_values(array_unique($ratings));

    // Sort ratings for consistency
    sort($ratings, SORT_NATURAL);

    // Join with '/'
    return implode('/', $ratings);
}



function cpuType($cpuString)
{
    $cpuType = "";
    if ($cpuString) {
        preg_match('/^[^(]+/', $cpuString, $match);
        $cpuType = trim($match[0]);
    }

    return $cpuType;
}
