<?php

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

function update_phone_search_index(
    $storage_type,
    $ram_type,
    $sd_card,
    $ramOptions,
    $storageOptions,
    $min_ram,
    $min_storage,
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
    $capacity = isset($specMap['battery']['capacity']) ? preg_replace('/[^0-9]/', '', $specMap['battery']['capacity']) : 0;
    $selfieCam = $specMap['selfie_camera']['setup'] ?? null;
    if ($selfieCam) {
        $selfieCammp = preg_match('/\(\s*(\d+)\s*mp\s*\)/i', (string) $selfieCam, $matches)
            ? $matches[1]
            : 0;
    }

    $mainCam = $specMap['main_camera']['setup'] ?? null;
    $mainCam = getShortCamera($mainCam ?? '');

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
            'min_ram' => $min_ram,
            'min_storage' => $min_storage,
            'storage_type' => $storage_type,
            'ram_type' => $ram_type,
            'sd_card' => $sd_card ?? 0,
            // 'available_colors' => json_encode($availableColors),
            'screen_size_inches' => $sizeInInches,
            'battery_capacity_mah' => $capacity ?? 0,
            'main_camera_mp' => $setup,
            'selfie_camera_mp' => $selfieCammp ?? 0,
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

    // SPECIFIC FIX: Remove "Qualcomm " prefix only for Snapdragon short strings
    if (preg_match('/^Qualcomm\s+Snapdragon\b/i', $chipset)) {
        $chipset = preg_replace('/^Qualcomm\s+/i', '', $chipset);
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





function mobileVersion($os)
{
    if (!is_string($os) || trim($os) === '') {
        return '';
    }

    $versions = [];

    // Match Android versions with optional region
    if (preg_match_all('/android\s*(\d+(?:\.\d+)?)\s*(?:\(([^)]+)\))?/i', $os, $matches)) {
        foreach ($matches[1] as $index => $version) {
            $version = rtrim($version, '.0');
            $region = !empty($matches[2][$index]) ? ' (' . trim($matches[2][$index]) . ')' : '';
            $versions[] = 'Android ' . $version . $region;
        }
    }

    // Match iOS versions with optional region
    if (preg_match_all('/iOS\s*(\d+(?:\.\d+)?)\s*(?:\(([^)]+)\))?/i', $os, $matches)) {
        foreach ($matches[1] as $index => $version) {
            $version = rtrim($version, '.0');
            $region = !empty($matches[2][$index]) ? ' (' . trim($matches[2][$index]) . ')' : '';
            $versions[] = 'iOS ' . $version . $region;
        }
    }

    // Match iPadOS versions with optional region
    if (preg_match_all('/iPadOS\s*(\d+(?:\.\d+)?)\s*(?:\(([^)]+)\))?/i', $os, $matches)) {
        foreach ($matches[1] as $index => $version) {
            $version = rtrim($version, '.0');
            $region = !empty($matches[2][$index]) ? ' (' . trim($matches[2][$index]) . ')' : '';
            $versions[] = 'iPadOS ' . $version . $region;
        }
    }

    // Match EMUI versions with optional region
    if (preg_match_all('/EMUI\s*(\d+(?:\.\d+)?)\s*(?:\(([^)]+)\))?/i', $os, $matches)) {
        foreach ($matches[1] as $index => $version) {
            $version = rtrim($version, '.0');
            $region = !empty($matches[2][$index]) ? ' (' . trim($matches[2][$index]) . ')' : '';
            $versions[] = 'EMUI ' . $version . $region;
        }
    }

    // Match HarmonyOS versions with optional region
    if (preg_match_all('/HarmonyOS\s*(\d+(?:\.\d+)?)\s*(?:\(([^)]+)\))?/i', $os, $matches)) {
        foreach ($matches[1] as $index => $version) {
            $version = rtrim($version, '.0');
            $region = !empty($matches[2][$index]) ? ' (' . trim($matches[2][$index]) . ')' : '';
            $versions[] = 'HarmonyOS ' . $version . $region;
        }
    }

    return implode(', ', $versions);
}

function getHdrSupport($features)
{
    if (!is_string($features) || trim($features) === '') {
        $hdr_support = 'no'; // Changed from '' to 'no' to match your scale
    } else {
        $hdrValues = array_filter(
            array_map('trim', explode(',', $features)),
            function ($feature) {
                $f = strtolower($feature);
                return (
                    str_contains($f, 'hdr') ||          // HDR10, HDR10+, HDR Vivid, HD
                    str_contains($f, 'dolby vision') || // Dolby Vision
                    str_contains($f, 'hlg')             // Hybrid Log-Gamma
                );
            }
        );

        // If no HDR features found, set to 'no'
        $hdr_support = !empty($hdrValues) ? strtoupper(implode(', ', $hdrValues)) : 'no';
    }

    return $hdr_support;
}
function buildMaterials($buildString)
{
    $materials = [
        'build_material' => null,
        'back_material' => null,
    ];

    $text = trim(strip_tags($buildString));
    if ($text === '') {
        return $materials;
    }

    // Better splitting: comma, slash, and handle common patterns
    $text = preg_replace('/\s*([,;\/])\s*/', ' $1 ', $text); // normalize spaces
    $parts = preg_split('/\s*[,;\/]\s*/', $text);

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '')
            continue;

        $lower = strtolower($part);

        // Back detection (more keywords)
        if (preg_match('/\b(back|rear|backside)\b/i', $lower)) {
            // Remove "back" keyword
            $clean = preg_replace('/\s*\b(back|rear|backside)\b\s*/i', ' ', $part);
            $clean = preg_replace('/\s*\([^)]+\)/', '', $clean); // remove (eco leather) etc.
            $clean = trim($clean);

            // Optional: split alternatives on "or" and take first (or keep both)
            if (stripos($clean, ' or ') !== false) {
                $options = preg_split('/\s+or\s+/i', $clean);
                $clean = trim($options[0]); // take first option as main
                // You can also store $options if needed
            }

            $materials['back_material'] = strtolower($clean) ?: null;
            continue;
        }

        // Front / main build detection
        if (preg_match('/\b(front|upper|main)\b/i', $lower) || stripos($lower, 'glass') !== false) {
            $clean = preg_replace('/\s*\b(front|upper|main)\b\s*/i', ' ', $part);
            $clean = preg_replace('/\s*\([^)]+\)/', '', $clean);
            $clean = trim($clean);

            $materials['build_material'] = strtolower($clean) ?: null;
        }
    }

    // Fallback: if no back found but we have frame, etc. — but in this case it's fine

    return $materials;
}

