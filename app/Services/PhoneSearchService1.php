<?php
namespace App\Services;

use App\Models\Phone;
use App\Models\PhoneSearchIndex;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PhoneSearchService
{
    public function searchPhones(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PhoneSearchIndex::query()
            ->join('phones', 'phone_search_index.phone_id', '=', 'phones.id')
            ->where('phone_search_index.is_available', true)
            ->where('phones.status', 'active');

        // Apply filters
        $query = $this->applyFilters($query, $filters);
        
        // Apply sorting
        $query = $this->applySorting($query, $filters);

        // Select optimized fields
        $query->select([
            'phones.id', 'phones.name', 'phones.brand', 'phones.main_image',
            'phone_search_index.min_price', 'phone_search_index.max_price',
            'phone_search_index.ram_gb', 'phone_search_index.main_camera_mp',
            'phone_search_index.screen_size_inches', 'phone_search_index.battery_capacity_mah',
            'phone_search_index.has_5g', 'phone_search_index.avg_rating',
            'phone_search_index.popularity_score'
        ]);

        return $query->paginate($perPage);
    }

    protected function applyFilters($query, array $filters)
    {
        // Text search
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('MATCH(phone_search_index.brand, phone_search_index.model, phone_search_index.name, phone_search_index.search_content) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
                  ->orWhere('phone_search_index.name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone_search_index.brand', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Brand filter
        if (!empty($filters['brand'])) {
            if (is_array($filters['brand'])) {
                $query->whereIn('phone_search_index.brand', $filters['brand']);
            } else {
                $query->where('phone_search_index.brand', $filters['brand']);
            }
        }

        // Price range
        if (!empty($filters['min_price'])) {
            $query->where('phone_search_index.min_price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('phone_search_index.max_price', '<=', $filters['max_price']);
        }

        // RAM filter
        if (!empty($filters['ram_gb'])) {
            if (is_array($filters['ram_gb'])) {
                $query->whereIn('phone_search_index.ram_gb', $filters['ram_gb']);
            } else {
                $query->where('phone_search_index.ram_gb', $filters['ram_gb']);
            }
        }

        // Storage filter
        if (!empty($filters['min_storage'])) {
            $query->where('phone_search_index.min_storage_gb', '>=', $filters['min_storage']);
        }
        if (!empty($filters['max_storage'])) {
            $query->where('phone_search_index.max_storage_gb', '<=', $filters['max_storage']);
        }

        // Screen size
        if (!empty($filters['screen_size'])) {
            $query->where('phone_search_index.screen_size_inches', '>=', $filters['screen_size']);
        }

        // Battery capacity
        if (!empty($filters['battery_min'])) {
            $query->where('phone_search_index.battery_capacity_mah', '>=', $filters['battery_min']);
        }

        // Camera megapixels
        if (!empty($filters['camera_mp'])) {
            $query->where('phone_search_index.main_camera_mp', '>=', $filters['camera_mp']);
        }

        // Feature filters (boolean)
        $booleanFilters = [
            'has_5g', 'has_nfc', 'has_wireless_charging', 'has_fast_charging',
            'has_fingerprint', 'has_face_unlock', 'has_dual_sim',
            'has_expandable_storage', 'has_headphone_jack', 'has_stereo_speakers',
            'has_ultrawide_camera', 'has_telephoto_camera', 'has_macro_camera'
        ];

        foreach ($booleanFilters as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] === true) {
                $query->where("phone_search_index.{$filter}", true);
            }
        }

        // OS filter
        if (!empty($filters['os'])) {
            $query->where('phone_search_index.os', 'LIKE', "%{$filters['os']}%");
        }

        // Display type
        if (!empty($filters['display_type'])) {
            $query->where('phone_search_index.display_type', 'LIKE', "%{$filters['display_type']}%");
        }

        // Refresh rate
        if (!empty($filters['refresh_rate'])) {
            $query->where('phone_search_index.refresh_rate_max', '>=', $filters['refresh_rate']);
        }

        // IP Rating
        if (!empty($filters['ip_rating'])) {
            $query->where('phone_search_index.ip_rating', $filters['ip_rating']);
        }

        return $query;
    }

    protected function applySorting($query, array $filters)
    {
        $sortBy = $filters['sort_by'] ?? 'popularity';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('phone_search_index.min_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('phone_search_index.max_price', 'desc');
                break;
            case 'rating':
                $query->orderBy('phone_search_index.avg_rating', 'desc')
                      ->orderBy('phone_search_index.total_reviews', 'desc');
                break;
            case 'newest':
                $query->orderBy('phones.release_date', 'desc');
                break;
            case 'name':
                $query->orderBy('phone_search_index.name', $sortDirection);
                break;
            case 'popularity':
            default:
                $query->orderBy('phone_search_index.popularity_score', 'desc')
                      ->orderBy('phone_search_index.avg_rating', 'desc');
                break;
        }

        return $query;
    }

    public function getFilterOptions(): array
    {
        return Cache::remember('phone_filter_options', 3600, function () {
            return [
                'brands' => PhoneSearchIndex::select('brand')
                    ->distinct()
                    ->orderBy('brand')
                    ->pluck('brand'),
                
                'price_range' => [
                    'min' => PhoneSearchIndex::min('min_price'),
                    'max' => PhoneSearchIndex::max('max_price'),
                ],
                
                'ram_options' => PhoneSearchIndex::select('ram_gb')
                    ->distinct()
                    ->whereNotNull('ram_gb')
                    ->orderBy('ram_gb')
                    ->pluck('ram_gb'),
                
                'storage_options' => PhoneSearchIndex::selectRaw('min_storage_gb as storage')
                    ->distinct()
                    ->whereNotNull('min_storage_gb')
                    ->orderBy('storage')
                    ->pluck('storage'),
                
                'screen_sizes' => PhoneSearchIndex::select('screen_size_inches')
                    ->distinct()
                    ->whereNotNull('screen_size_inches')
                    ->orderBy('screen_size_inches')
                    ->pluck('screen_size_inches'),
                
                'os_versions' => PhoneSearchIndex::select('os')
                    ->distinct()
                    ->whereNotNull('os')
                    ->orderBy('os')
                    ->pluck('os'),
                
                'display_types' => PhoneSearchIndex::select('display_type')
                    ->distinct()
                    ->whereNotNull('display_type')
                    ->orderBy('display_type')
                    ->pluck('display_type'),
                
                'ip_ratings' => PhoneSearchIndex::select('ip_rating')
                    ->distinct()
                    ->whereNotNull('ip_rating')
                    ->orderBy('ip_rating')
                    ->pluck('ip_rating'),
            ];
        });
    }
}