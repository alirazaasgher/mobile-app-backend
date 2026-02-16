<?php

namespace App\Services;

use App\Models\Phone;
use App\Models\PhoneSearchIndex;
use App\Models\PhoneSpecification;
use App\Models\Variant;
use App\Models\PhoneImage;
use App\Models\PhoneColor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class PhoneService
{

    protected $compareScoreService;
    public function __construct(CompareScoreService $compareScoreService)
    {
        $this->compareScoreService = $compareScoreService;
    }
    /**
     * Get filtered phones with pagination
     */
    public function getFilteredPhones(array $filters): array
    {
        $cacheKey = 'phones_filtered_' . md5(serialize($filters));

        return Cache::remember($cacheKey, 300, function () use ($filters) {
            $query = Phone::query()->with(['colors:id,phone_id,name,hex_code', 'storageOptions:id,phone_id,size,price']);

            // Apply filters using search index for performance
            $query->whereHas('searchIndex', function ($q) use ($filters) {
                $this->applySearchIndexFilters($q, $filters);
            });

            // Apply direct filters
            $this->applyDirectFilters($query, $filters);

            // Apply sorting
            $this->applySorting($query, $filters);

            // Paginate
            $perPage = $filters['per_page'] ?? 20;
            $results = $query->paginate($perPage);

            return [
                'data' => $results->items(),
                'meta' => [
                    'current_page' => $results->currentPage(),
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                    'last_page' => $results->lastPage(),
                    'from' => $results->firstItem(),
                    'to' => $results->lastItem(),
                ]
            ];
        });
    }

    /**
     * Get single phone with complete details
     */
    public function getPhoneDetails(int $id): array
    {
        $cacheKey = "phone_details_{$id}";

        return Cache::remember($cacheKey, 600, function () use ($id) {
            $phone = Phone::with([
                'colors.images',
                'storageOptions',
                'variants.color',
                'variants.storage'
            ])->findOrFail($id);

            // Get specifications by category
            $specifications = $this->getPhoneSpecifications($id);

            return [
                'id' => $phone->id,
                'name' => $phone->name,
                'brand' => $phone->brand,
                'model' => $phone->model,
                'tagline' => $phone->tagline,
                'slug' => $phone->slug,
                'primary_image' => $phone->primary_image,
                'status' => $phone->status,
                'announced_date' => $phone->announced_date,
                'release_date' => $phone->release_date,
                'popularity_score' => $phone->popularity_score,
                'colors' => $phone->colors->map(function ($color) {
                    return [
                        'id' => $color->id,
                        'name' => $color->name,
                        'slug' => $color->slug,
                        'hex' => $color->hex_code,
                        'price' => $color->price,
                        'images' => $color->images->pluck('image_url')->toArray()
                    ];
                }),
                'storage_options' => $phone->storageOptions->map(function ($storage) {
                    return [
                        'id' => $storage->id,
                        'size' => $storage->size,
                        'size_gb' => $storage->size_gb,
                        'price' => $storage->price
                    ];
                }),
                'variants' => $phone->variants->where('is_available', true)->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'color_name' => $variant->color->name,
                        'color_hex' => $variant->color->hex_code,
                        'storage_size' => $variant->storage->size,
                        'final_price' => $variant->final_price,
                        'stock_quantity' => $variant->stock_quantity
                    ];
                }),
                'specifications' => $specifications
            ];
        });
    }

    /**
     * Get phone specifications by category
     */
    public function getPhoneSpecifications(int $phoneId): array
    {
        $cacheKey = "phone_specs_{$phoneId}";

        return Cache::remember($cacheKey, 900, function () use ($phoneId) {
            $specs = DB::table('phone_specifications')
                ->where('phone_id', $phoneId)
                ->get()
                ->groupBy('category');

            $formattedSpecs = [];
            foreach ($specs as $category => $categorySpecs) {
                $formattedSpecs[$category] = [];
                foreach ($categorySpecs as $spec) {
                    $specData = json_decode($spec->spec_data, true);
                    $formattedSpecs[$category] = array_merge($formattedSpecs[$category], $specData);
                }
            }

            return $formattedSpecs;
        });
    }

    /**
     * Get phone variants
     */
    public function getPhoneVariants(int $phoneId): array
    {
        return Phone::findOrFail($phoneId)
            ->variants()
            ->with(['color.images', 'storage'])
            ->where('is_available', true)
            ->orderBy('final_price')
            ->get()
            ->toArray();
    }

    /**
     * Get popular phones
     */
    public function getPopularPhones(int $limit = 20): array
    {
        return Cache::remember("popular_phones_{$limit}", 1800, function () use ($limit) {
            return Phone::with(['colors:id,phone_id,name,hex_code', 'storageOptions:id,phone_id,size,price'])
                ->orderBy('popularity_score', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get featured phones
     */
    public function getFeaturedPhones(int $limit = 10): array
    {
        return Cache::remember("featured_phones_{$limit}", 3600, function () use ($limit) {
            return Phone::with(['colors:id,phone_id,name,hex_code'])
                ->where('is_featured', true)
                ->orderBy('featured_order')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get latest phones
     */
    public function getLatestPhones(int $limit = 15): array
    {
        return Cache::remember("latest_phones_{$limit}", 1800, function () use ($limit) {
            return Phone::with(['colors:id,phone_id,name,hex_code'])
                ->whereNotNull('release_date')
                ->orderBy('release_date', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Compare multiple phones
     */
    public function comparePhones(array $phoneIds): array
    {
        $phones = [];
        foreach ($phoneIds as $id) {
            $phones[] = $this->getPhoneDetails($id);
        }

        return [
            'phones' => $phones,
            'comparison_matrix' => $this->buildComparisonMatrix($phones)
        ];
    }

    /**
     * Apply search index filters for performance
     */
    private function applySearchIndexFilters($query, array $filters): void
    {
        if (!empty($filters['min_price'])) {
            $query->where('min_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('max_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['ram'])) {
            $query->whereIn('ram_gb', (array) $filters['ram']);
        }

        if (!empty($filters['storage'])) {
            $query->where(function ($q) use ($filters) {
                foreach ((array) $filters['storage'] as $storage) {
                    $q->orWhere('min_storage_gb', '<=', $storage)
                        ->where('max_storage_gb', '>=', $storage);
                }
            });
        }

        if (!empty($filters['screen_size'])) {
            $query->whereIn('screen_size', (array) $filters['screen_size']);
        }

        if (!empty($filters['battery_min'])) {
            $query->where('battery_capacity', '>=', $filters['battery_min']);
        }

        if (!empty($filters['has_5g'])) {
            $query->where('has_5g', true);
        }

        if (!empty($filters['has_nfc'])) {
            $query->where('has_nfc', true);
        }

        if (!empty($filters['has_wireless_charging'])) {
            $query->where('has_wireless_charging', true);
        }

        if (!empty($filters['camera_mp_min'])) {
            $query->where('main_camera_mp', '>=', $filters['camera_mp_min']);
        }
    }

    /**
     * Apply direct filters on phones table
     */
    private function applyDirectFilters($query, array $filters): void
    {
        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('brand', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('model', 'LIKE', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', 'active'); // Default to active phones
        }

        if (!empty($filters['year'])) {
            $query->whereYear('release_date', $filters['year']);
        }
    }

    /**
     * Apply sorting
     */
    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'popularity';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        switch ($sortBy) {
            case 'price':
                $query->join('phone_search_index', 'phones.id', '=', 'phone_search_index.phone_id')
                    ->orderBy('phone_search_index.min_price', $sortDirection);
                break;
            case 'popularity':
                $query->orderBy('popularity_score', $sortDirection);
                break;
            case 'rating':
                $query->join('phone_search_index', 'phones.id', '=', 'phone_search_index.phone_id')
                    ->orderBy('phone_search_index.avg_rating', $sortDirection);
                break;
            case 'release_date':
                $query->orderBy('release_date', $sortDirection);
                break;
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'brand':
                $query->orderBy('brand', $sortDirection)->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('popularity_score', 'desc');
        }
    }

    /**
     * Build comparison matrix for phone comparison
     */
    private function buildComparisonMatrix(array $phones): array
    {
        // Implementation for comparison matrix
        // This would highlight differences and similarities between phones
        return [];
    }
    /**
     * Handle primary image upload. Returns stored path or null.
     */

    public function handlePrimaryImage(?UploadedFile $file): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = 'primary_images/' . date('Y/m') . '/' . $filename;

        Storage::disk(config('filesystems.default'))
            ->put($path, file_get_contents($file), 'public');

        return $path;
    }

    /**
     * Variant diffing: update existing variants, delete removed, create new.
     *
     * $incomingSpecs: array of strings like ["4/64", "6/128"]
     * $priceModifiers: associative array price_modifier["4/64"] => price
     *
     * Returns arrays: [ram_list, storage_list, price_list]
     */
    public function syncVariants($phone, array $incomingSpecs, array $pricePKR, array $priceUSD): array
    {

        $ram_list = $storage_list = $price_list = [];

        // Normalize incoming as map: key => ['ram'=>..., 'storage'=>..., 'price'=>...]
        $incomingMap = [];
        foreach ($incomingSpecs as $spec) {
            $parts = array_map('trim', explode('/', $spec));
            $ram = $parts[0] ?? '';
            $storage = $parts[1] ?? '';

            $pricePKRArray = $pricePKR[$spec] ?? null;
            $priceUSDArray = $priceUSD[$spec] ?? null;
            $ramTypeArray = $ramType[$spec] ?? null;
            $storageTypeArray = $storageType[$spec] ?? null;
            $key = strtolower($ram . '/' . $storage);
            $incomingMap[$key] = [
                'ram' => $ram,
                'storage' => $storage,
                'pkr_price' => $pricePKRArray,
                'usd_price' => $priceUSDArray,
                'ram_type_id ' => $ramTypeArray,
                'storage_type_id' => $storageTypeArray,
                'raw' => $spec
            ];
            $ram_list[] = $ram;
            $storage_list[] = $storage;
            $price_list[] = [
                'pkr' => $pricePKRArray ?? null,
                'usd' => $priceUSDArray ?? null
            ];
        }


        // Existing variants keyed by ram/storage
        $existing = $phone->variants()->get();

        $existingMap = [];
        foreach ($existing as $ev) {
            $key = strtolower(trim($ev->ram) . '/' . trim($ev->storage));
            $existingMap[$key] = $ev;
        }
        // Update or delete existing
        foreach ($existingMap as $key => $ev) {
            if (isset($incomingMap[$key])) {
                $incoming = $incomingMap[$key];
                // if price changed or ram/storage changed (unlikely), update

                $needsUpdate =
                    (trim($ev->ram) !== $incoming['ram']) ||
                    (trim($ev->storage) !== $incoming['storage']) ||
                    ($ev->pkr_price != $incoming['pkr_price']) ||
                    ($ev->usd_price != $incoming['usd_price']);
                // ($ev->ram_type_id != ($incoming['ram_type_id'] ?? null)) ||
                // ($ev->storage_type_id != ($incoming['storage_type_id'] ?? null));
                if ($needsUpdate) {
                    $ev->update([
                        'ram' => $incoming['ram'],
                        'storage' => $incoming['storage'],
                        // 'ram_type_id' => $incoming['ram_type_id'] ?? null,
                        // 'storage_type_id' => $incoming['storage_type_id'] ?? null,
                        'pkr_price' => $incoming['pkr_price'],
                        'usd_price' => $incoming['usd_price'],
                    ]);
                }
                // remove from incomingMap so at the end only new items remain
                unset($incomingMap[$key]);
            } else {
                // was removed by user -> delete
                $ev->delete();
            }
        }


        // Remaining incomingMap items are new -> insert
        foreach ($incomingMap as $key => $data) {
            Variant::create([
                'phone_id' => $phone->id,
                'ram' => $data['ram'],
                'storage' => $data['storage'],
                // 'ram_type_id' => $incoming['ram_type_id'] ?? null,
                // 'storage_type_id' => $incoming['storage_type_id'] ?? null,
                'pkr_price' => $data['pkr_price'],
                'usd_price' => $data['usd_price'],
            ]);
        }

        // Ensure lists are unique and normalized for memory building
        return [$ram_list, $storage_list, $price_list];
    }

    /**
     * Process colors, delete any requested images, add new images.
     *
     * $deleteImageIds optional array of image ids to remove
     */
    public function syncColorsAndImages(
        $phone,
        array $uploadResults
    ): array {
        echo "<pre>";
        print_r($uploadResults);
        exit;
        $available_colors = [];

        // Process uploaded colors and their images
        foreach ($uploadResults['colors'] ?? [] as $colorData) {
            $available_colors[] = [
                'colorName' => $colorData['color_name'],
                'colorHex' => $colorData['color_hex'] ?? null,
                'color_id' => $colorData['color_id'] ?? null,
            ];
        }

        return $available_colors;
    }

    /**
     * Build memory spec array.
     */
    public function buildMemorySpec($ram_list, $storage_list, $storage_type, $ram_type, $sd_card)
    {
        $short = [];

        foreach ($ram_list as $i => $ram) {
            $short[] = $ram . 'GB/' . (is_numeric($storage_list[$i]) ? $storage_list[$i] . 'GB' : $storage_list[$i]);
        }

        return [
            'ram_type' => $ram_type,
            'storage_type' => $storage_type,
            'memory' => implode(', ', $short),
            'card_slot' => $sd_card,
            'expandable' => 0,
            'max_visible' => 4
        ];
    }

    /**
     * Save or update phone specifications.
     * $specs is expected to be associative: category => [k => v, ...]
     *
     * Returns mergedSpecs array used for search indexing.
     */
    public function saveSpecifications($phone, array $specs, callable $searchableTextGetter, $update = false): bool
    {
        // $scores = $this->scoreByCategory($specs, 'balanced');
        foreach ($specs as $category => $categorySpecs) {
            // skip if all values empty
            if (!array_filter($categorySpecs)) {
                // delete existing row if any
                PhoneSpecification::where('phone_id', $phone->id)
                    ->where('category', $category)
                    ->delete();
                continue;
            }

            // Filter helpers (you may already have app helpers)
            $filteredSpecs = $this->filterSpecs($categorySpecs);
            $expandable = $filteredSpecs['expandable'] ?? 0;
            $max_visible = $filteredSpecs['max_visible'] ?? null;
            unset($filteredSpecs['expandable']);
            unset($filteredSpecs['max_visible']);

            // remove UI-specific keys if present

            PhoneSpecification::updateOrCreate(
                ['phone_id' => $phone->id, 'category' => $category],
                [
                    'specifications' => json_encode($filteredSpecs),
                    'searchable_text' => $searchableTextGetter($category),
                    'expandable' => $expandable,
                    'max_visible' => $max_visible
                ]
            );
        }
        return true;
    }

    /**
     * Simple filter to drop empty values (feel free to replace with your app's filterSpecs).
     */
    protected function filterSpecs(array $specs): array
    {
        $out = [];
        foreach ($specs as $k => $v) {
            if (is_array($v)) {
                $filtered = array_filter($v);
                if ($filtered)
                    $out[$k] = $filtered;
            } else {
                if ($v !== null && $v !== '')
                    $out[$k] = $v;
            }
        }
        return $out;
    }

    protected function normalizeStorage($value)
    {
        $value = strtolower(trim($value));

        if (str_contains($value, 'tb')) {
            return ['value' => (float) $value * 1024, 'unit' => 'TB'];
        }

        if (str_contains($value, 'gb')) {
            return ['value' => (float) $value, 'unit' => 'GB'];
        }

        return ['value' => (float) $value, 'unit' => 'GB']; // fallback
    }

    public function handleBulkImageUpload(
        $phone,
        ?UploadedFile $primaryImage,
        array $colorNames,
        array $colorHex,
        array $colorImages = [],
        array $colorIds = [],
        array $deleteImageIds = []
    ): array {
        $brandSlug = Str::slug($phone->brand->name);
        $phoneSlug = Str::slug($phone->name);
        $basePath = "{$brandSlug}/{$phoneSlug}";
        $results = [
            'primary_image' => null
        ];

        DB::beginTransaction();
        try {
            // 1. Handle primary image upload
            if ($primaryImage) {
                $results['primary_image'] = $this->uploadSingleImage(
                    $primaryImage,
                    "{$basePath}/primary_images",
                    $phone->primary_image
                );
            }

            // 2. Delete requested images
            if (!empty($deleteImageIds)) {
                $this->deleteImages($deleteImageIds);
            }

            // 3. Bulk upload color images
            $results['colors'] = $this->bulkUploadColorImages(
                $phone,
                $colorNames,
                $colorHex,
                $colorImages,
                $colorIds,
                $basePath
            );

            DB::commit();
            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            // Clean up uploaded files on failure
            $this->cleanupOnFailure($results);
            throw $e;
        }
    }

    /**
     * Upload single image
     */
    protected function uploadSingleImage(UploadedFile $file, string $directory, string|null $oldPath = null): string
    {

        // Validate upload
        if (!$file->isValid()) {
            throw new \Exception("Invalid file upload");
        }

        // Delete old image if exists
        if ($oldPath && Storage::disk(config('filesystems.default'))->exists($oldPath)) {
            Storage::disk(config('filesystems.default'))->delete($oldPath);
            // optional log
            // \Log::info("Deleted old image: {$oldPath}");
        }

        // Generate new unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "{$directory}/{$filename}";

        // Save new image
        Storage::disk(config('filesystems.default'))->put(
            $path,
            file_get_contents($file->getRealPath()),
            'public'
        );

        return $path;
    }

    /**
     * Bulk upload color images
     */
    protected function bulkUploadColorImages(
        $phone,
        array $colorNames,
        array $colorHex,
        array $colorImages,
        array $colorIds,
        string $basePath
    ): array {
        $results = [];
        $allFilesToUpload = [];
        foreach ($colorNames as $key => $colorName) {

            if (!$colorName)
                continue;

            // Generate slug
            $slug = strtolower(trim($colorName));
            $slug = preg_replace('/\s+/', '_', $slug);
            $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
            $slug = trim($slug, '_');

            $colorId = $colorIds[$key] ?? null;

            if ($colorId) {
                // UPDATE
                $phoneColor = PhoneColor::where('id', $colorId)
                    ->where('phone_id', $phone->id)
                    ->first();

                if ($phoneColor) {
                    $phoneColor->update([
                        'name' => $colorName,
                        'hex_code' => $colorHex[$key] ?? null,
                        'slug' => $slug,
                    ]);
                }
            } else {
                // CREATE
                $phoneColor = PhoneColor::create([
                    'phone_id' => $phone->id,
                    'name' => $colorName,
                    'hex_code' => $colorHex[$key] ?? null,
                    'slug' => $slug,
                ]);
            }

            // 🟢 IMPORTANT: use $key, NOT $slug
            $images = $colorImages[$key] ?? [];
            foreach ($images as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {

                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = "{$basePath}/colors/{$slug}/{$filename}";

                    $allFilesToUpload[] = [
                        'file' => $file,
                        'path' => $path,
                        'color_id' => $phoneColor->id,
                        'color_name' => $colorName,
                    ];
                }
            }
        }

        // Bulk upload all files
        if (!empty($allFilesToUpload)) {
            $this->bulkUploadFiles($allFilesToUpload);
            // Create database records for uploaded images
            foreach ($allFilesToUpload as $uploadedFile) {
                PhoneImage::create([
                    'phone_color_id' => $uploadedFile['color_id'],
                    'image_url' => $uploadedFile['path'],
                ]);
            }
        }

        return $results;
    }

    /**
     * Bulk upload files to storage (more efficient)
     */
    protected function bulkUploadFiles(array $filesToUpload): void
    {
        $storage = Storage::disk(config('filesystems.default'));

        // For local storage or Cloudflare R2, we can use putFileAs or put
        foreach ($filesToUpload as $item) {
            $storage->put(
                $item['path'],
                file_get_contents($item['file']->getRealPath()),
                'public'
            );
        }

        // Alternative: Use Laravel's built-in batch upload if your driver supports it
        // This is more efficient for some storage drivers
        /*
        $promises = [];
        foreach ($filesToUpload as $item) {
            $promises[] = $storage->putAsync(
                $item['path'],
                file_get_contents($item['file']->getRealPath()),
                'public'
            );
        }
        // Wait for all uploads to complete
        Promise\all($promises)->wait();
        */
    }

    /**
     * Delete images in bulk
     */
    protected function deleteImages(array $imageIds): int
    {
        $images = PhoneImage::whereIn('id', $imageIds)->get();
        $paths = $images->pluck('image_url')->toArray();

        // Bulk delete from storage
        if (!empty($paths)) {
            Storage::disk(config('filesystems.default'))->delete($paths);
        }

        // Delete from database
        return PhoneImage::whereIn('id', $imageIds)->delete();
    }

    /**
     * Clean up uploaded files on failure
     */
    protected function cleanupOnFailure(array $results): void
    {
        $pathsToDelete = [];

        if (!empty($results['primary_image'])) {
            $pathsToDelete[] = $results['primary_image'];
        }

        foreach ($results['colors'] ?? [] as $color) {
            $pathsToDelete = array_merge($pathsToDelete, $color['images'] ?? []);
        }

        if (!empty($pathsToDelete)) {
            Storage::disk(config('filesystems.default'))->delete($pathsToDelete);
        }
    }

    /**
     * Get available colors (for response)
     */
    public function getAvailableColors($phone): array
    {
        return PhoneColor::where('phone_id', $phone->id)
            ->get()
            ->map(fn($color) => [
                'colorName' => $color->name,
                'colorHex' => $color->hex_code,
            ])
            ->toArray();
    }

    public function scoreByCategory(array $specs, $brand, $profile): array
    {
        // $res = $this->scorePerformance($specs, $profile);
        // echo "<pre>";
        // print_r($res);
        // exit;
        return [
            'display' => $this->scoreDisplay($specs, $brand->name, $profile),
            'performance' => $this->scorePerformance($specs, $profile),
            'camera' => $this->scoreCamera($specs, $profile),
            'battery' => $this->scoreBattery($specs, $profile),
            'build' => $this->scoreBuild($specs, $profile),
            'connectivity' => $this->scoreConnectivity($specs, $profile),
        ];
    }

    protected function scoreDisplay(array $s, string $brand, string $profile)
    {
        // 1. Validate input structure
        if (!isset($s['display'])) {
            return $this->compareScoreService->scoreCategory('display', [], $profile);
        }

        $display = $s['display'];

        // 2. Extract with proper null handling


        // 3. Brightness extraction with validation
        $brightnessData = extractAllBrightness($display['brightness'] ?? '');
        $brightnessTypical = $brightnessData['typical'] ?? null;
        $brightnessHBM = $brightnessData['hbm'] ?? null;
        $brightnessPeak = $brightnessData['peak'] ?? null;

        // 4. Contrast ratio with safer extraction
        $contrast_ratio = null;
        if (isset($display['contrast_ratio'])) {
            $parts = explode(':', (string) $display['contrast_ratio']);
            if (!empty($parts[0])) {
                $contrast_ratio = (int) str_replace([',', ' ', ':', ';'], '', $parts[0]);
            }
        }

        // 5. Display type with fallback
        $display_type = getShortDisplay($display['type'] ?? null) ?? '';
        $isLcd = stripos($display_type, 'lcd') !== false || stripos($display_type, 'ips') !== false;
        $chipset = getShortChipset($s['performance']['chipset']) ?? null;
        // 6. PWM logic with better defaults
        $pwmRaw = extractNumber($display['pwm_frequency'] ?? null);
        $pwmScoreMaster = $pwmRaw;
        if ($pwmRaw === null && $chipset) {
            // Only estimate for confirmed high-end panels
            if (preg_match('/Snapdragon 8 Gen [3-9]|Dimensity 9[3-9]00/i', $chipset)) {
                $pwmScoreMaster = 1920; // High-end chips likely have good panels
            } elseif ($isLcd) {
                $pwmScoreMaster = 0; // LCDs are DC-dimmed
            }
        }

        // 7. Screen ratio with validation
        $screen_ratio = null;
        if (isset($display['screen_to_body_ratio'])) {
            $ratio = $display['screen_to_body_ratio'];
            $clean = preg_replace('/[^\d.]/', '', $ratio);
            $screen_ratio = is_numeric($clean) ? (float) $clean : null;
        }

        $colour_depth = $s['display']['color_depth'] ?? null; // "1.07 billion colors (10-bit)"
        $colorGamut = $s['color_gamut'] ?? 'sRGB';
        $color_depth_master = null;

        if (!empty($colour_depth)) {
            $color_depth_master = $colour_depth;
        } elseif ($chipset && preg_match('/Snapdragon 8 Gen [3-9]|A1[89] Pro/i', $chipset)) {
            // Only 2024+ flagships reliably have 10-bit
            $color_depth_master = "10-bit";
        }
        $wide_gamuts = ['P3', 'Adobe RGB', 'Rec.2020', 'DCI-P3', 'Display P3'];
        $has_wide_gamut = false;
        foreach ($wide_gamuts as $gamut) {
            if (str_contains($colorGamut, $gamut)) {
                $has_wide_gamut = true;
                break;
            }
        }

        if ($color_depth_master == "10-bit" && !$has_wide_gamut) {
            $color_depth_master = "8-bit";
        }

        // 1. Clean the input
        $instantTouchRaw = $s['display']['instant_touch_sampling_rate'] ?? null;
        $instant_touch_sampling_rate = preg_replace('/\D+/', '', $instantTouchRaw) ?: null;

        // 9. Effective brightness calculation
        $effectiveBrightness = $brightnessHBM ?? $brightnessPeak ?? $brightnessTypical;

        // 10. Prepare data array with all extracted values
        $displayData = [
            'size' => extractSize($display['size'] ?? null),
            'type' => $display_type,
            'resolution' => shortResolution($display['resolution'] ?? null),
            'aspect_ratio' => $display['aspect_ratio'] ?? null,
            'screen_ratio' => $screen_ratio,
            'pixel_density' => extractPpi($display['resolution'] ?? null),
            'refresh_rate' => extractNumber($display['refresh_rate'] ?? null),
            'adaptive_refresh_rate' => preg_replace('/\D+/', '', $display['adaptive_refresh_rate_range'] ?? '') ?: null,
            'touch_sampling_rate' => preg_replace('/\D+/', '', $display['touch_sampling_rate'] ?? '') ?: null,
            'instant_touch_sampling_rate' => $instant_touch_sampling_rate,
            'brightness' => $effectiveBrightness,
            'brightness_typical' => $brightnessTypical,
            'brightness_hbm' => $brightnessHBM,
            'brightness_peak' => $brightnessPeak,
            'contrast_ratio' => $contrast_ratio,
            'contrast_score_master' => $contrast_ratio_master ?? null,
            'hdr_support' => getHdrSupport($display['features'] ?? ''),
            'pwm' => $pwmRaw,
            'pwm_score_master' => $pwmScoreMaster ?? NULL,
            // 'has_branded_glass' => $screenGlassType['has_branded_glass'] ?? false,
            'colour_depth' => $colour_depth ?? null,
            'colour_depth_master' => $color_depth_master,
            'always_on_display' => isset($display['always_on_display'])
                ? strtoupper($display['always_on_display']) === 'YES'
                : false,
        ];

        return $this->compareScoreService->scoreCategory('display', $displayData, $profile);
    }

    protected function scorePerformance(array $s, string $profile)
    {

        $antutu_score = $benchmark['antutu'] ?? null;
        $chipset = getShortChipset($s['performance']['chipset']) ?? null;
        $cpu = getSimplifiedCpuSpeed($s['performance']['cpu'] ?? '');
        $cooling_type = $s['performance']['cooling'] ?? null;
        $cooling_type_master = $cooling_type;
        // Only set if missing
        $throttling_rate = $s['performance']['throttling'] ?? null;
        if (!$throttling_rate && $chipset) {
            $throttling_rate = estimateThrottling($chipset);
        }

        $ai_capability = $s['performance']['ai_capability'] ?? null;

        if (!$ai_capability && $chipset) {
            $ai_capability = estimateAICapability($chipset);
        }
        $memoryParsed = parseMemory($s['memory']['memory'] ?? '');
        return $this->compareScoreService->scoreCategory('performance', [
            'chipset' => $chipset,
            'ram' => $memoryParsed['ram'],
            'storage_capacity' => $memoryParsed['storage'],
            'cpu' => cpuType($s['performance']['cpu']) ?? null,
            'cpu_speed' => $cpu,
            'gpu' => $s['performance']['gpu'] ?? null,
            'storage_type' => $s['memory']['storage_type'] ?? null,
            'ram_type' => $s['memory']['ram_type'] ?? null,
            'antutu_score' => $antutu_score,
            'card_slot' => $s['memory']['card_slot'] ?? 'NO',
            'cooling_type' => $cooling_type ?? null,
            'cooling_type_master' => $cooling_type_master ?? null,
            'ai_capability' => $ai_capability,
            'throttling_rate' => $throttling_rate ?? null,
        ], $profile);
    }

    protected function scoreCamera(array $s, string $profile)
    {

        $cameraApertures = extractCameraApertures($s['main_camera']);
        $cameraOpticalZoom = extractOpticalZoom($s['main_camera']);
        $cameratabilization = extractStabilization($s['main_camera']);
        $cameraSetup = parseCameraSetup($s['main_camera']['setup']);
        $cameraFlash = getFlash($s['main_camera']);
        $cameraVideo = extractVideo($s['main_camera']['video'] ?? '');
        $setup = $s['selfie_camera']['setup'] ?? ''; // e.g., "Single (50 MP)"
        $sensorSizeData = extractSensorSize($s['main_camera']['main_sensor'] ?? '');
        $frontAperture = extractFrontAperture($s['selfie_camera']['sensor'] ?? '');
        // Extract the first number
        preg_match('/\d+/', $setup, $matches);
        $frontCameraSetup = $matches[0] ?? null;
        $object = [];
        foreach ($cameraSetup as $value) {
            // Dynamically use 'type' as key and 'mp' as value
            $key = $value['type']; // e.g., 'rear', 'front', 'wide'
            $object[$key] = $value['mp'] ?? null; // fallback to null if 'mp' is missing
        }
        return $this->compareScoreService->scoreCategory(
            'camera',
            array_merge(
                $object,
                $cameraApertures, // dynamic camera keys
                [
                    'sensor_size' => $sensorSizeData,
                    'optical_zoom' => $cameraOpticalZoom,
                    'stabilization' => $cameratabilization,
                    'flash' => $cameraFlash,
                    'front' => $frontCameraSetup ?? null,
                    'front_aperture' => $frontAperture,
                    'video_resolution' => $cameraVideo ?? null,
                    'front_video' => extractVideo($s['selfie_camera']['video'] ?? '') ?? null,
                ]
            ),
            $profile
        );
    }

    protected function scoreBattery(array $s, string $profile)
    {

        $wiredChargingSpec = $s['battery']['charging_speed'] ?? '';
        $wirlessCharging = $s['battery']['wireless'] ?? '';
        $reverceCharging = $s['battery']['reverse'] ?? '';
        $chargingTime = extractChargingTime($wiredChargingSpec);
        $chargingTime50 = extractChargingTime50($wiredChargingSpec);
        return $this->compareScoreService->scoreCategory('battery', [
            "type" => parseBatteryType($s['battery']['type']),
            'capacity' => parseBatteryCapacity($s['battery']['capacity']) ?? null,
            'wired' => parseFastChargingToWatts($wiredChargingSpec),
            'wirless' => parseFastChargingToWatts($wirlessCharging ?? 0),
            'reverce' => parseFastChargingToWatts($reverceCharging ?? 0),
            'charging_time_0_to_100' => $chargingTime,
            'charging_time_0_to_50' => $chargingTime50,
        ], $profile);
    }


    protected function scoreBuild(array $s, string $profile)
    {

        $screenGlassType = extractScreenGlassType($s['display']['protection'] ?? null) ?? [];
        $fingerprint_sensor = parseFingerprintType($s['Features']['sensors'] ?? '');
        $buildMaterials = buildMaterials($s['build']['build'] ?? '');
        $mobileDimensions = getMobileDimensions($s['build']['dimensions'] ?? []);
        $formatGlassProtection = formatGlassProtection($screenGlassType);
        return $this->compareScoreService->scoreCategory('build', [
            'glass_protection' => $formatGlassProtection,
            'dimensions' => $mobileDimensions['dimensions'] ?? null,
            'thickness' => $mobileDimensions['thickness'] ?? null,
            'weight' => $s['build']['weight'] !== null
                ? (float) preg_replace('/[^0-9.]/', '', $s['build']['weight'])
                : null,
            'build_material' => $buildMaterials['build_material'] ?? null,
            'back_material' => $buildMaterials['back_material'] ?? null,
            'ip_rating' => shortIPRating($s['build']['ip_rating']) ?? null,
            'fingerprint_sensor' => $fingerprint_sensor
        ], $profile);
    }


    protected function scoreConnectivity(array $s, string $profile)
    {
        $simText = strtolower(strip_tags($s['build']['sim']));
        return $this->compareScoreService->scoreCategory('connectivity', [
            'sim' => $simText,
            'nfc' => $s['connectivity']['nfc'] ?? null,
            'stereo_speakers' => $s['audio']['stereo'] ?? null,
            '3.5mm_jack' => $s['audio']['3.5mm_jack'] ?? null,
            "infrared" => $s['connectivity']['infrared'] ?? null,
            'wifi' => formatWifiValue($s['connectivity']['wifi']),
            'bluetooth_version' => isset($s['connectivity']['bluetooth'])
                ? (preg_match('/v([\d.]+)/i', $s['connectivity']['bluetooth'], $m) ? $m[1] : null)
                : null,
            'usb' => formatUsbLabel($s['connectivity']['usb']),
        ], $profile);
    }
}