function parseFastChargingToWatts($value)
{
    if (!$value) {
        return null;
    }

    $value = strtoupper(trim($value));

    // 1️⃣ Explicit wattage (e.g., "30W", "67W", "120W", "25W")
    if (preg_match('/(\d+(?:\.\d+)?)\s*W\b/i', $value, $matches)) {
        return floatval($matches[1]);
    }

    // 2️⃣ USB PD (Power Delivery) with version
    if (preg_match('/PD\s*(\d+(?:\.\d+)?)/i', $value, $matches)) {
        $version = floatval($matches[1]);

        if ($version >= 3.2)
            return 40;   // PD 3.2 SPR AVS (50% in 20 min)
        if ($version >= 3.1)
            return 100;  // PD 3.1 SPR max
        if ($version >= 3.0)
            return 20;   // PD 3.0 standard (50% in 30 min)
        if ($version >= 2.0)
            return 20;   // PD 2.0 standard (50% in 30 min)

        return 15;  // Older PD versions
    }

    // 3️⃣ Standalone "PD" (assume PD 2.0/3.0 standard)
    if ($value === 'PD') {
        return 20;
    }

    // 4️⃣ Wireless charging protocols
    if (str_contains($value, 'MAGSAFE'))
        return 25;  // MagSafe (50% in 30 min)
    if (str_contains($value, 'QI2'))
        return 15;      // Qi2 (50% in 30 min)
    if (str_contains($value, 'QI'))
        return 7.5;       // Standard Qi (slower)

    // 5️⃣ Proprietary fast charging protocols (wired)
    // Check longer/specific names first to avoid false matches
    if (str_contains($value, 'SUPERVOOC'))
        return 65;
    if (str_contains($value, 'VOOC'))
        return 30;
    if (str_contains($value, 'WARP'))
        return 30;
    if (str_contains($value, 'DASH'))
        return 20;
    if (str_contains($value, 'HYPERCHARGE'))
        return 120;
    if (str_contains($value, 'SUPERCHARGE'))
        return 40;
    if (str_contains($value, 'QUICK CHARGE') || str_contains($value, 'QC'))
        return 18;
    if (str_contains($value, 'ADAPTIVE FAST'))
        return 15;
    if (str_contains($value, 'TURBOPOWER'))
        return 30;
    if (str_contains($value, 'FLASH CHARGE'))
        return 44;  // Vivo
    if (str_contains($value, 'MEIZU'))
        return 24;  // Meizu mCharge

    // 6️⃣ Fallback: extract numeric value only if it looks like wattage
    // Avoid false positives from version numbers (e.g., "Fast Charging 3.0")
    if (preg_match('/^(\d+(?:\.\d+)?)\s*$/i', $value, $matches)) {
        $numeric = floatval($matches[1]);
        // Only accept if it's a reasonable wattage range (5W - 300W)
        return ($numeric >= 5 && $numeric <= 300) ? $numeric : null;
    }

    return null;
}



function getMobileDimensions($raw)
{
    $result = [];

    if (empty($raw)) {
        return $result;
    }

    $clean = strip_tags($raw);

    // Try to match Folded first
    if (
        preg_match(
            '/Folded\s*:\s*([\d.]+)\s*x\s*([\d.]+)\s*x\s*([\d.]+)\s*mm/i',
            $clean,
            $m
        )
    ) {
        // Folded found → return only this
        return [
            'dimensions' => $m[1] . ' x ' . $m[2],
            'thickness' => $m[3],
        ];
    }

    // Fallback: check for a simple "L x W x T mm" (normal phones or single line)
    if (
        preg_match(
            '/([\d.]+)\s*x\s*([\d.]+)\s*x\s*([\d.]+)\s*mm/i',
            $clean,
            $m
        )
    ) {
        return [
            'dimensions' => $m[1] . ' x ' . $m[2],
            'thickness' => $m[3],
        ];
    }

    // Nothing found
    return $result;
}



