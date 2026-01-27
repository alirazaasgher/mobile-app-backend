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

    // public function getCompareSpecsAttribute(): array
    // {
    //     $s = $this->specifications->keyBy('category')
    //         ->map(fn($spec) => json_decode($spec->specifications, true) ?: []);
    //     $buildMaterials = $this->buildMaterials($s['build']['build'] ?? '');
    //     $mobileDimensions = $this->getMobileDimensions($s['build']['dimensions'] ?? []);
    //     $cameraApertures = $this->extractCameraApertures($s['main_camera']);
    //     $cameraOpticalZoom = $this->extractOpticalZoom($s['main_camera']);
    //     $cameratabilization = $this->extractStabilization($s['main_camera']);
    //     $cameraSetup = $this->parseCameraSetup($s['main_camera']['setup']);
    //     $cameraFlash = $this->getFlash($s['main_camera']);
    //     $cameraVideo = $this->extractVideo($s['main_camera']['video']);
    //     $setup = $s['selfie_camera']['setup'] ?? ''; // e.g., "Single (50 MP)"
    //     // Extract the first number
    //     preg_match('/\d+/', $setup, $matches);
    //     $frontCameraSetup = $matches[0] ?? null;
    //     $object = [];
    //     foreach ($cameraSetup as $value) {
    //         // Dynamically use 'type' as key and 'mp' as value
    //         $key = $value['type']; // e.g., 'rear', 'front', 'wide'
    //         $object[$key] = $value['mp'] ?? null; // fallback to null if 'mp' is missing
    //     }
    //     $memoryParsed = $this->parseMemory($s['memory']['memory']);
    //     $scorer = new CompareScoreService();
    //     try {
    //         $wiredChargingSpec = $s['battery']['charging_speed'] ?? '';
    //         $wirlessCharging = $s['battery']['wireless'] ?? '';
    //         $reverceCharging = $s['battery']['reverse'] ?? '';
    //         $screenGlassType = $this->extractScreenGlassType($s['display']['protection'] ?? null);
    //         $formatGlassProtection = $this->formatGlassProtection($screenGlassType ?? []);
    //         return [
    //             'key' => [
    //                 'display' => $scorer->scoreCategory('display', [
    //                     'size' => $this->extractSize($s['display']['size'] ?? null),
    //                     'type' => getShortDisplay($s['display']['type'] ?? null),
    //                     'resolution' => $this->shortResolution($s['display']['resolution'] ?? null),
    //                     'refresh_rate' => $this->extractNumber($s['display']['refresh_rate'] ?? null),
    //                     'screen_ratio' => (float) str_replace('%', '', $s['display']['screen_to_body_ratio'] ?? "N/A"),
    //                     'hdr_support' => $this->getHdrSupport($s['display']['features'] ?? ""),
    //                     "pixel_density" => $this->extractPpi($s['display']['resolution'] ?? null),
    //                     'brightness_(peak)' => $this->extractBrightness($s['display']['brightness'] ?? "", "peak"),
    //                     'brightness_(typical)' => $this->extractBrightness($s['display']['brightness'] ?? "", "typical"),
    //                     'glass_protection' => $formatGlassProtection,
    //                     'has_branded_glass' => $screenGlassType['has_branded_glass'] ?? null,
    //                 ]),
    //                 'performance' => $scorer->scoreCategory('performance', [
    //                     'chipset' => getShortChipset($s['performance']['chipset'] ?? null),
    //                     'os' => $this->mobileVersion($s['performance']['os']),
    //                     'ram' => $memoryParsed['ram'],
    //                     'storage_capacity' => $memoryParsed['storage'],
    //                     'cpu' => cpuType($s['performance']['cpu']) ?? null,
    //                     'gpu' => $s['performance']['gpu'] ?? null,
    //                     'storage_type' => $s['memory']['storage_type'] ?? null,
    //                     'ram_type' => $s['memory']['ram_type'] ?? null,
    //                     'card_slot' => $s['memory']['card_slot']

    //                 ]),
    //                 'camera' => $scorer->scoreCategory(
    //                     'camera',
    //                     array_merge(
    //                         $object,
    //                         $cameraApertures, // dynamic camera keys
    //                         [
    //                             'optical_zoom' => $cameraOpticalZoom,
    //                             'stabilization' => $cameratabilization,
    //                             'flash' => $cameraFlash,
    //                             'front' => $frontCameraSetup ?? null,
    //                             'video_resolution' => $cameraVideo ?? null,
    //                             'front_video' => $this->extractVideo($s['selfie_camera']['video']) ?? null,
    //                         ]
    //                     )
    //                 ),
    //                 'battery' => $scorer->scoreCategory('battery', [
    //                     "type" => $this->parseBatteryType($s['battery']['type']),
    //                     'capacity' => $this->parseBatteryCapacity($s['battery']['capacity']) ?? null,
    //                     'Fast' => $this->parseFastChargingToWatts($wiredChargingSpec),
    //                     'Wirless' => $this->parseFastChargingToWatts($wirlessCharging ?? 0),
    //                     'Reverce' => $this->parseFastChargingToWatts($reverceCharging ?? 0),
    //                 ]),

    //                 // 'software' => [
    //                 //     'os' => $this->shortOS($s['performance']['os'] ?? null),
    //                 //     'updates' => $s['performance']['update_policy'] ?? null,
    //                 // ],

    //                 'build' => $scorer->scoreCategory('build', [
    //                     'dimensions' => $mobileDimensions['dimensions'] ?? null,
    //                     'thickness' => $mobileDimensions['thickness'] ?? null,
    //                     'weight' => $s['build']['weight'] !== null
    //                         ? (float) preg_replace('/[^0-9.]/', '', $s['build']['weight'])
    //                         : null,
    //                     'build_material' => $buildMaterials['build_material'] ?? null,
    //                     'back_material' => $buildMaterials['back_material'] ?? null,
    //                     'ip_rating' => shortIPRating($s['build']['ip_rating']) ?? null,
    //                 ]),

    //                 'features' => $scorer->scoreCategory('features', [
    //                     'nfc' => $s['connectivity']['nfc'] ?? null,
    //                     'stereo_speakers' => $s['audio']['stereo'] ?? null,
    //                     '3.5mm_jack' => $s['audio']['3.5mm_jack'] ?? null,
    //                     "infrared" => $s['connectivity']['infrared'] ?? null,
    //                     'wifi' => $this->formatWifiValue($s['connectivity']['wifi']),
    //                     'bluetooth_version' => isset($s['connectivity']['bluetooth'])
    //                         ? (preg_match('/v([\d.]+)/i', $s['connectivity']['bluetooth'], $m) ? $m[1] : null)
    //                         : null,
    //                     'usb' => $this->formatUsbLabel($s['connectivity']['usb']),
    //                 ]),
    //             ]
    //         ];
    //     } catch (\Exception $e) {
    //         \Log::error("Error in getCompareSpecsAttribute for phone {$this->id}: " . $e->getMessage());
    //         echo "<pre>";
    //         print_r($e->getMessage());
    //         exit;
    //         return ['key' => [], 'expandable' => []];
    //     }
    // }


}
