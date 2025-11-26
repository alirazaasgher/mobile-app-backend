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
        $baseQuery = Phone::active()->withListingData();
        $usedPhoneIds = [];

        /**
         * Latest Phones
         */
        $latestMobiles = (clone $baseQuery)
            ->select('id', 'name', 'slug', 'release_date', 'primary_image')
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
            ->where('release_date', '>', now())
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
                        $q->where('min_price', '>=', $min);
                    if ($max !== null)
                        $q->where('max_price', '<=', $max);
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
                'price_ranges' => $mobilesByPriceRange, // if this is an array, you can wrap it in a resource too
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
                $query->orderBy('storage')->orderBy('price');
            },
        ])->where('slug', $slug)->firstOrFail();
        $queries = DB::getQueryLog();

        // Print queries
        // dd($phone);
        // $phone = Cache::remember($cacheKey, 600, function () use ($id) {

        // });
        return response()->json([
            'success' => true,
            'data' => new PhoneResource(resource: $phone)
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
            'filters.price_min' => 'nullable|numeric|min:0',
            'filters.price_max' => 'nullable|numeric|min:0',
            'sort' => ['nullable', Rule::in(['price_low_high', 'price_high_low', 'popular', 'newest'])],
        ]);

        $filters = $validated['filters'] ?? [];
        $sort = $validated['sort'] ?? 'newest';
        $perPage = $validated['per_page'] ?? 20;
        $query = Phone::active()->with(['brand:id,name', 'searchIndex']);
        $page = $validated['page'] ?? 1;
        // Generate a unique cache key
        $cacheKey = 'phones:' . md5(json_encode([
            'filters' => $filters,
            'sort' => $sort,
            'page' => $page,
            'per_page' => $perPage
        ]));

        $phones = Cache::remember($cacheKey, 300, function () use ($filters, $sort, $perPage, $page) {

            $query = Phone::active()->with(['brand:id,name', 'searchIndex']);

            // Brands
            if (!empty($filters['brands'])) {
                $brands = array_map('strtolower', $filters['brands']);
                $query->whereHas('brand', fn($q) => $q->whereIn(DB::raw('LOWER(name)'), $brands));
            }

            // Price Range
            if (!empty($filters['priceRange'])) {
                $query->whereHas('searchIndex', function ($q) use ($filters) {
                    if (isset($filters['priceRange']['min'])) {
                        $q->where('min_price', '>=', $filters['priceRange']['min']);
                    }
                    if (isset($filters['priceRange']['max'])) {
                        $q->where('max_price', '<=', $filters['priceRange']['max']);
                    }
                });
            }

            // RAM
            if (!empty($filters['ram'])) {
                $ramValues = array_map('intval', $filters['ram']);
                $query->whereHas('searchIndex', fn($q) => $q->whereRaw("JSON_OVERLAPS(ram_options, ?)", [json_encode($ramValues)]));
            }

            // Storage
            if (!empty($filters['storage'])) {
                $storageValues = array_map('intval', $filters['storage']);
                $query->whereHas('searchIndex', fn($q) => $q->whereRaw("JSON_OVERLAPS(storage_options, ?)", [json_encode($storageValues)]));
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
                $query->select('id', 'name', 'slug', 'release_date', 'primary_image')
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