function extractVideo(string $video): ?string
{
    if (!empty($video)) {
        $video = strtolower($video);

        // 8K
        if (preg_match('/8k@(\d+)fps/', $video, $m)) {
            return '8k@' . $m[1] . 'fps';
        }
        if (str_contains($video, '8k')) {
            return '8k';
        }

        // 4K
        if (preg_match('/4k@(\d+)fps/', $video, $m)) {
            return '4k@' . $m[1] . 'fps';
        }
        if (str_contains($video, '4k')) {
            return '4k';
        }

        // 1080p
        if (preg_match('/1080p@(\d+)fps/', $video, $m)) {
            return '1080p@' . $m[1] . 'fps';
        }
        if (str_contains($video, '1080p')) {
            return '1080p';
        }

        // 720p
        if (str_contains($video, '720p')) {
            return '720p';
        }
    }


    return null;
}

function getFlash(array $camera): ?string
{
    $features = $camera['features'] ?? '';

    if (preg_match('/([a-z0-9\-\s]+flash)/i', $features, $match)) {
        return trim($match[1]);
    }

    return null;
}

function extractStabilization(array $camera): string
{
    $text = strtolower(
        ($camera['main_sensor'] ?? '') . ' ' .
        strip_tags($camera['other_sensors'] ?? '') . ' ' .
        ($camera['video'] ?? '')
    );

    $hasOIS = str_contains($text, 'ois');
    $hasEIS = str_contains($text, 'eis');

    if ($hasOIS && $hasEIS) {
        return 'ois + eis';
    }

    if ($hasOIS) {
        return 'ois';
    }

    if ($hasEIS) {
        return 'eis';
    }

    return 'none';
}

function extractOpticalZoom(array $camera): ?int
{
    $text = strip_tags($camera['other_sensors'] ?? '');

    if (preg_match('/(\d+)x\s*optical zoom/i', $text, $m)) {
        return (int) $m[1];
    }

    return null;
}

function extractCameraApertures(array $camera): array
{
    $result = [];

    // MAIN SENSOR
    if (!empty($camera['main_sensor'])) {
        if (preg_match('/f\/([\d.]+).*?\((wide)\)/i', $camera['main_sensor'], $m)) {
            $result['wide_aperture'] = $m[1];
        }
    }

    // OTHER SENSORS (HTML)
    if (!empty($camera['other_sensors'])) {
        $html = strip_tags($camera['other_sensors']);

        preg_match_all(
            '/f\/([\d.]+).*?\((periscope telephoto|ultrawide|telephoto)\)/i',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $type = str_replace(' ', '_', strtolower($m[2]));
            $result[$type . "_aperture"] = $m[1];
        }
    }

    return $result;
}

function parseCameraSetup($setup)
{
    if (!$setup) {
        return [];
    }

    $cameras = array_map('trim', explode(',', $setup));

    return array_values(array_filter(array_map(function ($c) {
        preg_match('/(\d+)\s*MP\s*\((.*?)\)/i', $c, $match);

        if (!$match) {
            return null;
        }

        return [
            'mp' => (int) $match[1],
            'type' => str_replace(' ', '_', strtolower(trim($match[2]))),
        ];
    }, $cameras)));
}

function parseMemory(?string $memory): array
{
    if (!$memory) {
        return ['ram' => null, 'storage' => null, 'variants' => []];
    }

    $variants = [];
    $allRam = [];
    $allStorage = [];

    // Split by comma for multiple variants
    $parts = array_map('trim', explode(',', $memory));

    foreach ($parts as $part) {
        // Match patterns like "12GB/256GB" or "8GB/128GB"
        if (preg_match('/(\d+)\s*GB\s*\/\s*(\d+)\s*(GB|TB)/i', $part, $matches)) {
            $ram = (int) $matches[1];
            $storage = (int) $matches[2];

            // Convert TB to GB if needed
            if (strtoupper($matches[3]) === 'TB') {
                $storage = $storage * 1024;
            }

            $allRam[] = $ram;
            $allStorage[] = $storage;
        }
    }

    // Return the lowest variant for scoring (minimum)
    return [
        'ram' => !empty($allRam) ? min($allRam) : null,
        'storage' => !empty($allStorage) ? min($allStorage) : null,
    ];
}


// Helper methods - ADD THESE TO YOUR PHONE MODEL
function extractNumber($value)
{
    if (!$value)
        return null;
    preg_match('/\d+/', $value, $matches);
    return $matches[0] ?? null;
}

function extractSize($value)
{
    if (!$value)
        return null;
    preg_match('/([\d.]+)\s*inch/i', $value, $matches);
    return $matches[1] ?? null;
}

function shortResolution($value)
{
    if (!$value)
        return null;
    preg_match('/(\d+)\s*x\s*(\d+)/', $value, $matches);
    return isset($matches[1], $matches[2]) ? "{$matches[1]} x {$matches[2]}" : null;
}

function shortOS($value)
{
    if (!$value)
        return null;
    // "Android 16 ,OneUI 8.0" -> "Android 16"
    // "IOS 26" -> "iOS 26"
    return trim(explode(',', $value)[0]);
}

