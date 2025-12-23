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
        $memoryVariants = $this->variants
            ->map(function ($v) {
                return $v->ram . 'GB / ' . $v->storage . (is_numeric($v->storage) ? 'GB' : '');
            })
            ->values()
            ->toArray();
        try {
            $chargingSpec = $s['battery']['charging_speed'] ?? '';
            $wirlessCharging = $s['battery']['wireless'] ?? '';
            $reverceCharging = $s['battery']['reverse'] ?? '';
            $chargingSpec = shortChargingSpec($chargingSpec, $wirlessCharging, $reverceCharging);
            return [
                'key' => [
                    'battery' => [
                        'capacity' => $s['battery']['capacity'] ?? null,
                        'Fast' => $chargingSpec['fastCharging'] ?? null,
                        'Wirless' => $chargingSpec['convertWirlessCharging'] ?? null,
                        'Reverce' => $chargingSpec['convertReverceCharging'] ?? null,
                    ],
                    'display' => [
                        'size' => $this->extractSize($s['display']['size'] ?? null),
                        'type' => getShortDisplay($s['display']['type'] ?? null),
                        'resolution' => $this->shortResolution($s['display']['resolution'] ?? null),
                        'refresh_rate' => $this->extractNumber($s['display']['refresh_rate'] ?? null),
                    ],
                    'camera' => [
                        'main' => $s['main_camera']['setup'] ?? null,
                        'front' => $s['selfie_camera']['setup'] ?? null,
                        'main_video' => getVideoHighlight($s['main_camera']['video'] ?? null),
                        'front_video' => getVideoHighlight($s['selfie_camera']['video'] ?? null),
                    ],
                    'performance' => [
                        'chipset' => getShortChipset($s['performance']['chipset'] ?? null),
                        'memory' => implode(', ', $memoryVariants)
                    ],
                    'software' => [
                        'os' => $this->shortOS($s['performance']['os'] ?? null),
                        'updates' => $s['performance']['update_policy'] ?? null,
                    ],
                ],
                'expandable' => $s
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
        // "2868 x 1320 (~460 ppi)" -> "2868x1320"
        preg_match('/(\d+)\s*x\s*(\d+)/', $value, $matches);
        return isset($matches[1], $matches[2]) ? "{$matches[1]} x {$matches[2]}" : null;
    }

    private function shortStorage($value)
    {
        if (!$value)
            return null;
        $parts = array_map('trim', explode('/', $value));
        return count($parts) > 1 ? $parts[0] . '-' . end($parts) : $parts[0];
    }

    private function shortOS($value)
    {
        if (!$value)
            return null;
        // "Android 16 ,OneUI 8.0" -> "Android 16"
        // "IOS 26" -> "iOS 26"
        return trim(explode(',', $value)[0]);
    }
}
