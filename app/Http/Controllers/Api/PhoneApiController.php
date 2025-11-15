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
        // Latest mobiles 
        $latestMobiles = Phone::query()
            ->select('id', 'name', 'slug', 'release_date', 'primary_image', 'brand_id') // include brand_id for eager load
            ->with([
                'brand:id,name', // fetch only id & name from brand
                'searchIndex' // only necessary columns
            ])
            ->active() // your scope
            ->whereNotNull('release_date')
            ->whereNotIn('id', $usedPhoneIds)
            ->orderByDesc('release_date')
            ->take(10)
            ->get();

        $usedPhoneIds = array_merge($usedPhoneIds, $latestMobiles->pluck('id')->toArray());
        // Upcoming mobiles
        $upcomingMobiles = Phone::active()
            ->withListingData()
            ->where('release_date', '>', now())
            ->orderBy('release_date', 'asc')
            ->whereNotIn('id', $usedPhoneIds)
            ->take(10)->get();
        $usedPhoneIds = array_merge(
            $usedPhoneIds,
            $upcomingMobiles->pluck('id')->toArray()
        );
        // Popular mobiles 
        $popularMobiles = Phone::active()
            ->withListingData()
            ->orderBy('popularity_score', 'desc')
            ->whereNotIn('id', $usedPhoneIds)->take(10)
            ->get();
        $usedPhoneIds = array_merge($usedPhoneIds, $popularMobiles->pluck('id')->toArray());
        // Price Ranges 
        $priceRanges = ['under_10000' => [0, 10000], '10000_to_20000' => [10000, 20000], '20000_to_30000' => [20000, 30000], 'above_30000' => [30000, null],];
        $mobilesByPriceRange = [];
        foreach ($priceRanges as $key => [$min, $max]) {
            $query = Phone::active()
                ->withListingData()
                ->whereHas('searchIndex', function ($q) use ($min, $max) {
                    if (!is_null($min)) {
                        $q->where('min_price', '>=', $min);
                    }
                    if (!is_null($max)) {
                        $q->where('max_price', '<=', $max);
                    }
                })->whereNotIn('id', $usedPhoneIds)->take(10);
            $mobilesByPriceRange[$key] = $query->get();
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
        //dd($phone);
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
            'brands' => 'array',
            'brands.*' => 'string',
            'price_min' => 'numeric|min:0',
            'price_max' => 'numeric|min:0',
            'ram' => 'array',
            'ram.*' => 'integer',
            'storage' => 'array',
            'storage.*' => 'integer',
            'has_5g' => 'boolean',
            'os' => 'array',
            'os.*' => 'string',
            'search' => 'string|max:255',
            'sort_by' => Rule::in(['name', 'price', 'brands', 'created_at']),
            'sort_order' => Rule::in(['asc', 'desc']),
            'per_page' => 'integer|min:1|max:50',
        ]);
        $query = Phone::active()->with(['brand:id,name', 'searchIndex']);

        // Brands
        if (!empty($validated['brands'])) {
            $brands = array_map('strtolower', $validated['brands']);
            $query->whereHas('brand', fn($q) => $q->whereIn(\DB::raw('LOWER(name)'), $brands));
        }

        // Combined searchIndex filters
        $query->whereHas('searchIndex', function ($q) use ($validated) {

            // Price
            if (isset($validated['price_min'])) {
                $q->where('min_price', '>=', $validated['price_min']);
            }
            if (isset($validated['price_max'])) {
                $q->where('max_price', '<=', $validated['price_max']);
            }

            // RAM
            if (!empty($validated['ram'])) {
                $ramValues = array_map('intval', $validated['ram']);
                $q->whereRaw("JSON_OVERLAPS(ram_options, ?)", [json_encode($ramValues)]);
            }

            // Storage
            if (!empty($validated['storage'])) {
                $q->where(function ($q2) use ($validated) {
                    foreach ($validated['storage'] as $storage) {
                        $q2->orWhereJsonContains('storage_options', $storage);
                    }
                });
            }

            // 5G
            if (isset($validated['has_5g'])) {
                $q->where('has_5g', $validated['has_5g']);
            }

            // OS
            if (!empty($validated['os'])) {
                $q->whereIn('os', $validated['os']);
            }

            // Full-text search
            if (!empty($validated['search'])) {
                $q->whereRaw(
                    "MATCH(brand, model, name, search_content) AGAINST (? IN NATURAL LANGUAGE MODE)",
                    [$validated['search']]
                );
            }
        });



        // ✅ Case 1: No filters → return mixed mobiles
        if (empty(array_filter($validated))) {
            $phones = Phone::select('id', 'name', 'slug', 'release_date', 'primary_image')->active()
                ->with('searchIndex')
                ->inRandomOrder() // shuffle brands
                ->take(20) // fetch e.g. 30 mixed phones
                ->get();

            return response()->json([
                'success' => true,
                'data' => PhoneResource::collection($phones),
            ]);
        }
        //dd($query->toSql(), $query->getBindings());

        // ✅ Case 2: Filters applied → return paginated results
        $phones = $query
            ->orderBy($validated['sort_by'] ?? 'created_at', $validated['sort_order'] ?? 'desc')
            ->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'success' => true,
            'data' => PhoneResource::collection($phones->items()),
            'pagination' => [
                'current_page' => $phones->currentPage(),
                'last_page' => $phones->lastPage(),
                'per_page' => $phones->perPage(),
                'total' => $phones->total(),
            ],
            'filters_applied' => array_filter($validated),
        ]);
    }

    public function getPhoneBySlug(Request $request)
    {
        $slugs = Phone::select('slug')->where('slug', request('slug'))->first();
        return response()->json(["data" => $slugs]);
    }
}