function extractPpi(?string $resolution, ?float $screenSize = null): ?int
{
    if (!$resolution) {
        return null;
    }

    // Method 1: Extract PPI if already provided (e.g., "~450 ppi")
    if (preg_match('/~?\s*(\d+)\s*ppi/i', $resolution, $matches)) {
        return (int) $matches[1];
    }

    // Method 2: Calculate PPI from resolution and screen size
    if ($screenSize && preg_match('/(\d+)\s*[x×]\s*(\d+)/i', $resolution, $matches)) {
        $width = (int) $matches[1];
        $height = (int) $matches[2];

        // Calculate diagonal resolution in pixels
        $diagonalPixels = sqrt(($width ** 2) + ($height ** 2));

        // Calculate PPI
        $ppi = $diagonalPixels / $screenSize;

        return (int) round($ppi);
    }

    return null;
}

function extractAllBrightness(?string $brightness): array
{
    $result = [
        'peak' => null,
        'hbm' => null,
        'typical' => null,
    ];

    if (!$brightness) {
        return $result;
    }

    $brightness = strtolower($brightness);

    preg_match_all('/(\d+)\s*nits[^\)]*(?:\(([^)]+)\))?/', $brightness, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $value = (int) $match[1];
        $label = $match[2] ?? '';

        if (strpos($label, 'peak') !== false) {
            $result['peak'] = $value;
        } elseif (strpos($label, 'hbm') !== false) {
            $result['hbm'] = $value;
        } elseif (strpos($label, 'typical') !== false) {
            $result['typical'] = $value;
        }
    }

    return $result;
}

function extractScreenGlassType(?string $protection): ?array
{
    if (!$protection || empty(trim($protection))) {
        return null;
    }

    // Step 3: Normalize
    $protection = strtolower(trim($protection));

    // Step 4: Extract glass type
    return parseGlassProtection($protection);
}

function parseGlassProtection(?string $text): array
{
    if (!$text) {
        return emptyGlassResult();
    }

    $text = strtolower($text);

    $glassTypes = [
        'Gorilla Glass' => [
            'keywords' => [
                'gorilla glass',
                'gorilla armor',
                'gorilla victus',
                'gorilla dx',
                'corning gorilla'
            ],
            'version_regex' => '/gorilla\s*(?:glass\s*)?(armor\s*\d*|victus\s*3|victus\s*2|victus\s*\+|victus|dx\+|dx|7i|7|6|5|4|3|2|ceramic\s*\+?\s*\d*|\d+)/i',
            'brand' => 'Corning',
            'ranking' => [
                'armor' => 100,
                'victus 3' => 100,
                'victus 2' => 95,
                'victus+' => 95,
                'victus +' => 95,
                'victus' => 90,
                'dx+' => 88,
                'dx' => 86,
                '7i' => 85,
                '7' => 85,
                '6' => 75,
                '5' => 65,
                '4' => 55,
                '3' => 50,
                '2' => 45,
                '1' => 40,
            ]
        ],

        'Ceramic Shield' => [
            'keywords' => ['ceramic shield'],
            'version_regex' => '/ceramic\s*shield\s*(latest|gen\s*2|2nd|2|\d+)?/i',
            'brand' => 'Apple',
            'ranking' => [
                'latest' => 100,
                '2' => 100,
                'gen 2' => 100,
                '2nd' => 100,
                '1' => 92,
                '' => 92, // Default ceramic shield
            ]
        ],

        'Dragon Crystal Glass' => [
            'keywords' => ['dragon crystal', 'longqing'],
            'version_regex' => '/(?:dragon\s*crystal|longqing)\s*(?:glass\s*)?(\d+)?/i',
            'brand' => 'Xiaomi',
            'ranking' => [
                '3' => 100,
                '2' => 92,
                '1' => 85,
                '' => 85,
            ]
        ],

        'Kunlun Glass' => [
            'keywords' => ['kunlun'],
            'version_regex' => '/kunlun\s*glass\s*(\d+)?/i',
            'brand' => 'Huawei',
            'ranking' => [
                '2' => 95,
                '1' => 92,
                '' => 92,
            ]
        ],

        'Dragontrail Glass' => [
            'keywords' => ['dragontrail'],
            'version_regex' => '/dragontrail\s*(?:glass\s*)?(pro|star\s*2|star|x|\d+)?/i',
            'brand' => 'AGC Asahi',
            'ranking' => [
                'pro' => 75,
                'star 2' => 70,
                'star' => 68,
                'x' => 65,
                '' => 60,
            ]
        ],

        'Schott Xensation' => [
            'keywords' => ['xensation'],
            'version_regex' => '/xensation\s*(up|alpha|cover|3d|\d+)?/i',
            'brand' => 'Schott',
            'ranking' => [
                'up' => 85,
                'alpha' => 80,
                'cover' => 75,
                '3d' => 70,
                '' => 70,
            ]
        ],

        'Panda Glass' => [
            'keywords' => ['panda glass', 'panda king kong'],
            'version_regex' => '/panda\s*(?:king\s*kong\s*)?glass\s*(\d+)?/i',
            'brand' => 'Tunghsu',
            'ranking' => [
                'king kong' => 92,
                '2' => 85,
                '1' => 75,
                '' => 70,
            ]
        ],

        'Sapphire Crystal' => [
            'keywords' => ['sapphire crystal', 'sapphire glass'],
            'version_regex' => '/sapphire\s*(?:crystal|glass)/i',
            'brand' => 'Sapphire',
            'ranking' => [
                '' => 95, // Very scratch resistant but can shatter
            ]
        ],

        'Dinorex Glass' => [
            'keywords' => ['dinorex'],
            'version_regex' => '/dinorex\s*(?:glass\s*)?(\d+)?/i',
            'brand' => 'AGC',
            'ranking' => [
                '' => 65,
            ]
        ],

        'Asahi Glass' => [
            'keywords' => ['asahi glass'],
            'version_regex' => '/asahi\s*glass/i',
            'brand' => 'AGC',
            'ranking' => [
                '' => 70,
            ]
        ],

        'Aluminosilicate Glass' => [
            'keywords' => ['aluminosilicate'],
            'version_regex' => '/aluminosilicate\s*glass/i',
            'brand' => 'Generic',
            'ranking' => [
                '' => 50,
            ]
        ],

        'Tempered Glass' => [
            'keywords' => ['tempered glass'],
            'version_regex' => '/tempered\s*glass/i',
            'brand' => 'Generic',
            'ranking' => [
                '' => 40,
            ]
        ],

        'Toughened Glass' => [
            'keywords' => ['toughened glass', 'reinforced glass', 'scratch-resistant', 'drop-resistant'],
            'version_regex' => '/(?:toughened|reinforced|scratch|drop)-?resistant\s*glass/i',
            'brand' => 'Generic',
            'ranking' => [
                '' => 35,
            ]
        ],
    ];

    $result = emptyGlassResult();

    /* ---------- Detect glass type ---------- */
    foreach ($glassTypes as $base => $config) {
        foreach ($config['keywords'] as $keyword) {
            if (!str_contains($text, $keyword)) {
                continue;
            }

            $result['glass_name'] = $base;
            $result['brand'] = $config['brand'];
            $result['has_branded_glass'] = strtolower($config['brand']) !== 'generic';

            /* ---------- Version ---------- */
            if (
                !empty($config['version_regex']) &&
                preg_match($config['version_regex'], $text, $m)
            ) {
                $version = '';
                if (!empty($m[1])) {
                    $version = trim(preg_replace('/\s+/', ' ', $m[1]));
                    $result['version'] = ucwords($version);
                }

                // Strength score
                $key = strtolower($version);
                if (isset($config['ranking'][$key])) {
                    $result['strength_score'] = $config['ranking'][$key];
                } elseif (isset($config['ranking'][''])) {
                    // Fallback to base ranking if no version specified
                    $result['strength_score'] = $config['ranking'][''];
                }
            } elseif (isset($config['ranking'][''])) {
                // No regex but has base ranking
                $result['strength_score'] = $config['ranking'][''];
            }

            break 2;
        }
    }

    /* ---------- Mohs level ---------- */
    if (preg_match('/mohs\s*(?:level|hardness)\s*(\d+)/i', $text, $m)) {
        $result['mohs_level'] = (int) $m[1];
    }

    /* ---------- Front / Back detection ---------- */
    if (preg_match('/\b(front|back|both)\b/i', $text, $m)) {
        $result['applies_to'] = strtolower($m[1]);
    }

    return $result;
}

