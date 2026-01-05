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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\FuncCall;

class PhoneApiController extends Controller
{
    protected PhoneSearchService $phoneSearchService;

    public function __construct(PhoneSearchService $phoneSearchService)
    {
        $this->phoneSearchService = $phoneSearchService;
    }

    /**
     * Get filtered phones with pagination - Main listing API
     */
    public function index(Request $request): JsonResponse
    {
        // Single base query configuration
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

        $usedPhoneIds = [];

        // Latest Mobiles
        $latestMobiles = Phone::select($baseSelect)
            ->active()
            ->where('is_popular', 0)
            ->whereNotNull('release_date')
            ->orderByDesc('release_date')
            ->limit(12)
            ->get();

        $usedPhoneIds = $latestMobiles->pluck('id')->all();

        // Upcoming Mobiles
        $upcomingMobiles = Phone::select($baseSelect)
            ->active()
            ->whereIn('status', ['rumored', 'upcoming'])
            ->whereNotIn('id', $usedPhoneIds)
            ->orderBy('release_date', 'asc')
            ->limit(12)
            ->get();

        $usedPhoneIds = array_merge($usedPhoneIds, $upcomingMobiles->pluck('id')->all());

        // Popular Mobiles
        $popularMobiles = Phone::select($baseSelect)
            ->active()
            ->where('is_popular', 1)
            ->whereNotIn('id', $usedPhoneIds)
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        $usedPhoneIds = array_merge($usedPhoneIds, $popularMobiles->pluck('id')->all());

        // Load all brands in ONE query
        $allBrandIds = collect()
            ->merge($latestMobiles)
            ->merge($upcomingMobiles)
            ->merge($popularMobiles)
            ->pluck('brand_id')
            ->unique()
            ->all();

        $brands = DB::table('brands')
            ->select('id', 'name')
            ->whereIn('id', $allBrandIds)
            ->get()
            ->keyBy('id');

        // Load search indices (already optimized)
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
            ->whereIn('phone_id', array_unique($usedPhoneIds))
            ->get()
            ->keyBy('phone_id');

        // Attach relationships
        $attachData = function ($collection) use ($searchIndexes, $brands) {
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
                return $phone;
            });
        };

        $latestMobiles = $attachData($latestMobiles);
        $upcomingMobiles = $attachData($upcomingMobiles);
        $popularMobiles = $attachData($popularMobiles);

        // Price ranges: Use UNION ALL instead of loop (MUCH faster)
        $priceRanges = [
            'under_10000' => [0, 9999],
            '10000_20000' => [10000, 19999],
            '20000_30000' => [20000, 29999],
            '30000_40000' => [30000, 39999],
            '40000_50000' => [40000, 49999],
            '50000_60000' => [50000, 59999],
            'above_60000' => [60000, null],
        ];

        // Build UNION query
        $unionQuery = null;
        foreach ($priceRanges as $key => [$min, $max]) {
            $query = DB::table('phones')
                ->select(
                    'phones.id',
                    'phones.name',
                    'phones.slug',
                    'phones.primary_image',
                    'phone_search_indices.min_ram as ram',
                    'phone_search_indices.min_storage as storage',
                    'phone_search_indices.min_price_usd',
                    'phone_search_indices.storage_type',
                    'phone_search_indices.ram_type',
                    'phone_search_indices.min_price_pkr as min_price',
                    DB::raw("'$key' as price_range")
                )
                ->join('phone_search_indices', 'phone_search_indices.phone_id', '=', 'phones.id')
                ->where('phones.status', 1)
                ->where('phone_search_indices.min_price_pkr', '>', 0)
                ->when(!empty($usedPhoneIds), fn($q) => $q->whereNotIn('phones.id', $usedPhoneIds))
                ->when(
                    $max === null,
                    fn($q) => $q->where('phone_search_indices.min_price_pkr', '>=', $min),
                    fn($q) => $q->whereBetween('phone_search_indices.min_price_pkr', [$min, $max])
                )
                ->orderByDesc('phones.updated_at')
                ->limit(12);

            $unionQuery = $unionQuery ? $unionQuery->unionAll($query) : $query;
        }

        $priceRangeResults = $unionQuery->get();

        // Group by price range
        $mobilesByPriceRange = [];
        foreach ($priceRanges as $key => $range) {
            $mobilesByPriceRange[$key] = $priceRangeResults
                ->where('price_range', $key)
                ->map(fn($row) => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'slug' => $row->slug,
                    'primary_image' => $row->primary_image,
                    'searchIndex' => [
                        'ram' => $row->ram,
                        'storage' => $row->storage,
                        'min_price_usd' => $row->min_price_usd,
                        'storage_type' => $row->storage_type,
                        'ram_type' => $row->ram_type,
                        'min_price' => $row->min_price,
                    ]
                ])
                ->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'latest_mobiles' => PhoneResource::collection($latestMobiles),
                'upcoming_mobiles' => PhoneResource::collection($upcomingMobiles),
                'popular_mobiles' => PhoneResource::collection($popularMobiles),
                'price_ranges' => collect($mobilesByPriceRange)->map(function ($phones) {
                    return PhoneResource::collection($phones);
                }),
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
        ]);

        $slugs = $validated['slugs'];
        $phones = Phone::select('id', 'name', 'brand_id', 'slug', 'release_date', 'primary_image', 'updated_at', 'is_popular', 'status')
            ->whereIn('slug', $slugs)
            ->get();
        $allBrandIds = $phones->pluck('brand_id')->unique()->toArray();
        $usedPhoneIds = $phones->pluck('id')->unique()->toArray();
        $brands = DB::table('brands')
            ->select('id', 'name')
            ->whereIn('id', $allBrandIds)
            ->get()
            ->keyBy('id');

        // Load search indices (already optimized)
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
            ->whereIn('phone_id', array_unique($usedPhoneIds))
            ->get()
            ->keyBy('phone_id');

        // Attach relationships
        $attachData = function ($collection) use ($searchIndexes, $brands) {
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
                return $phone;
            });
        };
        $phones = $attachData($phones);
        PhoneResource::$hideDetails = true;
        return response()->json([
            'success' => true,
            'data' => $phones->map(fn($phone) => new PhoneResource($phone, true)),
            // 'similarMobiles' => !empty($similarMobiles) ? $similarMobiles->map(fn($phone) => new PhoneResource($phone, false)) : [], // omit searchIndex

        ]);
        // if ($phones->count() === 1) {
        //     $phone = $phones->first(); // get the single phone
        //     $ramOptions = $phone->searchIndex->ram_options
        //         ? json_decode($phone->searchIndex->ram_options, true)
        //         : [];

        //     $storageOptions = $phone->searchIndex->storage_options
        //         ? json_decode($phone->searchIndex->storage_options, true)
        //         : [];

        //     $minPrice = $phone->searchIndex->min_price_pkr ?? 0;
        //     $maxPrice = $phone->searchIndex->max_price_pkr ?? 0;
        //     $priceRange = [$minPrice * 0.85, $maxPrice * 1.15];
        //     $avgPrice = ($phone->searchIndex->min_price_pkr + $phone->searchIndex->max_price_pkr) / 2;
        //     $similarMobiles = $this->getSimilarMobiles($phone->id, $avgPrice, $ramOptions, $storageOptions, $priceRange);
        // }


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

    private function executeFilterQuery($filters, $perPage, $page)
    {
        // Use Query Builder for better performance
        $query = Phone::query()
            ->select(
                'phones.id',
                'phones.name',
                'phones.slug',
                'phones.release_date',
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
