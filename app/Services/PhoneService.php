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

class PhoneService
{
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

            // foreach ($categorySpecs as $k => $v) {
            //     if (!in_array($k, ['expandable', 'max_visible'])) {
            //         $mergedSpecs[$k] = $v;
            //     }
            // }
        }
        return true;

        // return $mergedSpecs;
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
        array $deleteImageIds = []
    ): array {
        $brandSlug = Str::slug($phone->brand->name);
        $phoneSlug = Str::slug($phone->name);
        $basePath = "{$brandSlug}/{$phoneSlug}";

        $results = [
            'primary_image' => null,
            'colors' => [],
            'uploaded_count' => 0,
            'deleted_count' => 0,
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
                $results['uploaded_count']++;
            }

            // 2. Delete requested images
            if (!empty($deleteImageIds)) {
                $results['deleted_count'] = $this->deleteImages($deleteImageIds);
            }

            // 3. Bulk upload color images
            $results['colors'] = $this->bulkUploadColorImages(
                $phone,
                $colorNames,
                $colorHex,
                $colorImages,
                $basePath
            );

            $results['uploaded_count'] += array_sum(array_column($results['colors'], 'images_count'));

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
        string $basePath
    ): array {
        $results = [];
        $allFilesToUpload = [];
        // First, prepare all uploads and create color records
        foreach ($colorNames as $slug => $colorName) {
            $colorHexCode = $colorHex[$slug] ?? null;
            if (empty($colorName) || empty($colorHexCode)) {
                continue;
            }
            // Find existing color by phone_id and previous slug (or ID if you have it)
            $existingColor = PhoneColor::where('phone_id', $phone->id)
                ->where('slug', $slug) // old slug from DB or fallback
                ->first();
            if ($existingColor) {
                // Update name, hex_code and slug
                $existingColor->update([
                    'name' => $colorName,
                    'hex_code' => $colorHexCode,
                    'slug' => $slug
                ]);
                $phoneColor = $existingColor;
            } else {
                // Create new if not found
                $phoneColor = PhoneColor::create([
                    'phone_id' => $phone->id,
                    'name' => $colorName,
                    'hex_code' => $colorHexCode,
                    'slug' => $slug
                ]);
            }

            $colorResult = [
                'color_name' => $colorName,
                'color_id' => $phoneColor->id,
                'images' => [],
                'images_count' => 0,
            ];

            // Prepare images for this color
            $images = $colorImages[$slug] ?? [];
            foreach ($images as $file) {
                if ($file && $file instanceof UploadedFile && $file->isValid()) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = "{$basePath}/colors/{$slug}/{$filename}";

                    $allFilesToUpload[] = [
                        'file' => $file,
                        'path' => $path,
                        'color_id' => $phoneColor->id,
                        'color_index' => $colorName,
                    ];
                }
            }

            $results[] = $colorResult;
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

                // Update results
                foreach ($results as &$result) {
                    if ($result['color_id'] === $uploadedFile['color_id']) {
                        $result['images'][] = $uploadedFile['path'];
                        $result['images_count']++;
                    }
                }
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
}