function emptyGlassResult(): array
{
    return [
        'glass_name' => null,
        'version' => null,
        'brand' => null,
        'mohs_level' => null,
        'has_branded_glass' => false,
        'strength_score' => null,
        'applies_to' => 'both', // front | back | both
    ];
}

function formatGlassProtection(array $data): string
{
    $text = '';

    // if (!empty($data['brand'])) $text .= $data['brand'] . ' ';
    if (!empty($data['glass_name']))
        $text .= $data['glass_name'];
    if (!empty($data['version']))
        $text .= ' ' . $data['version'];
    if (!empty($data['applies_to']) && $data['applies_to'] !== 'both') {
        $text .= ' (' . ucfirst($data['applies_to']) . ')';
    }

    return trim($text) ?: 'N/A';
}

function formatUsbLabel(?string $usb): ?string
{
    if (empty($usb)) {
        return null;
    }

    $type = null;
    $version = null;
    $generation = null;

    // TYPE-C / TYPE-A
    if (preg_match('/Type[-\s]?([A-Z])/i', $usb, $m)) {
        $type = 'TYPE-' . strtoupper($m[1]);
    }

    // USB version
    if (preg_match('/\b(2\.0|3\.0|3\.1|3\.2|4)\b/', $usb, $m)) {
        $version = $m[1];
    }

    // GEN
    if (preg_match('/Gen\s*(\d+(x\d+)?)/i', $usb, $m)) {
        $generation = 'GEN ' . strtoupper($m[1]);
    }

    if ($type && $version) {
        return $generation
            ? "{$type} {$version} ({$generation})"
            : "{$type} {$version}";
    }

    return null;
}

function formatWifiValue(?string $wifi): ?string
{
    if (empty($wifi)) {
        return null;
    }

    $version = null;
    $band = null;

    // Highest Wi-Fi version
    if (preg_match('/\b7\b/', $wifi)) {
        $version = '7';
    } elseif (stripos($wifi, '6e') !== false) {
        $version = '6E';
    } elseif (preg_match('/\b6\b/', $wifi)) {
        $version = '6';
    } elseif (stripos($wifi, 'ac') !== false) {
        $version = '5';
    }

    // Band
    if (stripos($wifi, 'tri-band') !== false) {
        $band = 'TRI-BAND';
    } elseif (stripos($wifi, 'dual-band') !== false) {
        $band = 'DUAL-BAND';
    }

    if ($version && $band) {
        return "{$version} ({$band})";
    }

    return $version;
}

