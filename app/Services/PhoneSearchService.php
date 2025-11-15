<?php
namespace App\Services;

use App\Models\Phone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PhoneSearchService
{
    /**
     * Search phones by text query
     */
    public function searchPhones(string $query, int $limit = 10): array
    {
        $cacheKey = "search_phones_" . md5($query . $limit);
        
        return Cache::remember($cacheKey, 300, function () use ($query, $limit) {
            return Phone::whereRaw('MATCH(searchable_text) AGAINST(? IN BOOLEAN MODE)', ["+$query*"])
                ->orWhere('name', 'LIKE', "%{$query}%")
                ->orWhere('brand', 'LIKE', "%{$query}%")
                ->with(['colors:id,phone_id,name,hex_code'])
                ->where('status', 'active')
                ->orderBy('popularity_score', 'desc')
                ->limit($limit)
                ->get(['id', 'name', 'brand', 'model', 'primary_image', 'slug'])
                ->toArray();
        });
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function getSearchSuggestions(string $query): array
    {
        $cacheKey = "search_suggestions_" . md5($query);
        
        return Cache::remember($cacheKey, 600, function () use ($query) {
            $brands = Phone::where('brand', 'LIKE', "$query%")
                ->distinct()
                ->limit(5)
                ->pluck('brand')
                ->toArray();
                
            $phones = Phone::where('name', 'LIKE', "%$query%")
                ->limit(8)
                ->pluck('name')
                ->toArray();
                
            return [
                'brands' => $brands,
                'phones' => $phones
            ];
        });
    }

    /**
     * Advanced search with multiple filters
     */
    public function advancedSearch(array $filters): array
    {
        $phoneService = new PhoneService();
        return $phoneService->getFilteredPhones($filters);
    }
}


// app/Console/Commands/UpdatePhoneSearchIndex.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phone;
use App\Models\PhoneSearchIndex;
use Illuminate\Support\Facades\DB;

class UpdatePhoneSearchIndex extends Command
{
    protected $signature = 'phones:update-search-index {--phone_id=}';
    protected $description = 'Update phone search index for fast filtering';

    public function handle()
    {
        $phoneId = $this->option('phone_id');
        
        if ($phoneId) {
            $this->updateSinglePhone($phoneId);
        } else {
            $this->updateAllPhones();
        }
        
        $this->info('Phone search index updated successfully!');
    }

    private function updateSinglePhone($phoneId)
    {
        $phone = Phone::findOrFail($phoneId);
        $this->updatePhoneSearchIndex($phone);
    }

    private function updateAllPhones()
    {
        Phone::chunk(100, function ($phones) {
            foreach ($phones as $phone) {
                $this->updatePhoneSearchIndex($phone);
            }
        });
    }

    private function updatePhoneSearchIndex($phone)
    {
        // Get aggregated data for search index
        $variants = $phone->variants()->with(['color', 'storage'])->get();
        $specs = $phone->specifications()->get()->groupBy('category');
        
        $searchData = [
            'phone_id' => $phone->id,
            'name' => $phone->name,
            'brand' => $phone->brand,
            'min_price' => $variants->min('final_price') ?? 0,
            'max_price' => $variants->max('final_price') ?? 0,
            'color_count' => $phone->colors()->count(),
            'storage_option_count' => $phone->storageOptions()->count(),
        ];

        // Extract RAM from memory specs
        $memorySpecs = $specs->get('memory', collect())->first();
        if ($memorySpecs) {
            $memoryData = json_decode($memorySpecs->spec_data, true);
            $searchData['ram_gb'] = $memoryData['ram_gb'] ?? null;
        }

        // Extract storage options
        $storageOptions = $phone->storageOptions()->pluck('size_gb');
        $searchData['min_storage_gb'] = $storageOptions->min();
        $searchData['max_storage_gb'] = $storageOptions->max();

        // Extract display specs
        $displaySpecs = $specs->get('display', collect())->first();
        if ($displaySpecs) {
            $displayData = json_decode($displaySpecs->spec_data, true);
            $searchData['screen_size'] = $displayData['size_inches'] ?? null;
        }

        // Extract battery specs
        $batterySpecs = $specs->get('battery', collect())->first();
        if ($batterySpecs) {
            $batteryData = json_decode($batterySpecs->spec_data, true);
            $searchData['battery_capacity'] = $batteryData['capacity_mah'] ?? null;
            $searchData['has_wireless_charging'] = isset($batteryData['wireless_charging_watts']) && $batteryData['wireless_charging_watts'] > 0;
        }

        // Extract camera specs
        $mainCamera = $phone->cameras()->where('camera_type', 'main')->first();
        if ($mainCamera) {
            $searchData['main_camera_mp'] = $mainCamera->megapixels;
        }

        // Extract connectivity specs
        $connectivitySpecs = $specs->get('connectivity', collect())->first();
        if ($connectivitySpecs) {
            $connectivityData = json_decode($connectivitySpecs->spec_data, true);
            $searchData['has_nfc'] = $connectivityData['nfc'] ?? false;
        }

        // Extract network specs
        $networkSpecs = $specs->get('network', collect())->first();
        if ($networkSpecs) {
            $networkData = json_decode($networkSpecs->spec_data, true);
            $searchData['has_5g'] = strpos(strtolower($networkData['technology'] ?? ''), '5g') !== false;
        }

        // Build searchable text
        $searchableText = collect([
            $phone->name,
            $phone->brand,
            $phone->model,
            $phone->tagline,
        ])->filter()->implode(' ');

        $searchData['searchable_text'] = $searchableText;

        // Update or create search index
        PhoneSearchIndex::updateOrCreate(
            ['phone_id' => $phone->id],
            $searchData
        );
    }
}