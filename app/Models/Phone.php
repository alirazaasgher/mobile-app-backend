<?php

// app/Models/Phone.php
namespace App\Models;

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
        echo "<pre>";
        print_r($s);
        exit;
        try {
            $chargingSpec = $s['battery']['charging_speed'] ?? '';
            $wirlessCharging = $s['battery']['wireless'] ?? '';
            $reverceCharging = $s['battery']['reverse'] ?? '';
            $chargingSpec = shortChargingSpec($chargingSpec, $wirlessCharging, $reverceCharging);
            $screenGlassType = $this->extractScreenGlassType($s['display']['protection']);
            $formatGlassProtection = $this->formatGlassProtection($screenGlassType);
            return [
                'key' => [
                    'display' => [
                        'size' => $this->extractSize($s['display']['size'] ?? null),
                        'type' => getShortDisplay($s['display']['type'] ?? null),
                        'resolution' => $this->shortResolution($s['display']['resolution'] ?? null),
                        'refresh_rate' => $this->extractNumber($s['display']['refresh_rate'] ?? null),
                        "pixel_density" => $this->extractPpi($s['display']['resolution'] ?? null),
                        'brightness_(peak)' => $this->extractBrightness($s['display']['brightness'], "peak"),
                        'brightness_(typical)' => $this->extractBrightness($s['display']['brightness'], "typical"),
                        'glass_protection' => $formatGlassProtection,
                        'has_branded_glass' => $screenGlassType['has_branded_glass'],

                    ],
                    'performance' => [
                        'chipset' => getShortChipset($s['performance']['chipset'] ?? null),
                        'cpu' => cpuType($s['performance']['cpu']) ?? null,
                        'gpu' => $s['performance']['gpu'] ?? null,

                    ],
                    'memory' => $s['memory'],
                    'camera' => [
                        'main' => $s['main_camera']['setup'] ?? null,
                        'front' => $s['selfie_camera']['setup'] ?? null,
                        'main_video' => getVideoHighlight($s['main_camera']['video'] ?? null),
                        'front_video' => getVideoHighlight($s['selfie_camera']['video'] ?? null),
                    ],
                    'battery' => [
                        'capacity' => $s['battery']['capacity'] ?? null,
                        'Fast' => $chargingSpec['fastCharging'] ?? null,
                        'Wirless' => $chargingSpec['convertWirlessCharging'] ?? null,
                        'Reverce' => $chargingSpec['convertReverceCharging'] ?? null,
                    ],

                    'software' => [
                        'os' => $this->shortOS($s['performance']['os'] ?? null),
                        'updates' => $s['performance']['update_policy'] ?? null,
                    ],

                    // 'build' => [
                    //     'dimensions' => $s['build']['dimensions'] ?? null,
                    //     'weight' => $s['build']['weight'] ?? null,
                    //     'build' => $s['build']['build'] ?? null,
                    //     'ip_rating' => shortIPRating($s['build']['ip_rating']) ?? null,
                    // ],

                    'features' => [
                        'nfc' => $s['connectivity']['nfc'] ?? null,
                        'stereo_speakers' => $s['audio']['stereo'] ?? null,
                        '3.5mm_jack' => $s['audio']['3.5mm_jack'] ?? null,
                        'wifi' => $this->formatWifiValue($s['connectivity']['wifi']),
                        'bluetooth_version' => isset($s['connectivity']['bluetooth'])
                            ? (preg_match('/v([\d.]+)/i', $s['connectivity']['bluetooth'], $m) ? $m[1] : null)
                            : null,
                        'nfc' => $s['connectivity']['usb'] ?? null,
                        'usb' => $this->formatUsbLabel($s['connectivity']['usb']),


                        // '5g' => $s['connectivity']['5g'] ?? null,
                    ],
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

    private function extractPpi(?string $resolution): ?string
    {
        if (!$resolution) {
            return null;
        }

        if (preg_match('/~\s*(\d+)\s*ppi/i', $resolution, $matches)) {
            return $matches[1] . ' ppi';
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
            return $matches[1] . ' nits';
        }

        // Match: typical 3000 nits (even if separated by comma)
        if ($type === 'typical' && preg_match('/typical[^\d]*(\d+)\s*nits/', $brightness, $matches)) {
            return $matches[1] . ' nits';
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
                'keywords' => ['gorilla glass'],
                'version_regex' => '/gorilla\s*glass\s*(victus\s*2|victus|dx\+|dx|7i|ceramic\+?\s*\d*|\d+)/i',
                'brand' => 'Corning',
                'ranking' => [
                    'victus 2' => 100,
                    'victus' => 95,
                    'dx+' => 90,
                    'dx' => 88,
                    '7i' => 85,
                ]
            ],

            'Ceramic Shield' => [
                'keywords' => ['ceramic shield'],
                'version_regex' => '/ceramic\s*shield\s*(\d+)?/i',
                'brand' => 'Apple',
                'ranking' => [
                    '2' => 92,
                    '1' => 88,
                ]
            ],

            'Dragontrail Glass' => [
                'keywords' => ['dragontrail'],
                'version_regex' => '/dragontrail\s*(pro|x|\d+)?/i',
                'brand' => 'Asahi',
                'ranking' => []
            ],

            'Schott Xensation' => [
                'keywords' => ['xensation'],
                'version_regex' => '/xensation\s*(up|cover|3d|\d+)?/i',
                'brand' => 'Schott',
                'ranking' => []
            ],

            'Kunlun Glass' => [
                'keywords' => ['kunlun'],
                'version_regex' => '/kunlun\s*glass\s*(\d+)?/i',
                'brand' => 'Huawei',
                'ranking' => []
            ],

            'Panda Glass' => [
                'keywords' => ['panda glass'],
                'version_regex' => '/panda\s*glass\s*(\d+)?/i',
                'brand' => 'Panda',
                'ranking' => []
            ],

            'Sapphire Crystal' => [
                'keywords' => ['sapphire crystal', 'sapphire glass'],
                'version_regex' => null,
                'brand' => 'Sapphire',
                'ranking' => []
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
                $result['has_branded_glass'] = true;

                /* ---------- Version ---------- */
                if (
                    !empty($config['version_regex']) &&
                    preg_match($config['version_regex'], $text, $m) &&
                    !empty($m[1])
                ) {

                    $version = trim(preg_replace('/\s+/', ' ', $m[1]));
                    $result['version'] = ucwords($version);

                    // Strength score
                    $key = strtolower($version);
                    if (isset($config['ranking'][$key])) {
                        $result['strength_score'] = $config['ranking'][$key];
                    }
                }

                break 2;
            }
        }

        /* ---------- Mohs level ---------- */
        if (preg_match('/mohs\s*level\s*(\d+)/i', $text, $m)) {
            $result['mohs_level'] = (int) $m[1];
        }

        /* ---------- Front / Back detection ---------- */
        if (str_contains($text, 'front')) {
            $result['applies_to'] = 'front';
        } elseif (str_contains($text, 'back')) {
            $result['applies_to'] = 'back';
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
}