function parseBatteryType(?string $type): ?string
{
    if (!$type) {
        return null;
    }

    $normalized = strtolower(trim($type));

    // Check for specific types
    if (preg_match('/si\/c|silicon[\s\-]?carbon/i', $normalized)) {
        return 'silicon-carbon';
    }

    if (preg_match('/graphene/i', $normalized)) {
        return 'graphene';
    }

    if (preg_match('/li[\s\-]?po|lithium[\s\-]?polymer/i', $normalized)) {
        return 'li-po';
    }

    if (preg_match('/li[\s\-]?ion|lithium[\s\-]?ion/i', $normalized)) {
        return 'li-ion';
    }

    return null;
}

function parseBatteryCapacity(?string $capacity): ?int
{
    if (!$capacity) {
        return null;
    }

    // Extract number from "7400 mAh" or "7400mAh" or "7400"
    if (preg_match('/(\d+)\s*m?ah?/i', $capacity, $matches)) {
        return (int) $matches[1];
    }

    // Just a number
    if (preg_match('/(\d+)/', $capacity, $matches)) {
        return (int) $matches[1];
    }

    return null;
}

function parseChargingSpeed(?string $chargingSpeed): array
{
    if (!$chargingSpeed) {
        return [
            'wired' => null,
            'wireless' => null,
            'technology' => null,
        ];
    }

    $result = [
        'wired' => null,
        'wireless' => null,
        'technology' => null,
    ];

    $normalized = strtolower(trim($chargingSpeed));

    // Extract wired charging: "80W wired" or "80W" or "80 watt"
    if (preg_match('/(\d+)\s*w(?:att)?(?:s)?\s*(?:wired)?/i', $normalized, $matches)) {
        if (stripos($normalized, 'wireless') === false) {
            $result['wired'] = (int) $matches[1];
        }
    }

    // Extract wireless charging: "50W wireless" or "wireless 50W"
    if (preg_match('/(?:wireless|qi).*?(\d+)\s*w/i', $normalized, $matches)) {
        $result['wireless'] = (int) $matches[1];
    } elseif (preg_match('/(\d+)\s*w.*?(?:wireless|qi)/i', $normalized, $matches)) {
        $result['wireless'] = (int) $matches[1];
    }

    // Detect charging technology
    $result['technology'] = detectChargingTechnology($normalized);

    return $result;
}

function detectChargingTechnology(string $text): ?string
{
    $technologies = [
        '/hypercharge/i' => 'hypercharge',
        '/supervooc/i' => 'supervooc',
        '/vooc/i' => 'vooc',
        '/warp[\s\-]?charge/i' => 'warp charge',
        '/dash[\s\-]?charge/i' => 'dash charge',
        '/super[\s\-]?fast[\s\-]?charging[\s\-]?2\.0/i' => 'super fast charging 2.0',
        '/super[\s\-]?fast[\s\-]?charging/i' => 'super fast charging',
        '/adaptive[\s\-]?fast[\s\-]?charging/i' => 'adaptive fast charging',
        '/turbopower/i' => 'turbopower',
        '/pump[\s\-]?express/i' => 'pump express',
        '/flexcharge/i' => 'flexcharge',
        '/usb[\s\-]?pd|power[\s\-]?delivery/i' => 'usb-pd',
        '/quick[\s\-]?charge[\s\-]?5/i' => 'quick charge 5',
        '/quick[\s\-]?charge[\s\-]?4\+/i' => 'quick charge 4+',
        '/quick[\s\-]?charge[\s\-]?4/i' => 'quick charge 4',
        '/quick[\s\-]?charge[\s\-]?3\+/i' => 'quick charge 3+',
        '/quick[\s\-]?charge[\s\-]?3/i' => 'quick charge 3.0',
        '/quick[\s\-]?charge[\s\-]?2/i' => 'quick charge 2.0',
        '/quick[\s\-]?charge/i' => 'quick charge',
    ];

    foreach ($technologies as $pattern => $tech) {
        if (preg_match($pattern, $text)) {
            return $tech;
        }
    }

    return null;
}

function getBenchmark($benchmark)
{
    // AnTuTu
    preg_match('/AnTuTu\s*v?\(?\d+\)?:\s*(\d+)/i', $benchmark, $antutuMatch);
    $antutu = (int) ($antutuMatch[1] ?? 0);

    // GeekBench v6 Single & Multi
    preg_match(
        '/GeekBench\s*v?\(?6\)?:\s*(\d+)\s*\(S\)\s*-\s*(\d+)\s*\(M\)/i',
        $benchmark,
        $geekMatch
    );

    $geekSingle = (int) ($geekMatch[1] ?? 0);
    $geekMulti = (int) ($geekMatch[2] ?? 0);
    return [
        'antutu' => $antutu,
        'geekbench_single' => $geekSingle,
        'geekbench_multi' => $geekMulti,
    ];
}

function estimateThrottling($chipset): ?int
{
    if (preg_match('/Snapdragon 8 Gen 3/i', $chipset))
        return 85;
    if (preg_match('/Snapdragon 8 Gen 2/i', $chipset))
        return 82;
    if (preg_match('/Snapdragon 8\+? Gen 1/i', $chipset))
        return 70;
    if (preg_match('/Dimensity 9300/i', $chipset))
        return 80;
    if (preg_match('/Dimensity 9200/i', $chipset))
        return 78;
    if (preg_match('/Tensor G[34]/i', $chipset))
        return 70;
    if (preg_match('/Snapdragon 7 Gen/i', $chipset))
        return 88;

    return null; // Don't guess unknown chips
}

