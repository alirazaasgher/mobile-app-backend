<?php

// app/Models/Phone.php
namespace App\Models;

use App\Services\CompareScoreService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends Model
{
    protected $fillable = [
        'brand_id',
        'slug',
        'description',
        'name',
        'tagline',
        'primary_image',
        'primary_color',
        'status',
        'deleted',
        'popularity_score',
        'avg_rating',
        'total_reviews',
        'announced_date',
        'release_date'
    ];

    protected $casts = [
        'avg_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'popularity_score' => 'integer',
        'announced_date' => 'date',
        'release_date' => 'date',
    ];

    public function specifications(): HasMany
    {
        return $this->hasMany(PhoneSpecification::class)->orderBy('order', 'asc');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function searchIndex(): HasOne
    {
        return $this->hasOne(PhoneSearchIndex::class);
    }

    // public function reviews(): HasMany
    // {
    //     return $this->hasMany(PhoneReview::class);
    // }

    public function getSpecsByCategory(string $category)
    {
        $spec = $this->specifications
            ->firstWhere('category', $category)
                ?->specifications ?? [];

        return json_decode($spec, true) ?: [];
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('deleted', 0);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('popularity_score', 'desc');
    }

    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    // Load optimized data for listing
    public function scopeWithListingData($query)
    {
        return $query->with([
            'searchIndex:phone_id,screen_size_inches',
        ]);
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        if (!empty($filters['ram'])) {
            $query->whereIn('ram', $filters['ram']);
        }

        if (!empty($filters['storage'])) {
            $query->whereIn('storage', $filters['storage']);
        }

        if (!empty($filters['display_size_min'])) {
            $query->where('display_size', '>=', $filters['display_size_min']);
        }

        if (!empty($filters['display_size_max'])) {
            $query->where('display_size', '<=', $filters['display_size_max']);
        }

        if (!empty($filters['camera_min'])) {
            $query->where('camera_mp', '>=', $filters['camera_min']);
        }

        if (!empty($filters['battery_min'])) {
            $query->where('battery_capacity', '>=', $filters['battery_min']);
        }

        if (!empty($filters['os'])) {
            $query->whereIn('os', $filters['os']);
        }

        if (isset($filters['has_5g'])) {
            $query->where('has_5g', $filters['has_5g']);
        }

        if (isset($filters['has_wireless_charging'])) {
            $query->where('has_wireless_charging', $filters['has_wireless_charging']);
        }

        if (!empty($filters['color'])) {
            $query->whereIn('color', $filters['color']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%");
                // ->orWhere('brand', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['sort_by']) && !empty($filters['sort_order'])) {
            $query->orderBy($filters['sort_by'], $filters['sort_order']);
        }

        return $query;
    }

    public function colors(): HasMany
    {
        return $this->hasMany(PhoneColor::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function competitors()
    {
        return $this->belongsToMany(Phone::class, 'mobile_competitors', 'mobile_id', 'competitor_id');
    }

    // In App\Models\Phone.php

    public function getCompareSpecsAttribute(): array
    {
        $s = $this->specifications->keyBy('category')
            ->map(fn($spec) => json_decode($spec->specifications, true) ?: []);
        $buildMaterials = $this->buildMaterials($s['build']['build']);
        $mobileDimensions = $this->getMobileDimensions($s['build']['dimensions'] ?? []);
        $cameraApertures = $this->extractCameraApertures($s['main_camera']);
        $cameraOpticalZoom = $this->extractOpticalZoom($s['main_camera']);
        $cameratabilization = $this->extractStabilization($s['main_camera']);
        $cameraSetup = $this->parseCameraSetup($s['main_camera']['setup']);
        $cameraFlash = $this->getFlash($s['main_camera']);
        $cameraVideo = $this->extractVideo($s['main_camera']['video']);
        $setup = $s['selfie_camera']['setup'] ?? ''; // e.g., "Single (50 MP)"
        // Extract the first number
        preg_match('/\d+/', $setup, $matches);
        $frontCameraSetup = $matches[0] ?? null;
        $object = [];
        foreach ($cameraSetup as $value) {
            // Dynamically use 'type' as key and 'mp' as value
            $key = $value['type']; // e.g., 'rear', 'front', 'wide'
            $object[$key] = $value['mp'] ?? null; // fallback to null if 'mp' is missing
        }
        $memoryParsed = $this->parseMemory($s['memory']['memory']);
        $scorer = new CompareScoreService();
        try {
            $wiredChargingSpec = $s['battery']['charging_speed'] ?? '';
            $wirlessCharging = $s['battery']['wireless'] ?? '';
            $reverceCharging = $s['battery']['reverse'] ?? '';
            // $chargingSpec = shortChargingSpec($wiredChargingSpec, $wirlessCharging, $reverceCharging);
            $screenGlassType = $this->extractScreenGlassType($s['display']['protection'] ?? null);
            $formatGlassProtection = $this->formatGlassProtection($screenGlassType ?? []);
            return [
                'key' => [
                    'display' => $scorer->scoreCategory('display', [
                        'size' => $this->extractSize($s['display']['size'] ?? null),
                        'type' => getShortDisplay($s['display']['type'] ?? null),
                        'resolution' => $this->shortResolution($s['display']['resolution'] ?? null),
                        'refresh_rate' => $this->extractNumber($s['display']['refresh_rate'] ?? null),
                        "pixel_density" => $this->extractPpi($s['display']['resolution'] ?? null),
                        'brightness_(peak)' => $this->extractBrightness($s['display']['brightness'] ?? "", "peak"),
                        'brightness_(typical)' => $this->extractBrightness($s['display']['brightness'] ?? "", "typical"),
                        'glass_protection' => $formatGlassProtection,
                        'has_branded_glass' => $screenGlassType['has_branded_glass'] ?? null,
                    ]),
                    'performance' => $scorer->scoreCategory('performance', [
                        'chipset' => getShortChipset($s['performance']['chipset'] ?? null),
                        'ram' => $memoryParsed['ram'],
                        'storage_capacity' => $memoryParsed['storage'],
                        'cpu' => cpuType($s['performance']['cpu']) ?? null,
                        'gpu' => $s['performance']['gpu'] ?? null,
                        'storage_type' => $s['memory']['storage_type'] ?? null,
                        'ram_type' => $s['memory']['ram_type'] ?? null,
                        'card_slot' => $s['memory']['card_slot']

                    ]),
                    'camera' => $scorer->scoreCategory(
                        'camera',
                        array_merge(
                            $object,
                            $cameraApertures, // dynamic camera keys
                            [
                                'optical_zoom' => $cameraOpticalZoom,
                                'stabilization' => $cameratabilization,
                                'flash' => $cameraFlash,
                                'front' => $frontCameraSetup ?? null,
                                'video_resolution' => $cameraVideo ?? null,
                                'front_video' => $this->extractVideo($s['selfie_camera']['video']) ?? null,
                            ]
                        )
                    ),
                    'battery' => $scorer->scoreCategory('battery', [
                        "type" => $this->parseBatteryType($s['battery']['type']),
                        'capacity' => $this->parseBatteryCapacity($s['battery']['capacity']) ?? null,
                        'Fast' => $this->parseFastChargingToWatts($wiredChargingSpec),
                        'Wirless' => $this->parseFastChargingToWatts($wirlessCharging ?? 0),
                        'Reverce' => $this->parseFastChargingToWatts($reverceCharging ?? 0),
                    ]),

                    // 'software' => [
                    //     'os' => $this->shortOS($s['performance']['os'] ?? null),
                    //     'updates' => $s['performance']['update_policy'] ?? null,
                    // ],

                    'build' => $scorer->scoreCategory('build', [
                        'dimensions' => $mobileDimensions['dimensions'] ?? null,
                        'thickness' => $mobileDimensions['thickness'] ?? null,
                        'weight' => $s['build']['weight'] !== null
                            ? (float) preg_replace('/[^0-9.]/', '', $s['build']['weight'])
                            : null,
                        'build_material' => $buildMaterials['build_material'] ?? null,
                        'back_material' => $buildMaterials['back_material'] ?? null,
                        'ip_rating' => shortIPRating($s['build']['ip_rating']) ?? null,
                    ]),

                    'features' => $scorer->scoreCategory('features', [
                        'nfc' => $s['connectivity']['nfc'] ?? null,
                        'stereo_speakers' => $s['audio']['stereo'] ?? null,
                        '3.5mm_jack' => $s['audio']['3.5mm_jack'] ?? null,
                        'wifi' => $this->formatWifiValue($s['connectivity']['wifi']),
                        'bluetooth_version' => isset($s['connectivity']['bluetooth'])
                            ? (preg_match('/v([\d.]+)/i', $s['connectivity']['bluetooth'], $m) ? $m[1] : null)
                            : null,
                        'usb' => $this->formatUsbLabel($s['connectivity']['usb']),
                    ]),
                ]
            ];
        } catch (\Exception $e) {
            \Log::error("Error in getCompareSpecsAttribute for phone {$this->id}: " . $e->getMessage());
            echo "<pre>";
            print_r($e->getMessage());
            exit;
            return ['key' => [], 'expandable' => []];
        }
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
            return 25;      // Qi2 (50% in 30 min)
        if (str_contains($value, 'QI'))
            return 15;       // Standard Qi (slower)

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



    function extractVideo(string $video): string
    {
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

        return 'unknown';
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

    private function parseMemory(?string $memory): array
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
    private function extractNumber($value)
    {
        if (!$value)
            return null;
        preg_match('/\d+/', $value, $matches);
        return $matches[0] ?? null;
    }

    private function extractSize($value)
    {
        if (!$value)
            return null;
        preg_match('/([\d.]+)\s*inch/i', $value, $matches);
        return $matches[1] ?? null;
    }

    private function shortResolution($value)
    {
        if (!$value)
            return null;
        preg_match('/(\d+)\s*x\s*(\d+)/', $value, $matches);
        return isset($matches[1], $matches[2]) ? "{$matches[1]} x {$matches[2]}" : null;
    }

    private function shortOS($value)
    {
        if (!$value)
            return null;
        // "Android 16 ,OneUI 8.0" -> "Android 16"
        // "IOS 26" -> "iOS 26"
        return trim(explode(',', $value)[0]);
    }

    private function extractPpi(?string $resolution, ?float $screenSize = null): ?int
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

    private function extractBrightness(?string $brightness, string $type): ?string
    {
        if (!$brightness) {
            return null;
        }

        $type = strtolower($type);

        // Normalize string
        $brightness = strtolower($brightness);

        // Match: peak 3300 nits
        if ($type === 'peak' && preg_match('/peak\s*(\d+)\s*nits/', $brightness, $matches)) {
            return $matches[1];
        }

        // Match: typical 3000 nits (even if separated by comma)
        if ($type === 'typical' && preg_match('/typical[^\d]*(\d+)\s*nits/', $brightness, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractScreenGlassType(?string $protection): ?array
    {
        if (!$protection || empty(trim($protection))) {
            return null;
        }

        // Step 3: Normalize
        $protection = strtolower(trim($protection));

        // Step 4: Extract glass type
        return $this->parseGlassProtection($protection);
    }

    private function parseGlassProtection(?string $text): array
    {
        if (!$text) {
            return $this->emptyGlassResult();
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

        $result = $this->emptyGlassResult();

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

    private function emptyGlassResult(): array
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

    private function formatGlassProtection(array $data): string
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

        return trim($text) ?: 'Unspecified glass';
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

    private function parseBatteryType(?string $type): ?string
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

    private function parseBatteryCapacity(?string $capacity): ?int
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

    private function parseChargingSpeed(?string $chargingSpeed): array
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
        $result['technology'] = $this->detectChargingTechnology($normalized);

        return $result;
    }

    private function detectChargingTechnology(string $text): ?string
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
}
