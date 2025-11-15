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
        return $this->hasMany(PhoneSpecification::class)->orderBy('order','asc');
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
        return $this->specifications()
            ->where('category', $category)
            ->first()?->specifications ?? [];
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('status', 'published');
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

}

   