function estimateAICapability($chipset): ?int
{
    if (preg_match('/Snapdragon 8 Gen 3|A18 Pro/i', $chipset))
        return 85;
    if (preg_match('/Snapdragon 8 Gen 2|Dimensity 9300|Tensor G4/i', $chipset))
        return 70;
    if (preg_match('/Snapdragon 8 Gen 1|Tensor G3|A17 Pro/i', $chipset))
        return 55;
    if (preg_match('/Snapdragon 8s Gen 3|Snapdragon 7\+ Gen/i', $chipset))
        return 50;
    if (preg_match('/Dimensity 8[23]00/i', $chipset))
        return 45;

    return null;
}
function extractSensorSize($mainSensor)
{
    if (!empty($mainSensor)) {
        // Extract sensor size like: 1/1.4", 1/1.56", 1/1.12"
        preg_match('/(\d+)\/(\d+\.?\d*)"/', $mainSensor, $matches);

        if (isset($matches[1]) && isset($matches[2])) {
            $numerator = (float) $matches[1];   // 1
            $denominator = (float) $matches[2]; // 1.4
            $decimal = $numerator / $denominator; // For scoring
            $fraction = $matches[0]; // "1/1.4\"" - For display

            return [
                'value' => $decimal,      // 0.714 (for scoring)
                'display' => $fraction,   // "1/1.4\"" (for UI)
            ];
        }
    }


    return null;
}

function extractFrontAperture($sensor)
{
    if (!empty($mainSensor)) {
        // Extract: "12 MP, f/2.2, (wide)"
        preg_match('/f\/(\d+\.?\d*)/', $sensor, $matches);
        return $matches[0] ?? null; // Returns "f/2.2"
    }

    return null;
}

function extractChargingTime(string $chargingSpeed): ?int
{
    // Match patterns like: "100% in 53 min", "0-100% in 25 minutes", "full charge in 30min"
    if (preg_match('/(?:100%|full charge)\s+in\s+(\d+)\s*min/i', $chargingSpeed, $matches)) {
        return (int) $matches[1];
    }

    if (preg_match('/0-100%\s+in\s+(\d+)\s*min/i', $chargingSpeed, $matches)) {
        return (int) $matches[1];
    }

    return null;
}

function extractChargingTime50(string $chargingSpeed): ?int
{
    // Match patterns like: "50% in 12 min", "0-50% in 15 minutes"
    if (preg_match('/50%\s+in\s+(\d+)\s*min/i', $chargingSpeed, $matches)) {
        return (int) $matches[1];
    }

    if (preg_match('/0-50%\s+in\s+(\d+)\s*min/i', $chargingSpeed, $matches)) {
        return (int) $matches[1];
    }

    return null;
}

function parseWirelessChargingToWatts($value)
{
    if (!$value || $value === 'N/A' || $value === '-' || $value === 'No') {
        return 0;
    }

    $value = strtoupper(trim($value));

    // Explicit wireless wattage
    if (preg_match('/(\d+(?:\.\d+)?)\s*W\s+(?:WIRELESS|W\/LESS)/i', $value, $matches)) {
        return floatval($matches[1]);
    }

    if (preg_match('/(?:WIRELESS|W\/LESS)\s+(\d+(?:\.\d+)?)\s*W/i', $value, $matches)) {
        return floatval($matches[1]);
    }

    // Proprietary wireless charging
    if (str_contains($value, '50W WIRELESS'))
        return 50;  // Xiaomi/OPPO
    if (str_contains($value, '30W WIRELESS'))
        return 30;
    if (str_contains($value, '25W WIRELESS'))
        return 25;
    if (str_contains($value, 'MAGSAFE'))
        return 15;       // Apple
    if (str_contains($value, 'QI2'))
        return 15;           // Qi2 standard
    if (str_contains($value, 'QI'))
        return 7.5;           // Standard Qi

    // Generic wireless
    if (str_contains($value, 'WIRELESS CHARGING') || str_contains($value, 'WIRELESS')) {
        return 10; // Assume basic wireless
    }

    // Any wattage in the string
    if (preg_match('/(\d+(?:\.\d+)?)\s*W/i', $value, $matches)) {
        return floatval($matches[1]);
    }

    return 0;
}

