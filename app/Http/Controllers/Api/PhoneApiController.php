<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Phone;
use App\Services\PhoneSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\PhoneResource;
use App\Http\Resources\PhoneListResource;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
        $usedPhoneIds = [];
        $baseQuery = Phone::select('id', 'name', 'slug', 'release_date', 'primary_image', 'status')->with(['searchIndex'])->active();
        $usedPhoneIds = [];

        /**
         * Latest Phones
         */
        $latestMobiles = (clone $baseQuery)
            ->where('is_popular', 0)
            ->whereNotNull('release_date')
            ->orderByDesc('release_date')
            ->take(10)
            ->get();
        $usedPhoneIds = $latestMobiles->pluck('id')->toArray();

        /**
         * Upcoming Phones
         */
        $upcomingMobiles = (clone $baseQuery)
            ->whereIn('status', ['rumored', 'upcoming'])
            ->whereNotIn('id', $usedPhoneIds)
            ->orderBy('release_date', 'asc')
            ->take(10)
            ->get();

        $usedPhoneIds = array_merge($usedPhoneIds, $upcomingMobiles->pluck('id')->toArray());

        /**
         * Popular Phones
         */
        $popularMobiles = (clone $baseQuery)
            ->where('is_popular', 1) // only popular
            ->whereNotIn('id', $usedPhoneIds)
            // ->orderByDesc('popularity_score')
            ->take(10)
            ->get();

        $usedPhoneIds = array_merge($usedPhoneIds, $popularMobiles->pluck('id')->toArray());


        /**
         * Price Ranges
         */
        $priceRanges = [
            'under_10000' => [0, 10000],
            '10000_to_20000' => [10000, 20000],
            '20000_to_30000' => [20000, 30000],
            'above_30000' => [30000, null],
        ];

        $mobilesByPriceRange = [];

        foreach ($priceRanges as $key => [$min, $max]) {
            $mobilesByPriceRange[$key] = (clone $baseQuery)
                ->where('is_popular', 0)
                ->whereNotIn('id', $usedPhoneIds)
                ->whereHas('searchIndex', function ($q) use ($min, $max) {
                    if ($min !== null)
                        $q->where('min_price_pkr', '>=', $min);
                    if ($max !== null)
                        $q->where('max_price_pkr', '<=', $max);
                })
                ->take(10)
                ->get();

            $usedPhoneIds = array_merge($usedPhoneIds, $mobilesByPriceRange[$key]->pluck('id')->toArray());
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
                $query->with(['ram_type:id,name', 'storage_type:id,name'])
                    ->orderBy('storage')
                    ->orderBy('pkr_price');
            },
            // Competitors only need minimal info: id, name, slug, primary_image, brand_id
            'competitors' => function ($q) {
                $q->select('phones.id', 'phones.name', 'phones.slug', 'phones.primary_image') // minimal fields
                    ->with('searchIndex'); // eager load searchIndex
            }
        ])
            ->where('slug', $slug)
            ->firstOrFail();
        $ramOptions = $phone->searchIndex->ramOptions ?? [];
        $storageOptions = $phone->searchIndex->storageOptions ?? [];
        echo "<pre>";
        print_r($phone->searchIndex);
        echo "----";
        print_r($storageOptions);
        exit;
        $minPrice = $phone->searchIndex->min_price ?? 0;
        $maxPrice = $phone->searchIndex->max_price ?? 0;
        $priceRange = [$minPrice * 0.85, $maxPrice * 1.15];

        $similarMobiles = Phone::with('searchIndex') // eager load to prevent N+1
            ->where('id', '!=', $phone->id)
            ->whereHas('searchIndex', function ($query) use ($ramOptions, $storageOptions, $priceRange) {
                $query->when(!empty($ramOptions), fn($q) => $q->whereIn('ram', $ramOptions))
                    ->when(!empty($storageOptions), fn($q) => $q->whereIn('storage', $storageOptions))
                    ->where(function ($q) use ($priceRange) {
                        $q->whereBetween('min_price_pkr', $priceRange)
                            ->orWhereBetween('max_price_pkr', $priceRange);
                    });
            })
            ->limit(6)
            ->get(['id', 'name', 'slug', 'primary_image', 'brand_id']);

        dd($similarMobiles->toSql());
        // ->get(['id', 'name', 'slug', 'primary_image', 'brand_id']);
        return response()->json([
            'success' => true,
            'data' => new PhoneResource(resource: $phone),
            'similarMobiles' => $similarMobiles
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
            'filters.priceRange' => 'nullable|array|max:2',
            'filters.priceRange.*' => 'nullable|numeric|min:0',
            'filters.screenSizes' => 'nullable|array',
            'filters.batteryCapacity' => 'nullable|array',
            'sort' => ['nullable', Rule::in(['price_low_high', 'price_high_low', 'popular', 'newest'])],
        ]);

        $filters = $validated['filters'] ?? [];
        $sort = $validated['sort'] ?? 'newest';
        $perPage = $validated['per_page'] ?? 20;
        // $query = Phone::active()->with(['brand:id,name', 'searchIndex']);
        $page = $validated['page'] ?? 1;
        // Generate a unique cache key
        $cacheKey = 'phones:' . md5(json_encode([
            'filters' => $filters,
            'sort' => $sort,
            'page' => $page,
            'per_page' => $perPage
        ]));

        $phones = Cache::remember($cacheKey, 50, function () use ($filters, $sort, $perPage, $page) {
            $query = Phone::active()->with(['brand:id,name', 'searchIndex']);
            // Brands
            if (!empty($filters['brands'])) {
                $brands = array_map('strtolower', $filters['brands']);
                $query->whereHas('brand', fn($q) => $q->whereIn(DB::raw('LOWER(name)'), $brands));
            }
            // Price Range
            if (!empty($filters['priceRange'])) {
                $query->whereHas('searchIndex', function ($q) use ($filters) {
                    $min = $filters['priceRange'][0] ?? null;
                    $max = $filters['priceRange'][1] ?? null;

                    if (!is_null($min)) {
                        $q->where('min_price', '>=', $min);
                    }
                    if (!is_null($max)) {
                        $q->where('max_price', '<=', $max);
                    }
                });
            }
            // RAM
            if (!empty($filters['ram'])) {
                $ramValues = array_map('intval', $filters['ram']);

                $query->whereHas('searchIndex', function ($q) use ($ramValues) {
                    $q->where(function ($subQ) use ($ramValues) {
                        foreach ($ramValues as $value) {
                            $subQ->orWhereRaw("JSON_CONTAINS(ram_options, ?)", [json_encode($value)]);
                        }
                    });
                });
            }

            // Storage
            if (!empty($filters['storage'])) {
                $storageValues = array_map('intval', $filters['storage']);

                $query->whereHas('searchIndex', function ($q) use ($storageValues) {
                    $q->where(function ($subQ) use ($storageValues) {
                        foreach ($storageValues as $value) {
                            $subQ->orWhereRaw("JSON_CONTAINS(storage_options, ?)", [json_encode($value)]);
                        }
                    });
                });
            }

            // Features
            if (!empty($filters['screenSizes'])) {
                $query->whereHas('searchIndex', function ($q) use ($filters) {
                    $q->whereColumn('phones.id', 'phone_search_indices.phone_id'); // ensures the join condition
                    $q->where(function ($q2) use ($filters) {
                        foreach ($filters['screenSizes'] as $range) {
                            if (preg_match('/^(\d+(\.\d+)?)to(\d+(\.\d+)?)$/', $range, $matches)) {
                                $min = floatval($matches[1]);
                                $max = floatval($matches[3]);
                                $q2->orWhereBetween('screen_size_inches', [$min, $max]);
                            }
                        }
                    });
                });
            }

            if (!empty($filters['batteryCapacity'])) {
                $query->whereHas('searchIndex', function ($q) use ($filters) {
                    $q->where(function ($q2) use ($filters) {
                        foreach ($filters['batteryCapacity'] as $value) {
                            $value = urldecode($value); // decode %26 to &

                            // Range: "5000-5999mAh"
                            if (preg_match('/^(\d+)-(\d+)/', $value, $matches)) {
                                $q2->orWhereBetween('battery_capacity_mah', [
                                    intval($matches[1]),
                                    intval($matches[2])
                                ]);
                                continue;
                            }

                            // "6000 & Above" or "6000&Above"
                            if (preg_match('/^(\d+)\s*&?\s*Above$/i', $value, $matches)) {
                                $q2->orWhere('battery_capacity_mah', '>=', intval($matches[1]));
                                continue;
                            }

                            // Exact value: "3000mAh"
                            if (preg_match('/^(\d+)/', $value, $matches)) {
                                $q2->orWhere('battery_capacity_mah', intval($matches[1]));
                            }
                        }
                    });
                });
            }



            // Features
            if (!empty($filters['features'])) {
                foreach ($filters['features'] as $feature) {
                    if ($feature === '5g') {
                        $query->whereHas('searchIndex', fn($q) => $q->where('has_5g', 1));
                    }
                }
            }

            // If no filters, return mixed phones
            if (empty(array_filter($filters))) {
                $query->select('id', 'name', 'slug', 'release_date', 'primary_image', 'status')
                    ->orderBy('release_date', 'desc')
                    ->inRandomOrder();
            }

            // Sorting
            switch ($sort) {
                case 'price_low_high':
                    $query->join('phone_search_index as searchIndex', 'phones.id', '=', 'searchIndex.phone_id')
                        ->orderBy('searchIndex.min_price', 'asc');
                    break;
                case 'price_high_low':
                    $query->join('phone_search_index as searchIndex', 'phones.id', '=', 'searchIndex.phone_id')
                        ->orderBy('searchIndex.max_price', 'desc');
                    break;
                case 'popular':
                    $query->orderByDesc('is_popular')->orderByDesc('release_date');
                    break;
                case 'newest':
                default:
                    $query->orderByDesc('release_date');
                    break;
            }
            //dd($query->toSql(),$query->getBindings());

            // Paginate
            return $query->paginate($perPage, ['phones.*'], 'page', $page);
        });

        return response()->json([
            'success' => true,
            'data' => PhoneResource::collection($phones),
            'pagination' => [
                'current_page' => $phones->currentPage(),
                'from' => $phones->firstItem(),
                'last_page' => $phones->lastPage(),
                'per_page' => $phones->perPage(),
                'to' => $phones->lastItem(),
                'total' => $phones->total(),
                'first_page_url' => $phones->url(1),
                'last_page_url' => $phones->url($phones->lastPage()),
                'next_page_url' => $phones->nextPageUrl(),
                'prev_page_url' => $phones->previousPageUrl(),
                'path' => $phones->path(),
            ],
            'filters_applied' => array_filter($validated),
        ]);
    }

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
}
