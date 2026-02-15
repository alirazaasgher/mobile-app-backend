<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Phone;
use App\Services\PhoneSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\PhoneResource;
use App\Http\Resources\PhoneListResource;
use App\Services\CompareScoreService;
use App\Services\PhoneService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;

class PhoneApiController extends Controller
{
    protected PhoneSearchService $phoneSearchService;
    protected $phoneService;
    public function __construct(PhoneSearchService $phoneSearchService, PhoneService $phoneService)
    {
        $this->phoneSearchService = $phoneSearchService;
        $this->phoneService = $phoneService;
    }

    /**
     * Get filtered phones with pagination - Main listing API
     */
    public function index(): JsonResponse
    {
        $baseSelect = [
            'id',
            'name',
            'brand_id',
            'slug',
            'release_date',
            'primary_image',
            'updated_at',
            'is_popular',
            'status'
        ];

        // Fetch all collections in parallel queries
        $latestMobiles = Phone::select($baseSelect)
            ->active()
            ->where('is_popular', 0)
            ->whereNotNull('release_date')
            ->orderByDesc('release_date')
            ->limit(12)
            ->get();

        $usedPhoneIds = $latestMobiles->pluck('id')->all();

        $upcomingMobiles = Phone::select($baseSelect)
            ->active()
            ->whereIn('status', ['rumored', 'upcoming'])
            ->whereNotIn('id', $usedPhoneIds)
            ->orderBy('release_date', 'asc')
            ->limit(12)
            ->get();

        $usedPhoneIds = array_merge($usedPhoneIds, $upcomingMobiles->pluck('id')->all());
        $popularMobiles = Phone::select($baseSelect)
            ->active()
            ->where('is_popular', 1)
            ->whereNotIn('id', $usedPhoneIds)
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        $usedPhoneIds = array_merge($usedPhoneIds, $popularMobiles->pluck('id')->all());

        // Fetch price range mobiles with UNION ALL for better performance
        $priceRangeQueries = [
            'under_10000' => [0, 9999],
            '10000_20000' => [10000, 19999],
            '20000_30000' => [20000, 29999],
            '30000_40000' => [30000, 39999],
            '40000_50000' => [40000, 49999],
            '50000_60000' => [50000, 59999],
            'above_60000' => [60000, null],
        ];

        $unionQuery = null;
        foreach ($priceRangeQueries as $key => [$min, $max]) {
            $query = Phone::select(array_merge($baseSelect, [DB::raw("'$key' as price_range")]))
                ->active()
                ->whereNotIn('id', $usedPhoneIds)
                ->whereHas('searchIndex', function ($q) use ($min, $max) {
                    $q->where('min_price_pkr', '>', 0);
                    if (is_null($max)) {
                        $q->where('min_price_pkr', '>=', $min);
                    } else {
                        $q->whereBetween('min_price_pkr', [$min, $max - 1]);
                    }
                })
                ->latest('updated_at')
                ->limit(12);

            if ($unionQuery === null) {
                $unionQuery = $query;
            } else {
                $unionQuery->unionAll($query);
            }
        }

        $priceRangeMobiles = $unionQuery ? collect(DB::select($unionQuery->toSql(), $unionQuery->getBindings())) : collect();

        // Group price range results
        $mobilesByPriceRange = $priceRangeMobiles->groupBy('price_range')->map(function ($items) {
            return collect($items)->map(fn($item) => (object) (array) $item);
        });

        // Collect all phone IDs for bulk loading
        $allPhoneIds = collect()
            ->merge($latestMobiles->pluck('id'))
            ->merge($upcomingMobiles->pluck('id'))
            ->merge($popularMobiles->pluck('id'))
            ->merge($priceRangeMobiles->pluck('id'))
            ->unique()
            ->all();

        // Bulk load brands (single query)
        $brandIds = collect()
            ->merge($latestMobiles->pluck('brand_id'))
            ->merge($upcomingMobiles->pluck('brand_id'))
            ->merge($popularMobiles->pluck('brand_id'))
            ->merge($priceRangeMobiles->pluck('brand_id'))
            ->unique()
            ->all();

        $brands = DB::table('brands')
            ->select('id', 'name')
            ->whereIn('id', $brandIds)
            ->get()
            ->keyBy('id');

        // Bulk load search indices (single query)
        $searchIndexes = DB::table('phone_search_indices')
            ->select(
                'phone_id',
                'min_ram AS ram',
                'min_storage AS storage',
                'min_price_usd',
                'storage_type',
                'ram_type',
                'min_price_pkr as min_price'
            )
            ->whereIn('phone_id', $allPhoneIds)
            ->get()
            ->keyBy('phone_id');

        // Attach relationships helper
        $attachRelations = function ($collection) use ($searchIndexes, $brands) {
            return $collection->map(function ($phone) use ($searchIndexes, $brands) {
                $phone->brand = $brands[$phone->brand_id] ?? null;
                $phone->searchIndex = $searchIndexes[$phone->id] ?? (object) [
                    'ram' => null,
                    'storage' => null,
                    'min_price_usd' => null,
                    'storage_type' => null,
                    'ram_type' => null,
                    'min_price' => null,
                ];
                unset($phone->price_range); // Remove helper field
                return $phone;
            });
        };

        // Attach to all collections
        $latestMobiles = $attachRelations($latestMobiles);
        $upcomingMobiles = $attachRelations($upcomingMobiles);
        $popularMobiles = $attachRelations($popularMobiles);

        // Transform price range collections
        $priceRangeData = collect($priceRangeQueries)->keys()->mapWithKeys(function ($key) use ($mobilesByPriceRange, $attachRelations) {
            $phones = $mobilesByPriceRange->get($key, collect());
            return [$key => PhoneResource::collection($attachRelations($phones))];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'latest_mobiles' => PhoneResource::collection($latestMobiles),
                'upcoming_mobiles' => PhoneResource::collection($upcomingMobiles),
                'popular_mobiles' => PhoneResource::collection($popularMobiles),
                'price_ranges' => $priceRangeData,
            ],
        ]);
    }


    /**
     * Get single phone with complete details
     */
    public function show(string $slug): JsonResponse
    {
        $cacheKey = "phone_details_{$slug}";
        DB::enableQueryLog();
        $phone = Phone::with([
            'brand:id,name',
            'specifications',
            'searchIndex',
            'colors',
            'colors.images',
            'variants' => function ($query) {
                // ->with(['ram_type:id,name', 'storage_type:id,name'])
                $query->orderBy('storage')
                    ->orderBy('pkr_price');
            },
            // Competitors only need minimal info: id, name, slug, primary_image, brand_id
            'competitors' => function ($q) {
                $q->select('phones.id', 'phones.name', 'brand_id', 'phones.slug', 'phones.primary_image') // minimal fields
                    ->with('searchIndex'); // eager load searchIndex
            }
        ])
            ->where('slug', $slug)
            ->firstOrFail();
        $competitorIds = $phone->competitors->pluck('id')->toArray();
        $ramOptions = $phone->searchIndex->ram_options
            ? json_decode($phone->searchIndex->ram_options, true)
            : [];

        $storageOptions = $phone->searchIndex->storage_options
            ? json_decode($phone->searchIndex->storage_options, true)
            : [];
        $minPrice = $phone->searchIndex->min_price_pkr ?? 0;
        $maxPrice = $phone->searchIndex->max_price_pkr ?? 0;
        $priceRange = [$minPrice * 0.85, $maxPrice * 1.15];
        $avgPrice = ($phone->searchIndex->min_price_pkr + $phone->searchIndex->max_price_pkr) / 2;
        $similarMobiles = $this->getSimilarMobiles($phone->id, $avgPrice, $ramOptions, $storageOptions, $priceRange, $competitorIds);

        //dd($similarMobiles->toSql(), $similarMobiles->getBindings());
        // ->get(['id', 'name', 'slug', 'primary_image', 'brand_id']);
        return response()->json([
            'success' => true,
            'data' => new PhoneResource(resource: $phone),
            'similarMobiles' => PhoneResource::collection($similarMobiles),
        ]);
    }

    /**
     * Get phone variants with stock and pricing
     */
    public function variants(string $id): JsonResponse
    {
        $cacheKey = "phone_variants_{$id}";

        $variants = Cache::remember($cacheKey, 300, function () use ($id) {
            $phone = Phone::findOrFail($id);

            return $phone->variants()
                ->select([
                    'id',
                    'color_name',
                    'color_slug',
                    'color_hex',
                    'storage_size',
                    'storage_gb',
                    'price',
                    'stock_quantity',
                    'is_available',
                    'images'
                ])
                ->where('is_available', true)
                ->orderBy('storage_gb')
                ->orderBy('price')
                ->get()
                ->groupBy('color_slug');
        });

        return response()->json([
            'success' => true,
            'data' => $variants->map(function ($group) {
                return [
                    'color_name' => $group->first()->color_name,
                    'color_slug' => $group->first()->color_slug,
                    'color_hex' => $group->first()->color_hex,
                    'images' => $group->first()->images,
                    'variants' => $group->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'storage_size' => $variant->storage_size,
                            'storage_gb' => $variant->storage_gb,
                            'price' => $variant->price,
                            'stock_quantity' => $variant->stock_quantity,
                            'is_available' => $variant->is_available,
                        ];
                    })->values()
                ];
            })->values()
        ]);
    }

    public function phones(Request $request)
    {
        $validated = $request->validate([
            'filters.brands' => 'nullable|array',
            'filters.ram' => 'nullable|array',
            'filters.storage' => 'nullable|array',
            'filters.os' => 'nullable|array',
            'filters.os.*' => 'string',
            'filters.has_5g' => 'nullable|boolean',
            'filters.priceRange' => 'nullable|array',
            'filters.screenSize' => 'nullable|array',
            'filters.batteryCapacity' => 'nullable|array',
            'filters.mobileStatus' => ['nullable', Rule::in(['price-low-to-high', 'price-high-to-low', 'upcoming', 'new'])],
            'per_page' => 'nullable|numeric|min:0|max:50',
            'page' => 'nullable|numeric|min:0',
        ]);

        $filters = $validated['filters'] ?? [];
        $perPage = min($validated['per_page'] ?? 30, 50); // Cap at 50
        $page = $validated['page'] ?? 1;

        // Only cache if no filters or very common filter combinations
        $shouldCache = empty(array_filter($filters)) || $page <= 2;
        if ($shouldCache) {
            $cacheKey = 'phones:' . md5(json_encode([
                'filters' => $filters,
                'page' => $page,
                'per_page' => $perPage
            ]));

            //$response = $this->executeFilterQuery($filters, $perPage, $page);

            $response = Cache::remember($cacheKey, now()->addHours(48), function () use ($filters, $perPage, $page) {
                return $this->executeFilterQuery($filters, $perPage, $page);
            });
        } else {

            $response = $this->executeFilterQuery($filters, $perPage, $page);
        }

        return response()->json([
            'success' => true,
            'data' => PhoneResource::collection($response['phones']),
            'pagination' => $response['pagination'],
            'filters_applied' => array_filter($validated),
        ]);
    }

    // public function phones_1(Request $request)
    // {

    //     $validated = $request->validate([
    //         'filters.brands' => 'nullable|array',
    //         'filters.ram' => 'nullable|array',
    //         'filters.storage' => 'nullable|array',
    //         'filters.os' => 'nullable|array',
    //         'filters.os.*' => 'string',
    //         'filters.has_5g' => 'nullable|boolean',
    //         'filters.priceRange' => 'nullable|array',
    //         'filters.screenSize' => 'nullable|array',
    //         'filters.batteryCapacity' => 'nullable|array',
    //         'filters.mobileStatus' => ['nullable', Rule::in(['price-low-to-high', 'price-high-to-low', 'upcoming', 'new'])],
    //         'per_page' => 'nullable|numeric|min:0',
    //         'page' => 'nullable|numeric|min:0',
    //     ]);

    //     $filters = $validated['filters'] ?? [];
    //     $perPage = $validated['per_page'] ?? 30;
    //     // $query = Phone::active()->with(['brand:id,name', 'searchIndex']);
    //     $page = $validated['page'] ?? 1;
    //     // Generate a unique cache key
    //     $cacheKey = 'phones:' . md5(json_encode([
    //         'filters' => $filters,
    //         'page' => $page,
    //         'per_page' => $perPage
    //     ]));

    //     $phones = Cache::remember($cacheKey, now()->addHours(48), function () use ($filters, $perPage, $page) {

    //         $query = Phone::active()->with(['brand:id,name', 'searchIndex']);
    //         // Brands
    //         if (!empty($filters['brands'])) {
    //             $brands = array_map('strtolower', $filters['brands']);
    //             $query->whereHas('brand', fn($q) => $q->whereIn(DB::raw('LOWER(name)'), $brands));
    //         }
    //         // Price Range
    //         if (!empty($filters['priceRange'])) {
    //             $query->whereHas('searchIndex', function ($q) use ($filters) {
    //                 $min = $filters['priceRange'][0] ?? null;
    //                 $max = $filters['priceRange'][1] ?? null;

    //                 if (!is_null($min)) {
    //                     $q->where(function ($query) use ($min) {
    //                         $query->where('max_price_pkr', '>=', $min)
    //                             ->orWhere(function ($q) use ($min) {
    //                                 $q->where('max_price_pkr', '<=', 0)
    //                                     ->where('min_price_pkr', '>=', $min);
    //                             });
    //                     })
    //                         ->where(function ($query) {
    //                             // Ensure at least one price is valid
    //                             $query->where('max_price_pkr', '>', 0)
    //                                 ->orWhere('min_price_pkr', '>', 0);
    //                         });
    //                 }

    //                 if (!is_null($max)) {
    //                     $q->where(function ($query) use ($max) {
    //                         $query->where('min_price_pkr', '<=', $max)
    //                             ->orWhere(function ($q) use ($max) {
    //                                 $q->where('min_price_pkr', '<=', 0)
    //                                     ->where('max_price_pkr', '<=', $max);
    //                             });
    //                     })
    //                         ->where(function ($query) {
    //                             // Ensure at least one price is valid
    //                             $query->where('min_price_pkr', '>', 0)
    //                                 ->orWhere('max_price_pkr', '>', 0);
    //                         });
    //                 }
    //             });
    //         }
    //         // RAM
    //         if (!empty($filters['ram'])) {
    //             $ramValues = array_map('intval', $filters['ram']);

    //             $query->whereHas('searchIndex', function ($q) use ($ramValues) {
    //                 $q->where(function ($subQ) use ($ramValues) {
    //                     foreach ($ramValues as $value) {
    //                         $subQ->orWhereRaw("JSON_CONTAINS(ram_options, ?)", [json_encode($value)]);
    //                     }
    //                 });
    //             });
    //         }

    //         // Storage
    //         if (!empty($filters['storage'])) {
    //             $storageValues = array_map('intval', $filters['storage']);

    //             $query->whereHas('searchIndex', function ($q) use ($storageValues) {
    //                 $q->where(function ($subQ) use ($storageValues) {
    //                     foreach ($storageValues as $value) {
    //                         $subQ->orWhereRaw("JSON_CONTAINS(storage_options, ?)", [json_encode($value)]);
    //                     }
    //                 });
    //             });
    //         }

    //         // Features
    //         if (!empty($filters['screenSize'])) {
    //             $query->whereHas('searchIndex', function ($q) use ($filters) {
    //                 $q->whereColumn('phones.id', 'phone_search_indices.phone_id'); // ensures the join condition
    //                 $q->where(function ($q2) use ($filters) {
    //                     foreach ($filters['screenSize'] as $range) {
    //                         if (preg_match('/^(\d+(\.\d+)?)to(\d+(\.\d+)?)$/', $range, $matches)) {
    //                             $min = floatval($matches[1]);
    //                             $max = floatval($matches[3]);
    //                             $q2->orWhereBetween('screen_size_inches', [$min, $max]);
    //                         }
    //                     }
    //                 });
    //             });
    //         }

    //         if (!empty($filters['batteryCapacity'])) {
    //             $query->whereHas('searchIndex', function ($q) use ($filters) {
    //                 $q->where(function ($q2) use ($filters) {
    //                     foreach ($filters['batteryCapacity'] as $value) {
    //                         $value = urldecode($value); // decode %26 to &

    //                         // Range: "5000-5999mAh"
    //                         if (preg_match('/^(\d+)-(\d+)/', $value, $matches)) {
    //                             $q2->orWhereBetween('battery_capacity_mah', [
    //                                 intval($matches[1]),
    //                                 intval($matches[2])
    //                             ]);
    //                             continue;
    //                         }

    //                         // "6000 & Above" or "6000&Above"
    //                         if (preg_match('/^(\d+)\s*&?\s*Above$/i', $value, $matches)) {
    //                             $q2->orWhere('battery_capacity_mah', '>=', intval($matches[1]));
    //                             continue;
    //                         }

    //                         // Exact value: "3000mAh"
    //                         if (preg_match('/^(\d+)/', $value, $matches)) {
    //                             $q2->orWhere('battery_capacity_mah', intval($matches[1]));
    //                         }
    //                     }
    //                 });
    //             });
    //         }

    //         if (!empty($filters['mobileStatus'])) {

    //             $status = $filters['mobileStatus'];

    //             // Price: Low → High
    //             if ($status === 'price-low-to-high') {
    //                 $query->whereHas('searchIndex', function ($q) {
    //                     $q->orderBy('min_price_pkr', 'asc');
    //                 });
    //             }

    //             // Price: High → Low
    //             elseif ($status === 'price-high-to-low') {
    //                 $query->whereHas('searchIndex', function ($q) {
    //                     $q->orderBy('max_price_pkr', 'desc');
    //                 });
    //             }

    //             // Upcoming phones
    //             elseif ($status === 'upcoming') {
    //                 $query->where('status', 'upcoming')
    //                     ->orderByDesc('release_date');
    //             }

    //             // New / Default
    //             else {
    //                 $query->orderByDesc('release_date');
    //             }
    //         }


    //         // If no filters, return mixed phones
    //         if (empty(array_filter($filters))) {
    //             $query->select('id', 'name', 'brand_id', 'slug', 'release_date', 'primary_image', 'status', 'updated_at')
    //                 ->with('brand:id,name')
    //                 ->orderBy('release_date', 'desc');
    //         }

    //         // Paginate
    //         return $query->paginate($perPage, ['phones.*'], 'page', $page);
    //         //dd($query->toSql(), $query->getBindings());
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'data' => PhoneResource::collection($phones),
    //         'pagination' => [
    //             'current_page' => $phones->currentPage(),
    //             'from' => $phones->firstItem(),
    //             'last_page' => $phones->lastPage(),
    //             'per_page' => $phones->perPage(),
    //             'to' => $phones->lastItem(),
    //             'total' => $phones->total(),
    //             'first_page_url' => $phones->url(1),
    //             'last_page_url' => $phones->url($phones->lastPage()),
    //             'next_page_url' => $phones->nextPageUrl(),
    //             'prev_page_url' => $phones->previousPageUrl(),
    //             'path' => $phones->path(),
    //         ],
    //         'filters_applied' => array_filter($validated),
    //     ]);
    // }

    public function getPhoneBySlug(Request $request)
    {
        $slugs = Phone::pluck('slug'); // pluck returns array of values
        return response()->json(["data" => $slugs]);
    }

    public function getStaticFilters()
    {
        // Static data
        $brands = ['samsung', 'xiaomi', 'iphone'];
        $rams = ['4gb', '8gb', '12gb'];
        $storages = ['64gb', '128gb', '256gb'];

        $params = [];

        // Case 1: RAM + Storage only (no brand)
        foreach ($rams as $ram) {
            foreach ($storages as $storage) {
                $params[] = [
                    'filters' => ["{$ram}-{$storage}"]
                ];
            }
        }

        // Case 2: Brand + RAM + Storage
        foreach ($brands as $brand) {
            foreach ($rams as $ram) {
                foreach ($storages as $storage) {
                    $params[] = [
                        'filters' => [$brand, "{$ram}-{$storage}"]
                    ];
                }
            }
        }

        return response()->json([
            'data' => $params
        ]);
    }

    public function brands()
    {
        $brands = Brand::all();
        return response()->json(["data" => $brands]);
    }

    public function count()
    {
        $count = Phone::count();
        return response()->json(["count" => $count]);
    }

    public function compare(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'slugs' => 'required|array|min:1|max:4',
            'slugs.*' => 'string|exists:phones,slug',
            'profile' => 'nullable|string|in:balanced,gaming,camera,battery,budget_conscious,media_consumer,business_professional',
        ]);

        $slugs = $validated['slugs'];
        $profile = $validated['profile'] ?? 'balanced';
        $phones = Phone::select(
            'id',
            'name',
            'brand_id',
            'slug',
            'release_date',
            'primary_image',
            'primary_color',
            'updated_at',
            'is_popular',
            'status'
        )
            ->with([
                'specifications' => function ($query) {
                    $query->select('phone_id', 'category', 'specifications');
                }
            ])
            ->whereIn('slug', $slugs)
            ->get();

        // Early return if no phones found
        if ($phones->isEmpty()) {
            return response()->json(['error' => 'No phones found'], 404);
        }

        // Step 2: Extract IDs in one go
        $phoneIds = $phones->pluck('id')->all();
        $brandIds = $phones->pluck('brand_id')->unique()->all();

        // Step 3: Parallel data loading (more efficient than sequential)
        [$brands, $searchIndexes] = [
            DB::table('brands')
                ->select('id', 'name', 'logo') // Add logo if needed
                ->whereIn('id', $brandIds)
                ->get()
                ->keyBy('id'),

            DB::table('phone_search_indices')
                ->select(
                    'phone_id',
                    'min_ram AS ram',
                    'min_storage AS storage',
                    'min_price_usd',
                    'storage_type',
                    'ram_type',
                    'min_price_pkr AS min_price'
                )
                ->whereIn('phone_id', $phoneIds)
                ->get()
                ->keyBy('phone_id')
        ];
        $scoredPhones = $phones->map(function ($phone) use ($searchIndexes, $brands, $profile) {
            $phone->brand = $brands[$phone->brand_id] ?? null;
            $phone->searchIndex = $searchIndexes[$phone->id] ?? (object) [
                'ram' => null,
                'storage' => null,
                'min_price_usd' => null,
                'storage_type' => null,
                'ram_type' => null,
                'min_price' => null,
            ];

            $s = $phone->specifications
                ->keyBy('category')
                ->map(
                    static fn($spec) => $spec->specifications
                    ? json_decode($spec->specifications, true)
                    : []
                )
                ->toArray();
            $phone->scores = $this->phoneService->scoreByCategory($s, $phone->brand, $profile);
            return $phone;
        });

        $verdict = $this->generateVerdict($scoredPhones, $profile);

        $chartData = $this->formatChartData($scoredPhones, $profile);
        return response()->json([
            'success' => true,
            'profile' => $profile,
            'data' => $scoredPhones->map(fn($phone) => new PhoneResource($phone, true)),
            'comparison' => [
                'scores' => $scoredPhones->map(function ($phone) use ($profile) {
                    return [
                        'phone_id' => $phone->id,
                        'phone_name' => $phone->name,
                        'primary_color' => $phone->primary_color,
                        'category_scores' => $phone->scores,
                        'total_score' => $this->calculateTotalScore($phone->scores ?? [], $profile),
                    ];
                }),
                'verdict' => $verdict,
                'winner' => $verdict['overall_recommendation']['recommended_phone'],
                'charts' => $chartData,
            ],
        ]);
    }

    public function scorePhone($specs, $scorer, $profile)
    {
        $s = $specs->keyBy('category')
            ->map(fn($spec) => json_decode($spec->specifications, true) ?: []);
        $benchmark = getBenchmark($s['performance']['benchmark'] ?? '');
        $buildMaterials = buildMaterials($s['build']['build'] ?? '');
        $mobileDimensions = getMobileDimensions($s['build']['dimensions'] ?? []);
        $cameraApertures = extractCameraApertures($s['main_camera']);
        $cameraOpticalZoom = extractOpticalZoom($s['main_camera']);
        $cameratabilization = extractStabilization($s['main_camera']);
        $cameraSetup = parseCameraSetup($s['main_camera']['setup']);
        $cameraFlash = getFlash($s['main_camera']);
        $cameraVideo = extractVideo($s['main_camera']['video']);
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
        $memoryParsed = parseMemory($s['memory']['memory'] ?? '');
        try {
            $wiredChargingSpec = $s['battery']['charging_speed'] ?? '';
            $wirlessCharging = $s['battery']['wireless'] ?? '';
            $reverceCharging = $s['battery']['reverse'] ?? '';
            $screenGlassType = extractScreenGlassType($s['display']['protection'] ?? null);
            $formatGlassProtection = formatGlassProtection($screenGlassType ?? []);
            return [
                'display' => $scorer->scoreCategory('display', [
                    // Core panel basics
                    'size' => extractSize($s['display']['size'] ?? null),
                    'type' => getShortDisplay($s['display']['type'] ?? null),
                    'resolution' => shortResolution($s['display']['resolution'] ?? null),
                    'aspect_ratio' => $s['display']['aspect_ratio'] ?? null,
                    'screen_ratio' => (float) str_replace('%', '', $s['display']['screen_to_body_ratio'] ?? 'N/A'),
                    'pixel_density' => extractPpi($s['display']['resolution'] ?? null),

                    // Motion & responsiveness
                    'refresh_rate' => extractNumber($s['display']['refresh_rate'] ?? null),
                    'adaptive_refresh_rate' => preg_replace('/\D+/', '', $s['display']['adaptive_refresh_rate_range'] ?? null),
                    'touch_sampling_rate' => preg_replace('/\D+/', '', $s['display']['touch_sampling_rate'] ?? null),

                    // Brightness & visibility
                    'brightness_peak' => extractBrightness($s['display']['brightness'] ?? '', 'peak'),
                    'brightness_typical' => extractBrightness($s['display']['brightness'] ?? '', 'typical'),
                    'contrast_ratio' => isset($s['display']['contrast_ratio'])
                        ? str_replace(',', '', explode(':', $s['display']['contrast_ratio'])[0])
                        : null,
                    'hdr_support' => getHdrSupport($s['display']['features'] ?? ''),

                    // Eye care & protection
                    'pwm' => extractNumber($s['display']['pwm_frequency'] ?? null),
                    'glass_protection' => $formatGlassProtection,
                    'has_branded_glass' => $screenGlassType['has_branded_glass'] ?? null,

                    // Features
                    'always_on_display' => $s['display']['always_on_display'] ?? 'NO',
                ], $profile),

                'performance' => $scorer->scoreCategory('performance', [
                    'chipset' => getShortChipset($s['performance']['chipset'] ?? null),
                    'ram' => $memoryParsed['ram'],
                    'storage_capacity' => $memoryParsed['storage'],
                    'cpu' => cpuType($s['performance']['cpu']) ?? null,
                    'gpu' => $s['performance']['gpu'] ?? null,
                    'storage_type' => $s['memory']['storage_type'] ?? null,
                    'ram_type' => $s['memory']['ram_type'] ?? null,
                    'instant_touch_sampling_rate' => preg_replace('/\D+/', '', $s['performance']['instant_touch_sampling_rate'] ?? null),
                    //'antutu_score_(v10)' => $benchmark['antutu'] ?? null,
                    'card_slot' => $s['memory']['card_slot'] ?? 'NO'

                ], $profile),
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
                            'front_video' => extractVideo($s['selfie_camera']['video']) ?? null,
                        ]
                    ),
                    $profile
                ),
                'battery' => $scorer->scoreCategory(
                    'battery',
                    [
                        "type" => parseBatteryType($s['battery']['type']),
                        'capacity' => parseBatteryCapacity($s['battery']['capacity']) ?? null,
                        'Fast' => parseFastChargingToWatts($wiredChargingSpec),
                        'Wirless' => parseFastChargingToWatts($wirlessCharging ?? 0),
                        'Reverce' => parseFastChargingToWatts($reverceCharging ?? 0),
                    ],
                    $profile
                ),
                'build' => $scorer->scoreCategory(
                    'build',
                    [
                        'dimensions' => $mobileDimensions['dimensions'] ?? null,
                        'thickness' => $mobileDimensions['thickness'] ?? null,
                        'weight' => $s['build']['weight'] !== null
                            ? (float) preg_replace('/[^0-9.]/', '', $s['build']['weight'])
                            : null,
                        'build_material' => $buildMaterials['build_material'] ?? null,
                        'back_material' => $buildMaterials['back_material'] ?? null,
                        'ip_rating' => shortIPRating($s['build']['ip_rating']) ?? null,
                    ],
                    $profile
                ),

                'features' => $scorer->scoreCategory(
                    'features',
                    [
                        'nfc' => $s['connectivity']['nfc'] ?? null,
                        'stereo_speakers' => $s['audio']['stereo'] ?? null,
                        '3.5mm_jack' => $s['audio']['3.5mm_jack'] ?? null,
                        "infrared" => $s['connectivity']['infrared'] ?? null,
                        'wifi' => formatWifiValue($s['connectivity']['wifi']),
                        'bluetooth_version' => isset($s['connectivity']['bluetooth'])
                            ? (preg_match('/v([\d.]+)/i', $s['connectivity']['bluetooth'], $m) ? $m[1] : null)
                            : null,
                        'usb' => formatUsbLabel($s['connectivity']['usb']),
                    ],
                    $profile
                ),
                // 'software' => $scorer->scoreCategory(
                //     'software',
                //     [
                //         'os' => mobileVersion($s['performance']['os'] ?? null),
                //         'update_policy' => $s['performance']['update_policy'] ?? null, // e.g., "3 years security updates"
                //         'extra_features' => $s['software']['features'] ?? null, // e.g., gestures, multitasking, AI features
                //     ],
                //     $profile
                // ),
                // 'value' => $scorer->scoreCategory(
                //     'value',
                //     [
                //         'price' => $s['pricing']['price'] ?? null,
                //         'spec_score_sum' => array_sum([
                //             $s['performance']['chipset_score'] ?? 0,
                //             $s['camera']['score'] ?? 0,
                //             $s['battery']['score'] ?? 0,
                //             $s['display']['score'] ?? 0,
                //             $s['build']['score'] ?? 0,
                //             $s['features']['score'] ?? 0,
                //         ]),
                //         'price_performance_ratio' => isset($s['pricing']['price']) && isset($s['performance']['chipset_score'])
                //             ? $s['performance']['chipset_score'] / $s['pricing']['price']
                //             : null,
                //     ],
                //     $profile
                // ),
            ];
        } catch (\Exception $e) {
            \Log::error("Error in getCompareSpecsAttribute for phone: " . $e->getMessage());
            echo "<pre>";
            print_r($e->getMessage());
            exit;
            return ['key' => [], 'expandable' => []];
        }
    }

    private function generateVerdict($phones, $profile)
    {

        $verdictConfig = config('compare_scoring.compare_verdict');
        $categoryWinners = [];
        $categoryVerdicts = [];

        // Get all categories from config (including new ones)
        $categories = array_keys(array_filter($verdictConfig, function ($item) {
            return isset($item['label']) && isset($item['thresholds']);
        }));

        // Determine winner for each category
        foreach ($categories as $category) {

            $categoryScores = $phones->map(function ($phone) use ($category) {
                return [
                    'phone' => $phone,
                    'score' => $phone->scores[$category]['score'] ?? 0,
                ];
            })->sortByDesc('score');

            $winner = $categoryScores->first();
            $runnerUp = $categoryScores->skip(1)->first();

            $scoreDiff = $winner['score'] - ($runnerUp['score'] ?? 0);
            $thresholds = $verdictConfig[$category]['thresholds'];

            // Determine verdict type based on thresholds
            if ($scoreDiff >= $thresholds['decisive']) {
                $verdictType = 'decisive';
            } elseif ($scoreDiff >= $thresholds['notable']) {
                $verdictType = 'notable';
            } elseif ($scoreDiff >= $thresholds['marginal']) {
                $verdictType = 'marginal';
            } else {
                $verdictType = 'close';
            }

            $winnerKey = $this->phoneKey($winner['phone']);
            $runnerKey = $runnerUp ? $this->phoneKey($runnerUp['phone']) : null;

            // Store category analysis
            $categoryWinners[$category] = [
                'winner' => $winnerKey,
                'runner_up' => $runnerKey,
                'score' => $winner['score'],
                'difference' => round($scoreDiff, 1),
                'verdict_type' => $verdictType,
                'category_label' => $verdictConfig[$category]['label'] ?? ucfirst($category),
            ];

            // Generate verdict text with advantages
            $categoryVerdicts[$category] = $this->generateCategoryVerdict(
                $category,
                $winner,
                $runnerUp,
                $scoreDiff,
                $verdictType,
                $verdictConfig
            );
        }

        return [
            'category_winners' => $categoryWinners,
            'category_verdicts' => $categoryVerdicts,
            'overall_recommendation' => $this->generateOverallRecommendation($phones, $categoryWinners, $profile),
        ];
    }

    private function phoneKey($phone)
    {
        return trim($phone->brand->name . ' ' . $phone->name);
    }
    private function generateOverallRecommendation($phones, $categoryWinners, $profile)
    {
        $verdictConfig = config('compare_scoring.compare_verdict');
        $phoneAnalysis = [];

        foreach ($phones as $phone) {
            $phoneKey = $this->phoneKey($phone);
            $weightedWins = 0;
            $totalWins = 0;
            $winTypes = [
                'dominant' => [],
                'decisive' => [],
                'notable' => [],
                'close' => [],
                'marginal' => [],
            ];

            // Analyze wins for each category
            foreach ($categoryWinners as $category => $data) {
                if ($data['winner'] === $phoneKey) {
                    $totalWins++;
                    $categoryWeight = $verdictConfig[$category]['weight'] ?? 1.0;

                    // Different scoring based on win type
                    $typeMultiplier = match ($data['verdict_type']) {
                        'decisive' => 3.0,
                        'notable' => 2.0,
                        'marginal' => 1.5,
                        'close' => 1.2,
                        default => 1.0,
                    };

                    $weightedWins += $categoryWeight * $typeMultiplier;
                    $winTypes[$data['verdict_type']][] = $category;
                }
            }

            // Bonus for high-priority category wins
            $highPriorityBonus = 0;
            foreach ($winTypes as $type => $categories) {
                foreach ($categories as $category) {
                    $priority = $verdictConfig[$category]['priority'] ?? 'medium';
                    if ($priority === 'critical') {
                        $highPriorityBonus += 2.0;
                    } elseif ($priority === 'high') {
                        $highPriorityBonus += 1.0;
                    }
                }
            }
            $weightedWins += $highPriorityBonus;

            $phoneAnalysis[$phoneKey] = [
                'weighted_score' => $weightedWins,
                'total_wins' => $totalWins,
                'win_types' => $winTypes,
                'total_score' => $this->calculateTotalScore($phone->scores, $profile),
                'phone_object' => $phone,
            ];
        }

        // Sort phones by analysis
        uasort($phoneAnalysis, function ($a, $b) {
            // Primary: Weighted score
            if (abs($a['weighted_score'] - $b['weighted_score']) > 2.0) {
                return $b['weighted_score'] <=> $a['weighted_score'];
            }

            // Secondary: Total wins
            if ($a['total_wins'] !== $b['total_wins']) {
                return $b['total_wins'] <=> $a['total_wins'];
            }

            // Tertiary: Critical category wins
            $aCritical = count($a['win_types']['decisive'] ?? []) + count($a['win_types']['dominant'] ?? []);
            $bCritical = count($b['win_types']['decisive'] ?? []) + count($b['win_types']['dominant'] ?? []);
            if ($aCritical !== $bCritical) {
                return $bCritical <=> $aCritical;
            }

            // Quaternary: Total score
            return $b['total_score'] <=> $a['total_score'];
        });

        $recommendedPhoneKey = array_key_first($phoneAnalysis);
        $recommendedAnalysis = $phoneAnalysis[$recommendedPhoneKey];
        $runnerUpKey = array_keys($phoneAnalysis)[1] ?? null;
        $runnerUpAnalysis = $runnerUpKey ? $phoneAnalysis[$runnerUpKey] : null;

        // Generate the recommendation
        $message = $this->generateNuancedMessage(
            $recommendedPhoneKey,
            $recommendedAnalysis,
            $runnerUpAnalysis,
            $categoryWinners,
            $profile,
            $verdictConfig
        );

        return [
            'message' => $message,
            'recommended_phone' => $recommendedPhoneKey,
            'weighted_score' => round($recommendedAnalysis['weighted_score'], 2),
            'total_wins' => $recommendedAnalysis['total_wins'],
            'win_breakdown' => $recommendedAnalysis['win_types'],
            'confidence_level' => $this->calculateConfidence($recommendedAnalysis, $runnerUpAnalysis),
            'key_differentiators' => $this->identifyKeyDifferentiators($categoryWinners, $recommendedPhoneKey),
            'profile_match' => $this->calculateProfileMatch($recommendedPhoneKey, $categoryWinners, $profile, $verdictConfig),
            'caveats' => $this->addContextualCaveats($categoryWinners, $recommendedPhoneKey),
            'trade_off_message' => $this->generateTradeOffMessage($phones, $categoryWinners, $verdictConfig),
        ];
    }

    private function getMagnitudeDescriptor($scoreDiff, $modifiers)
    {
        if ($scoreDiff >= ($modifiers['game_changer'] ?? 25)) {
            return 'game-changing difference';
        } elseif ($scoreDiff >= ($modifiers['dominant'] ?? 18)) {
            return 'dominant advantage';
        } elseif ($scoreDiff >= ($modifiers['significant'] ?? 12)) {
            return 'significant advantage';
        } elseif ($scoreDiff >= ($modifiers['noticeable'] ?? 8)) {
            return 'noticeable advantage';
        } elseif ($scoreDiff >= ($modifiers['slight'] ?? 4)) {
            return 'slight advantage';
        } elseif ($scoreDiff >= ($modifiers['marginal'] ?? 1)) {
            return 'marginal advantage';
        }

        return null;
    }

    private function generateCategoryVerdict($category, $winner, $runnerUp, $scoreDiff, $verdictType, $verdictConfig)
    {
        $winnerPhone = $winner['phone'];
        $winnerName = $winnerPhone->brand->name . ' ' . $winnerPhone->name;

        if ($verdictType === 'close') {
            return $verdictConfig[$category]['verdicts']['close'];
        }

        // Get the template
        $template = $verdictConfig[$category]['verdicts']['winner'];

        // Get specific advantages from scores
        $advantages = [];
        $winnerScores = $winnerPhone->scores[$category] ?? [];
        $runnerScores = $runnerUp['phone']->scores[$category] ?? [];

        if (isset($winnerScores['advantages']) && is_array($winnerScores['advantages'])) {
            foreach ($winnerScores['advantages'] as $advantageKey => $advantageValue) {
                if (isset($verdictConfig[$category]['verdicts']['advantages'][$advantageKey])) {
                    $advantageTemplate = $verdictConfig[$category]['verdicts']['advantages'][$advantageKey];
                    $advantages[] = str_replace('{value}', $advantageValue, $advantageTemplate);
                }
            }
        }

        // Build final verdict
        $verdict = str_replace('{phone}', $winnerName, $template);

        if (!empty($advantages)) {
            $verdict .= ' Specifically: ' . $this->formatList($advantages, 'sentence');
        }

        // Add magnitude descriptor if not close
        $magnitude = $this->getMagnitudeDescriptor($scoreDiff, $verdictConfig['score_modifiers'] ?? []);
        if ($magnitude && $verdictType !== 'close') {
            $verdict .= ' (' . $magnitude . ')';
        }

        return $verdict;
    }

    private function generateNuancedMessage($phoneName, $analysis, $runnerUpAnalysis, $categoryWinners, $profile, $verdictConfig)
    {
        $messages = [];
        $totalCategories = count($categoryWinners);

        // Start with strong opening if we have decisive/dominant wins
        $decisiveWins = count($analysis['win_types']['decisive'] ?? []);
        $dominantWins = count($analysis['win_types']['dominant'] ?? []);

        if ($dominantWins > 0) {
            $dominantCategories = array_map(
                fn($cat) => $verdictConfig[$cat]['label'] ?? ucfirst($cat),
                $analysis['win_types']['dominant']
            );
            $messages[] = "🔥 **Dominates** in " . $this->formatList($dominantCategories) .
                " - these are game-changing advantages";
        } elseif ($decisiveWins > 0) {
            $decisiveCategories = array_map(
                fn($cat) => $verdictConfig[$cat]['label'] ?? ucfirst($cat),
                $analysis['win_types']['decisive']
            );
            $messages[] = "⭐ **Significantly outperforms** in " . $this->formatList($decisiveCategories);
        }

        // Add profile-specific context
        if ($profile !== 'balanced' && isset($verdictConfig['profile_priorities'][$profile])) {
            $profileInfo = $verdictConfig['profile_priorities'][$profile];
            $profileCategories = $profileInfo['categories'] ?? [];

            $profileWins = array_intersect(
                array_merge(...array_values($analysis['win_types'])),
                $profileCategories
            );

            if (!empty($profileWins)) {
                $profileWinLabels = array_map(
                    fn($cat) => $verdictConfig[$cat]['label'] ?? ucfirst($cat),
                    $profileWins
                );
                $messages[] = "🎯 **Perfect for {$profile} users**: Wins in key areas like " .
                    $this->formatList($profileWinLabels);
            }
        }

        // Mention total win count if winning majority
        if ($analysis['total_wins'] > $totalCategories / 2) {
            $winPercentage = round(($analysis['total_wins'] / $totalCategories) * 100);
            $messages[] = "📊 **Wins {$analysis['total_wins']} out of {$totalCategories} categories** ({$winPercentage}%)";
        }

        // Mention notable wins
        $notableWins = count($analysis['win_types']['notable'] ?? []);
        if ($notableWins > 0) {
            $notableCategories = array_map(
                fn($cat) => $verdictConfig[$cat]['label'] ?? ucfirst($cat),
                $analysis['win_types']['notable']
            );
            $messages[] = "📈 **Excels** in " . $this->formatList($notableCategories);
        }

        // Acknowledge areas where it's close or loses
        if ($runnerUpAnalysis) {
            $closeLosses = [];
            foreach ($categoryWinners as $category => $data) {
                if ($data['winner'] !== $phoneName && $data['verdict_type'] === 'close') {
                    $closeLosses[] = $verdictConfig[$category]['label'] ?? ucfirst($category);
                }
            }

            if (count($closeLosses) <= 2 && !empty($messages)) {
                $messages[] = "🤏 **Close calls**: Differences in " . $this->formatList($closeLosses) . " are minimal";
            }
        }

        // Add value consideration if available
        if (isset($categoryWinners['value']) && $categoryWinners['value']['winner'] === $phoneName) {
            $valueDiff = $categoryWinners['value']['difference'] ?? 0;
            if ($valueDiff >= 15) {
                $messages[] = "💰 **Excellent value**: Significantly better price-to-performance ratio";
            }
        }

        // Build final message
        if (empty($messages)) {
            return "📱 **{$phoneName}** is recommended based on overall balanced performance.";
        }

        return "📱 **{$phoneName}** " . lcfirst(implode('. ', $messages)) . ".";
    }
    private function formatList($items, $type = 'simple')
    {
        if (empty($items)) {
            return '';
        }

        $items = array_unique($items);

        if (count($items) === 1) {
            return $items[0];
        }

        if (count($items) === 2) {
            return $items[0] . ' and ' . $items[1];
        }

        $last = array_pop($items);

        if ($type === 'sentence') {
            return implode(', ', $items) . ', and ' . $last;
        }

        return implode(', ', $items) . ', and ' . $last;
    }

    /**
     * Calculate recommendation confidence
     */
    private function calculateConfidence($analysis, $runnerUpAnalysis = null)
    {
        $confidenceScore = 0;

        // Base score from weighted wins
        $confidenceScore += $analysis['weighted_score'];

        // Bonus for decisive/dominant wins
        $confidenceScore += count($analysis['win_types']['dominant'] ?? []) * 5;
        $confidenceScore += count($analysis['win_types']['decisive'] ?? []) * 3;

        // Penalty if runner-up is close
        if ($runnerUpAnalysis) {
            $gap = $analysis['weighted_score'] - $runnerUpAnalysis['weighted_score'];
            if ($gap < 3) {
                $confidenceScore -= 2;
            }
        }

        if ($confidenceScore >= 15) {
            return [
                'level' => 'Very High',
                'score' => $confidenceScore,
                'phrase' => 'Strongly recommended',
                'icon' => '🎯'
            ];
        } elseif ($confidenceScore >= 10) {
            return [
                'level' => 'High',
                'score' => $confidenceScore,
                'phrase' => 'Recommended',
                'icon' => '👍'
            ];
        } elseif ($confidenceScore >= 6) {
            return [
                'level' => 'Moderate',
                'score' => $confidenceScore,
                'phrase' => 'Leans toward',
                'icon' => '🤔'
            ];
        } else {
            return [
                'level' => 'Low',
                'score' => $confidenceScore,
                'phrase' => 'Slight preference',
                'icon' => '⚖️'
            ];
        }
    }


    /**
     * Identify key differentiators
     */
    private function identifyKeyDifferentiators($categoryWinners, $recommendedPhone)
    {
        $differentiators = [];

        foreach ($categoryWinners as $category => $data) {
            if ($data['winner'] === $recommendedPhone) {
                $impact = match ($data['verdict_type']) {
                    'dominant' => 'game_changer',
                    'decisive' => 'high',
                    'notable' => 'medium',
                    default => 'low',
                };

                // Only include medium to high impact differentiators
                if (in_array($impact, ['game_changer', 'high', 'medium'])) {
                    $differentiators[] = [
                        'category' => $data['category_label'] ?? ucfirst($category),
                        'winner' => $data['winner'],
                        'margin' => $data['difference'],
                        'impact' => $impact,
                        'type' => $data['verdict_type'],
                    ];
                }
            }
        }

        // Sort by impact (game_changer > high > medium)
        usort($differentiators, function ($a, $b) {
            $impactOrder = ['game_changer' => 3, 'high' => 2, 'medium' => 1, 'low' => 0];
            return ($impactOrder[$b['impact']] ?? 0) <=> ($impactOrder[$a['impact']] ?? 0);
        });

        return $differentiators;
    }

    private function addContextualCaveats($categoryWinners, $recommendedPhone)
    {
        $caveats = [];

        foreach ($categoryWinners as $category => $data) {
            // Check for major losses (opponent has decisive or dominant win)
            if ($data['winner'] !== $recommendedPhone) {
                if ($data['verdict_type'] === 'dominant') {
                    $caveats[] = [
                        'type' => 'warning',
                        'category' => $data['category_label'] ?? ucfirst($category),
                        'message' => "⚠️ **{$data['winner']} has a massive advantage in {$data['category_label']}** ({$data['difference']} points). If this is critical for you, reconsider.",
                        'severity' => 'high',
                        'difference' => $data['difference'],
                    ];
                } elseif ($data['verdict_type'] === 'decisive' && $data['difference'] >= 20) {
                    $caveats[] = [
                        'type' => 'note',
                        'category' => $data['category_label'] ?? ucfirst($category),
                        'message' => "📝 **{$data['winner']} is significantly better in {$data['category_label']}**.",
                        'severity' => 'medium',
                        'difference' => $data['difference'],
                    ];
                }
            }
        }

        return $caveats;
    }

    private function calculateProfileMatch($phoneName, $categoryWinners, $profile, $verdictConfig)
    {
        if ($profile === 'balanced' || !isset($verdictConfig['profile_priorities'][$profile])) {
            return null;
        }

        $profileInfo = $verdictConfig['profile_priorities'][$profile];
        $priorityCategories = $profileInfo['categories'] ?? [];
        $profileWins = 0;
        $totalPriorityCategories = count($priorityCategories);

        foreach ($priorityCategories as $category) {
            if (isset($categoryWinners[$category]) && $categoryWinners[$category]['winner'] === $phoneName) {
                $profileWins++;
            }
        }

        if ($totalPriorityCategories === 0) {
            return null;
        }

        $matchPercentage = round(($profileWins / $totalPriorityCategories) * 100);

        return [
            'profile' => $profile,
            'match_percentage' => $matchPercentage,
            'wins_in_priority' => $profileWins,
            'total_priority_categories' => $totalPriorityCategories,
            'message' => $profileInfo['message'] ?? '',
        ];
    }

    private function generateTradeOffMessage($phones, $categoryWinners, $verdictConfig)
    {
        $phoneWins = [];

        foreach ($categoryWinners as $category => $data) {
            $phoneWins[$data['winner']][] = $verdictConfig[$category]['label'] ?? ucfirst($category);
        }

        $phonesArray = array_keys($phoneWins);

        if (count($phonesArray) < 2) {
            return $verdictConfig['overall']['templates']['too_close'] ?? "Both phones are very close in performance.";
        }

        $phone1 = $phonesArray[0];
        $phone2 = $phonesArray[1];
        $categories1 = $phoneWins[$phone1] ?? [];
        $categories2 = $phoneWins[$phone2] ?? [];

        if (empty($categories1) || empty($categories2)) {
            return $verdictConfig['overall']['templates']['too_close'] ?? "Both phones are very close in performance.";
        }

        $template = $verdictConfig['overall']['templates']['balanced_trade_off'] ??
            "{phone1} excels at {categories1}, while {phone2} is better for {categories2}";

        return str_replace(
            ['{phone1}', '{categories1}', '{phone2}', '{categories2}'],
            [
                $phone1,
                $this->formatList(array_slice($categories1, 0, 3)),
                $phone2,
                $this->formatList(array_slice($categories2, 0, 3))
            ],
            $template
        );
    }

    private function determineWinner($phones, $profile)
    {
        $phoneScores = $phones->mapWithKeys(function ($phone) use ($profile) {
            return [$phone->name => $this->calculateTotalScore($phone->scores, $profile)];
        })->toArray();

        arsort($phoneScores);
        return array_key_first($phoneScores);
    }

    private function calculateTotalScore($categoryScores, $profile)
    {
        $profileConfig = config("compare_scoring.compare_profiles.$profile", []);
        $categoryWeights = $profileConfig['weights'] ?? [];

        $totalWeightedScore = 0;
        $totalActiveWeight = 0;

        foreach ($categoryScores as $category => $scoreData) {
            $categoryScore = $scoreData['score'] ?? 0;

            // Skip categories with no score
            if ($categoryScore <= 0) {
                continue;
            }

            $categoryWeight = $categoryWeights[$category] ?? 0;

            // Accumulate weighted scores directly
            $totalWeightedScore += $categoryScore * $categoryWeight;
            $totalActiveWeight += $categoryWeight;
        }

        // Normalize: divide by total active weight (already on 0-100 scale)
        return $totalActiveWeight > 0
            ? round($totalWeightedScore / $totalActiveWeight, 2)
            : 0;
    }

    /**
     * Format data for chart visualization
     */
    private function formatChartData($phones, $profile)
    {
        $categories = ['display', 'performance', 'camera', 'battery', 'build', 'features'];

        // Radar Chart Data
        $radarData = [];
        foreach ($categories as $category) {
            $dataPoint = ['category' => ucfirst($category)];

            foreach ($phones as $phone) {
                $dataPoint[$phone->brand->name . ' ' . $phone->name] = $phone->scores[$category]['score'] ?? 0;
            }

            $radarData[] = $dataPoint;
        }

        // Bar Chart Data (same structure works for grouped bars)
        $barData = $radarData;

        // Category Breakdown Data (for detailed comparison)
        $categoryBreakdown = [];
        foreach ($categories as $category) {
            $categoryBreakdown[$category] = [
                'label' => config("compare_scoring.compare_verdict.{$category}.label"),
                'phones' => $phones->map(function ($phone) use ($category) {
                    return [
                        'name' => $phone->brand->name . ' ' . $phone->name,
                        'score' => $phone->scores[$category]['score'] ?? 0,
                        'out_of' => $phone->scores[$category]['out_of'] ?? 100,
                    ];
                })->toArray(),
            ];
        }

        // Overall Score Comparison
        $overallScores = $phones->map(function ($phone) use ($profile) {
            return [
                'name' => $phone->brand->name . ' ' . $phone->name,
                'score' => $this->calculateTotalScore($phone->scores, $profile),
                'color' => $phone->primary_color
            ];
        })->toArray();

        return [
            'radar' => $radarData,
            'bar' => $barData,
            'category_breakdown' => $categoryBreakdown,
            'overall_scores' => $overallScores,
            'phone_colors' => $phones->mapWithKeys(function ($phone) {
                return [$phone->brand->name . ' ' . $phone->name => $phone->primary_color];
            })->toArray(),
        ];
    }
    public function search(Request $request)
    {
        $term = $request->query('q'); // Get search term from query parameter

        if (!$term) {
            return response()->json([
                'data' => [],
                'message' => 'No search term provided.'
            ]);
        }

        // Create a cache key based on the search term
        $cacheKey = 'phone_search_' . md5(strtolower(trim($term)));

        // Cache the results for 1 hour (3600 seconds)
        $phones = Cache::remember($cacheKey, 3600, function () use ($term) {
            return Phone::query()
                ->select(['id', 'name', 'slug', 'primary_image', 'brand_id'])
                ->with('brand:id,name')
                ->where('name', 'LIKE', "%{$term}%")
                ->orWhereHas('brand', function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%");
                })
                ->limit(10)
                ->get();
        });

        PhoneResource::$hideDetails = false;

        return response()->json([
            'data' => PhoneResource::collection($phones)
        ]);
    }

    public function getAllCompareSlugs()
    {
        // Only generate comparisons for popular or recent phones
        $phones = Phone::where('deleted', 0)
            ->orderBy('release_date', 'desc') // or 'created_at', 'desc'
            ->limit($limit ?? 100) // Limit to top N phones to keep sitemap manageable
            ->pluck('slug')
            ->toArray();

        $count = count($phones);
        $slugs = [];

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $slugs[] = $phones[$i] . '-vs-' . $phones[$j];
            }
        }
        return response()->json(["data" => $slugs]);
    }

    public function getSimilarMobiles($phoneId, $avgPrice, $ramOptions, $storageOptions, $priceRange, $competitorIds = [])
    {

        return Phone::with(['searchIndex', 'brand:id,name'])
            ->join('phone_search_indices as psi', 'phones.id', '=', 'psi.phone_id')
            ->where('phones.id', '!=', $phoneId)
            ->when(!empty($competitorIds), fn($q) => $q->whereNotIn('phones.id', $competitorIds))
            ->where(function ($query) use ($ramOptions, $storageOptions, $priceRange) {
                if (!empty($ramOptions)) {
                    $query->where(function ($q) use ($ramOptions) {
                        foreach ($ramOptions as $ram) {
                            $q->orWhereRaw('JSON_CONTAINS(psi.ram_options, ?)', [json_encode($ram)]);
                        }
                    });
                }

                if (!empty($storageOptions)) {
                    $query->where(function ($q) use ($storageOptions) {
                        foreach ($storageOptions as $storage) {
                            $q->orWhereRaw('JSON_CONTAINS(psi.storage_options, ?)', [json_encode($storage)]);
                        }
                    });
                }

                if ($priceRange) {
                    $query->where(function ($q) use ($priceRange) {
                        $q->whereBetween('psi.min_price_pkr', $priceRange)
                            ->orWhereBetween('psi.max_price_pkr', $priceRange);
                    });
                }
            })
            ->orderByRaw('POWER(((psi.min_price_pkr + psi.max_price_pkr)/2) - ?, 2) ASC', [$avgPrice])
            ->select('phones.*') // keep full Phone model for resource
            ->limit(6)
            ->get();
    }

    public function getDisplayScore(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:display,battery,performance,camera',
            'phone_id' => 'nullable|integer|exists:phones,id',
        ]);

        $category = $validated['category'];
        $excludePhoneId = $validated['phone_id'] ?? null;

        // Build query
        $query = Phone::select(
            'id',
            'name',
            'brand_id',
            'slug',
            'release_date',
            'primary_image',
            'primary_color',
            'updated_at',
            'is_popular',
        )
            ->with([
                'brand:id,name', // Include brand info
                'specifications' => function ($query) use ($category) {
                    $query->where('category', $category)
                        ->select('phone_id', 'category', 'score', 'details');
                }
            ])
            ->where('status', 'active') // Only active phones
            ->whereHas('specifications', function ($query) use ($category) {
                // Only phones that have this category score
                $query->where('category', $category);
            });

        // Exclude specific phone if provided
        if ($excludePhoneId) {
            $query->whereNot('id', $excludePhoneId);
        }

        // Get phones and sort by category score
        $phones = $query->get()->map(function ($phone) use ($category) {
            // Extract the score from specifications
            $specification = $phone->specifications->first();

            return [
                'id' => $phone->id,
                'name' => $phone->name,
                'brand' => $phone->brand->name ?? 'Unknown',
                'slug' => $phone->slug,
                'displayScore' => $specification->score ?? 0,
                'image' => $phone->primary_image,
                'color' => $phone->primary_color,
                'releaseDate' => $phone->release_date,
                'keyFeatures' => $specification->details['key_features'] ?? [],
            ];
        })
            ->sortByDesc('displayScore')
            ->values() // Re-index array
            ->toArray();

        return response()->json([
            'category' => ucfirst($category) . ' Score',
            'categoryId' => $category,
            'rankedPhones' => $phones,
            'totalPhones' => count($phones),
            'currentSort' => 'score',
        ]);
    }

    private function executeFilterQuery($filters, $perPage, $page)
    {
        // Use Query Builder for better performance
        $query = Phone::query()
            ->select(
                'phones.id',
                'phones.name',
                'phones.slug',
                'phones.release_date',
                'phones.updated_at',
                'phones.status',
                'phones.primary_image',
                'brands.id as brand_id',
                'brands.name as brand_name',
                'si.min_ram as ram',
                'si.min_storage as storage',
                'si.min_price_usd',
                'si.min_price_pkr',
                'si.ram_options',
                'si.storage_options'
            )
            ->active()
            ->join('phone_search_indices as si', 'si.phone_id', '=', 'phones.id')
            ->join('brands', 'brands.id', '=', 'phones.brand_id');


        // ============================================================
        // FILTER: BRANDS
        // ============================================================
        if (!empty($filters['brands'])) {
            $brands = array_map('strtolower', $filters['brands']);
            $query->whereIn(DB::raw('LOWER(brands.name)'), $brands);
        }

        // ============================================================
        // FILTER: PRICE RANGE
        // ============================================================


        if (!empty($filters['priceRange'])) {
            $min = $filters['priceRange'][0] ?? null;
            $max = $filters['priceRange'][1] ?? null;

            $query->where(function ($q) use ($min, $max) {
                // At least one price must be valid
                $q->where(function ($subQ) {
                    $subQ->where('si.min_price_pkr', '>', 0)
                        ->orWhere('si.max_price_pkr', '>', 0);
                });

                if (!is_null($min)) {
                    $q->where(function ($subQ) use ($min) {
                        $subQ->where('si.max_price_pkr', '>=', $min)
                            ->orWhere(function ($q2) use ($min) {
                                $q2->where('si.max_price_pkr', '<=', 0)
                                    ->where('si.min_price_pkr', '>=', $min);
                            });
                    });
                }

                if (!is_null($max)) {
                    $q->where(function ($subQ) use ($max) {
                        $subQ->where('si.min_price_pkr', '<=', $max)
                            ->orWhere(function ($q2) use ($max) {
                                $q2->where('si.min_price_pkr', '<=', 0)
                                    ->where('si.max_price_pkr', '<=', $max);
                            });
                    });
                }
            });
        }

        // ============================================================
        // FILTER: RAM
        // ============================================================
        if (!empty($filters['ram'])) {
            $ramValues = array_map('intval', $filters['ram']);

            $query->where(function ($q) use ($ramValues) {
                foreach ($ramValues as $value) {
                    $q->orWhereRaw("JSON_CONTAINS(si.ram_options, ?)", [json_encode($value)]);
                }
            });
        }

        // ============================================================
        // FILTER: STORAGE
        // ============================================================
        if (!empty($filters['storage'])) {
            $storageValues = array_map('intval', $filters['storage']);

            $query->where(function ($q) use ($storageValues) {
                foreach ($storageValues as $value) {
                    $q->orWhereRaw("JSON_CONTAINS(si.storage_options, ?)", [json_encode($value)]);
                }
            });
        }

        // ============================================================
        // FILTER: SCREEN SIZE
        // ============================================================
        if (!empty($filters['screenSize'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['screenSize'] as $range) {
                    if (preg_match('/^(\d+(?:\.\d+)?)to(\d+(?:\.\d+)?)$/', $range, $matches)) {
                        $min = floatval($matches[1]);
                        $max = floatval($matches[2]);
                        $q->orWhereBetween('si.screen_size_inches', [$min, $max]);
                    }
                }
            });
        }

        // ============================================================
        // FILTER: BATTERY CAPACITY
        // ============================================================
        if (!empty($filters['batteryCapacity'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['batteryCapacity'] as $value) {
                    $value = urldecode($value);

                    if (preg_match('/^(\d+)-(\d+)/', $value, $matches)) {
                        $q->orWhereBetween('si.battery_capacity_mah', [
                            intval($matches[1]),
                            intval($matches[2])
                        ]);
                    } elseif (preg_match('/^(\d+)\s*&?\s*Above$/i', $value, $matches)) {
                        $q->orWhere('si.battery_capacity_mah', '>=', intval($matches[1]));
                    } elseif (preg_match('/^(\d+)/', $value, $matches)) {
                        $q->orWhere('si.battery_capacity_mah', intval($matches[1]));
                    }
                }
            });
        }

        // ============================================================
        // SORTING
        // ============================================================
        $status = $filters['mobileStatus'] ?? 'new';

        switch ($status) {
            case 'price-low-to-high':
                $query->orderBy('si.min_price_pkr', 'asc');
                break;

            case 'price-high-to-low':
                $query->orderBy('si.max_price_pkr', 'desc');
                break;

            case 'upcoming':
                $query->where('phones.status', 'upcoming')
                    ->orderByDesc('phones.release_date');
                break;

            default: // 'new'
                $query->orderByDesc('phones.release_date');
                break;
        }

        // Get results
        $phones = $query->paginate($perPage, ['*'], 'page', $page);

        $phones->getCollection()->transform(function ($phone) {
            $phone->brand = (object) [
                'id' => $phone->brand_id,
                'name' => $phone->brand_name,
            ];

            $phone->searchIndex = (object) [
                'ram' => $phone->ram,
                'storage' => $phone->storage,
                'min_price_pkr' => $phone->min_price_pkr,
                'min_price_usd' => $phone->min_price_usd,
            ];

            // Unset raw joined columns to reduce payload
            unset(
                $phone->brand_id,
                $phone->brand_name,
                $phone->ram,
                $phone->storage,
                $phone->max_price_pkr
            );

            return $phone;
        });
        // echo "<pre>";
        // print_r($phones);
        // exit;
        return [
            'phones' => $phones->items(),
            'pagination' => [
                'total' => $phones->total(),
                'per_page' => $phones->perPage(),
                'current_page' => $phones->currentPage(),
                'last_page' => $phones->lastPage(),
                'from' => $phones->firstItem(),
                'to' => $phones->lastItem(),
            ],
        ];
    }
}
