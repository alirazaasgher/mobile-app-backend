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
        $cacheTTL = 43200;
        $usedPhoneIds = [];
        $baseQuery = Phone::select('id','name', 'slug', 'release_date', 'primary_image', 'status', 'updated_at')
            ->with(['searchIndex','brand:id,name'])
            ->active();

        // Cache latest mobiles
        $latestMobiles = Cache::tags(['phones', 'latest'])->remember('latest_mobiles', $cacheTTL, function () use ($baseQuery) {
            return (clone $baseQuery)
                ->where('is_popular', 0)
                ->whereNotNull('release_date')
                ->orderByDesc('release_date')
                ->take(12)
                ->get();
        });
        $usedPhoneIds = $latestMobiles->pluck('id')->toArray();

        // Cache upcoming mobiles
        $upcomingMobiles = Cache::tags(['phones', 'upcoming'])->remember('upcoming_mobiles', $cacheTTL, function () use ($baseQuery, $usedPhoneIds) {
            return (clone $baseQuery)
                ->whereIn('status', ['rumored', 'upcoming'])
                ->whereNotIn('id', $usedPhoneIds)
                ->orderBy('release_date', 'asc')
                ->take(12)
                ->get();
        });
        $usedPhoneIds = array_merge($usedPhoneIds, $upcomingMobiles->pluck('id')->toArray());

        // Cache popular mobiles
        $popularMobiles = Cache::tags(['phones', 'popular'])->remember('popular_mobiles', $cacheTTL, function () use ($baseQuery, $usedPhoneIds) {
            return (clone $baseQuery)
                ->where('is_popular', 1)
                ->whereNotIn('id', $usedPhoneIds)
                ->take(12)
                ->get();
        });
        $usedPhoneIds = array_merge($usedPhoneIds, $popularMobiles->pluck('id')->toArray());

        // Cache price ranges
        $priceRanges = [
            'under_10000' => [0, 9999],
            '10000_20000' => [10000, 19999],
            '20000_30000' => [20000, 29999],
            '30000_40000' => [30000, 39999],
            '40000_50000' => [40000, 49999],
            '50000_60000' => [50000, 59999],
            'above_60000' => [60000, null],
        ];

        $mobilesByPriceRange = [];
        foreach ($priceRanges as $key => [$min, $max]) {
            $mobilesByPriceRange[$key] = Cache::tags(['phones', 'price-range', $key])->remember("price_range_{$key}", $cacheTTL, function () use ($baseQuery, $usedPhoneIds, $min, $max) {
                return (clone $baseQuery)
                    ->when(!empty($usedPhoneIds), function ($q) use ($usedPhoneIds) {
                        $q->whereNotIn('id', $usedPhoneIds);
                    })
                    ->whereHas('searchIndex', function ($q) use ($min, $max) {
                        $q->where('min_price_pkr', '>', 0);
                        if (is_null($max)) {
                            $q->where('min_price_pkr', '>=', $min);
                        } else {
                            $q->where('min_price_pkr', '>=', $min)
                                ->where('min_price_pkr', '<', $max);
                        }
                    })
                    ->latest('updated_at')
                    ->limit(12)
                    ->get();
            });
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
                $q->select('phones.id', 'phones.name', 'phones.slug', 'phones.primary_image') // minimal fields
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

        $similarMobiles = Phone::with('searchIndex')
            ->join('phone_search_indices as psi', 'phones.id', '=', 'psi.phone_id')
            ->where('phones.id', '!=', $phone->id)
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
            'per_page' => 'nullable|numeric|min:0',
            'page' => 'nullable|numeric|min:0',
        ]);

        $filters = $validated['filters'] ?? [];
        $perPage = $validated['per_page'] ?? 20;
        // $query = Phone::active()->with(['brand:id,name', 'searchIndex']);
        $page = $validated['page'] ?? 1;
        // Generate a unique cache key
        $cacheKey = 'phones:' . md5(json_encode([
            'filters' => $filters,
            'page' => $page,
            'per_page' => $perPage
        ]));

        $phones = Cache::remember($cacheKey, 1, function () use ($filters, $perPage, $page) {

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
                        $q->where(function ($query) use ($min) {
                            $query->where('max_price_pkr', '>=', $min)
                                ->orWhere(function ($q) use ($min) {
                                    $q->where('max_price_pkr', '<=', 0)
                                        ->where('min_price_pkr', '>=', $min);
                                });
                        })
                            ->where(function ($query) {
                                // Ensure at least one price is valid
                                $query->where('max_price_pkr', '>', 0)
                                    ->orWhere('min_price_pkr', '>', 0);
                            });
                    }

                    if (!is_null($max)) {
                        $q->where(function ($query) use ($max) {
                            $query->where('min_price_pkr', '<=', $max)
                                ->orWhere(function ($q) use ($max) {
                                    $q->where('min_price_pkr', '<=', 0)
                                        ->where('max_price_pkr', '<=', $max);
                                });
                        })
                            ->where(function ($query) {
                                // Ensure at least one price is valid
                                $query->where('min_price_pkr', '>', 0)
                                    ->orWhere('max_price_pkr', '>', 0);
                            });
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
            if (!empty($filters['screenSize'])) {
                $query->whereHas('searchIndex', function ($q) use ($filters) {
                    $q->whereColumn('phones.id', 'phone_search_indices.phone_id'); // ensures the join condition
                    $q->where(function ($q2) use ($filters) {
                        foreach ($filters['screenSize'] as $range) {
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

            if (!empty($filters['mobileStatus'])) {

                $status = $filters['mobileStatus'];

                // Price: Low → High
                if ($status === 'price-low-to-high') {
                    $query->whereHas('searchIndex', function ($q) {
                        $q->orderBy('min_price_pkr', 'asc');
                    });
                }

                // Price: High → Low
                elseif ($status === 'price-high-to-low') {
                    $query->whereHas('searchIndex', function ($q) {
                        $q->orderBy('max_price_pkr', 'desc');
                    });
                }

                // Upcoming phones
                elseif ($status === 'upcoming') {
                    $query->where('status', 'upcoming')
                        ->orderByDesc('release_date');
                }

                // New / Default
                else {
                    $query->orderByDesc('release_date');
                }
            }


            // If no filters, return mixed phones
            if (empty(array_filter($filters))) {
                $query->select('id', 'name', 'slug', 'release_date', 'primary_image', 'status', 'updated_at')
                    ->orderBy('release_date', 'desc')
                    ->inRandomOrder();
            }

            // Paginate
            return $query->paginate($perPage, ['phones.*'], 'page', $page);
            dd($query->toSql(), $query->getBindings());
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

        // Fetch phones with only needed relationships
        $phones = Phone::with([
            'brand:id,name',           // only id and name
            'searchIndex',
            'specifications',
            'competitors',        // include compare_specs in resource
        ])
            ->whereIn('slug', $slugs)
            ->get();
        PhoneResource::$hideDetails = true;
        return response()->json([
            'success' => true,
            'data' => PhoneResource::collection($phones),

        ]);
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
}