function parseFingerprintType(string $sensors = ''): ?string
{
    if (empty($sensors) || $sensors === 'N/A' || $sensors === '-') {
        return null;
    }

    $sensors = strtolower(trim($sensors));

    // No fingerprint at all
    if (
        strpos($sensors, 'fingerprint') === false &&
        strpos($sensors, 'finger print') === false &&
        strpos($sensors, 'fp') === false
    ) {
        return null;
    }

    // Extract text inside brackets/parentheses
    $type = '';
    if (preg_match('/fingerprint\s*\((.*?)\)/i', $sensors, $matches)) {
        $type = strtolower(trim($matches[1]));
    } elseif (preg_match('/fingerprint\s*\[(.*?)\]/i', $sensors, $matches)) {
        $type = strtolower(trim($matches[1]));
    } elseif (preg_match('/fingerprint[,\s]+([^,;]+)/i', $sensors, $matches)) {
        // Match "Fingerprint, under display optical" format
        $type = strtolower(trim($matches[1]));
    } else {
        // Fingerprint mentioned but no type specified
        $type = $sensors;
    }

    // Priority-based detection (most specific first)

    // 1. Ultrasonic (premium)
    if (
        strpos($type, 'ultrasonic') !== false ||
        strpos($type, 'ultra sonic') !== false ||
        strpos($type, '3d sonic') !== false
    ) {
        return 'ultrasonic in-display';
    }

    // 2. Optical in-display
    if (
        strpos($type, 'optical') !== false &&
        (strpos($type, 'under display') !== false ||
            strpos($type, 'in-display') !== false ||
            strpos($type, 'under-display') !== false ||
            strpos($type, 'screen') !== false)
    ) {
        return 'optical in-display';
    }

    // 3. Generic optical (assume in-display if not specified)
    if (strpos($type, 'optical') !== false) {
        return 'optical in-display';
    }

    // 4. Generic in-display (assume optical)
    if (
        strpos($type, 'under display') !== false ||
        strpos($type, 'in-display') !== false ||
        strpos($type, 'under-display') !== false ||
        strpos($type, 'on-screen') !== false ||
        strpos($type, 'screen') !== false
    ) {
        return 'optical in-display';
    }

    // 5. Side-mounted (common in mid-range)
    if (
        strpos($type, 'side') !== false ||
        strpos($type, 'side-mounted') !== false ||
        strpos($type, 'power button') !== false
    ) {
        return 'side-mounted';
    }

    // 6. Rear-mounted (older phones)
    if (
        strpos($type, 'rear') !== false ||
        strpos($type, 'back') !== false ||
        strpos($type, 'rear-mounted') !== false ||
        strpos($type, 'back-mounted') !== false
    ) {
        return 'rear-mounted';
    }

    // 7. Front-mounted (home button)
    if (
        strpos($type, 'front') !== false ||
        strpos($type, 'home button') !== false ||
        strpos($type, 'front-mounted') !== false
    ) {
        return 'front-mounted';
    }

    // 8. Capacitive (generic)
    if (strpos($type, 'capacitive') !== false) {
        return 'capacitive';
    }

    // Fallback: if "fingerprint" exists but no specific type
    return 'capacitive';
}

function parseSimType(string $simHtml): string
{
    if (empty($simHtml) || $simHtml === 'N/A' || $simHtml === '-') {
        return 'single sim';
    }

    // Remove HTML tags first
    $simText = strip_tags($simHtml);

    // Decode HTML entities (&nbsp; etc.)
    $simText = html_entity_decode($simText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Normalize whitespace and convert to lowercase
    $simText = strtolower(trim($simText));
    $simText = preg_replace('/\s+/', ' ', $simText); // Collapse multiple spaces

    // Count different SIM types
    $hasISim = stripos($simText, 'isim') !== false;
    $hasESim = stripos($simText, 'esim') !== false;

    // Count physical nano-SIM slots
    preg_match_all('/nano-sim/i', $simText, $nanoMatches);
    $nanoSimCount = count($nanoMatches[0]);

    // Detect dual physical SIM
    $hasDualPhysical = (
        $nanoSimCount >= 2 ||
        stripos($simText, 'dual sim') !== false ||
        stripos($simText, 'dual-sim') !== false ||
        stripos($simText, 'hybrid') !== false ||
        stripos($simText, 'dual standby') !== false
    );

    // Count eSIM occurrences
    preg_match_all('/esim/i', $simText, $esimMatches);
    $esimCount = count($esimMatches[0]);
    $hasDualESim = $esimCount >= 2;

    // Priority-based detection

    // 1. iSIM + eSIM + Physical NanoSIM (Ultra Premium)
    if ($hasISim && $hasESim && $nanoSimCount >= 1) {
        return 'iSIM + eSIM + NanoSIM';
    }

    // 2. iSIM + eSIM (2026 Premium)
    if ($hasISim && $hasESim) {
        return 'iSIM + eSIM';
    }

    // 3. Dual Physical + eSIM (2025/26 Flagship)
    if ($hasDualPhysical && $hasESim) {
        return 'dual sim + esim';
    }

    // 4. Dual eSIM (Apple style)
    if ($hasDualESim && $nanoSimCount === 0) {
        return 'dual esim';
    }

    // 5. Single Physical + eSIM (Standard Mid-range)
    if ($nanoSimCount === 1 && $hasESim) {
        return 'single sim + esim';
    }

    // 6. eSIM Only (No physical slot)
    if ($hasESim && $nanoSimCount === 0 && !$hasDualPhysical) {
        return 'esim only';
    }

    // 7. Dual Physical SIM (No eSIM)
    if ($hasDualPhysical) {
        return 'dual sim';
    }

    // 8. Single Physical SIM (Budget)
    return 'single sim';
}

function getSimplifiedCpuSpeed($cpuString): ?string
{
    if (!empty($cpuString)) {

        // Allow spaces around x
        preg_match_all('/(\d+)\s*x\s*(\d+\.?\d*)\s*GHz/i', $cpuString, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return null;
        }

        $clusters = [];
        foreach ($matches as $match) {
            $coreCount = $match[1];
            $frequency = $match[2];
            $clusters[] = "$coreCount x $frequency GHz";
        }

        return implode(' & ', $clusters);
    }

    return null;
}

