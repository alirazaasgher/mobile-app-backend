<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MobileApiController extends Controller
{
    // GET /api/phones - List phones with dynamic filters
    public function index(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'array',
            'brand.*' => 'string',
            'price_min' => 'numeric|min:0',
            'price_max' => 'numeric|min:0',
            'ram' => 'array',
            'ram.*' => 'integer',
            'storage' => 'array',
            'storage.*' => 'integer',
            'display_size_min' => 'numeric',
            'display_size_max' => 'numeric',
            'camera_min' => 'integer',
            'battery_min' => 'integer',
            'os' => 'array',
            'os.*' => 'string',
            'has_5g' => 'boolean',
            'has_wireless_charging' => 'boolean',
            'color' => 'array',
            'color.*' => 'string',
            'search' => 'string|max:255',
            'sort_by' => Rule::in(['name', 'price', 'brand', 'created_at']),
            'sort_order' => Rule::in(['asc', 'desc']),
            'per_page' => 'integer|min:1|max:100',
        ]);

        // Normal phones list with filters
        $phones = Phone::active()
            ->withListingData()
            ->filter($validated)
            ->orderBy($validated['sort_by'] ?? 'created_at', $validated['sort_order'] ?? 'desc')
            ->paginate($validated['per_page'] ?? 20);

        // Keep track of used IDs
        $usedPhoneIds = [];

        // Latest mobiles
        $latestMobiles = Phone::active()
            ->withListingData()
            ->whereNotNull('release_date')
            ->orderBy('release_date', 'desc')
            ->whereNotIn('id', $usedPhoneIds)
            ->take(10)
            ->get();
        $usedPhoneIds = array_merge($usedPhoneIds, $latestMobiles->pluck('id')->toArray());

        // Upcoming mobiles
        $upcomingMobiles = Phone::active()
            ->withListingData()
            ->where('release_date', '>', now())
            ->orderBy('release_date', 'asc')
            ->whereNotIn('id', $usedPhoneIds)
            ->take(10)
            ->get();
        $usedPhoneIds = array_merge($usedPhoneIds, $upcomingMobiles->pluck('id')->toArray());

        // Popular mobiles
        $popularMobiles = Phone::active()
            ->withListingData()
            ->orderBy('popularity_score', 'desc')
            ->whereNotIn('id', $usedPhoneIds)
            ->take(10)
            ->get();
        $usedPhoneIds = array_merge($usedPhoneIds, $popularMobiles->pluck('id')->toArray());

        // Price Ranges
        $priceRanges = [
            'under_10000' => [0, 10000],
            '10000_to_20000' => [10000, 20000],
            '20000_to_30000' => [20000, 30000],
            'above_30000' => [30000, null],
        ];

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
                })
                ->whereNotIn('id', $usedPhoneIds)
                ->take(10);

            $mobilesByPriceRange[$key] = $query->get();
            $usedPhoneIds = array_merge($usedPhoneIds, $mobilesByPriceRange[$key]->pluck('id')->toArray());
        }

        return response()->json([
            'success' => true,
            'data' => $phones->items(),
            'pagination' => [
                'current_page' => $phones->currentPage(),
                'last_page' => $phones->lastPage(),
                'per_page' => $phones->perPage(),
                'total' => $phones->total(),
            ],
            'filters_applied' => array_filter($validated),
            'sections' => [
                'latest_mobiles' => $latestMobiles,
                'upcoming_mobiles' => $upcomingMobiles,
                'popular_mobiles' => $popularMobiles,
                'price_ranges' => $mobilesByPriceRange,
            ]
        ]);
    }

    // GET /api/phones/{slug} - Get single phone
    public function show($slug)
    {
        $phone = Phone::active()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $phone
        ]);
    }

    // POST /api/admin/phones - Create phone (Admin only)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'details' => 'required|array',
            'details.overview' => 'required|array',
            'details.overview.name' => 'required|string|max:255',
            'details.specs' => 'required|array',
            'price' => 'nullable|numeric|min:0',
        ]);

        // Generate slug from name
        $slug = Str::slug($validated['details']['overview']['name']);

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Phone::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $phone = Phone::create([
            'name' => $validated['details']['overview']['name'],
            'slug' => $slug,
            'details' => $validated['details'],
            'price' => $validated['price'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phone created successfully',
            'data' => $phone
        ], 201);
    }

    // PUT /api/admin/phones/{id} - Update phone (Admin only)
    public function update(Request $request, Phone $phone)
    {
        $validated = $request->validate([
            'details' => 'sometimes|array',
            'details.overview.name' => 'sometimes|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        // Update slug if name changed
        if (
            isset($validated['details']['overview']['name']) &&
            $validated['details']['overview']['name'] !== $phone->name
        ) {

            $slug = Str::slug($validated['details']['overview']['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (Phone::where('slug', $slug)->where('id', '!=', $phone->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
            $validated['name'] = $validated['details']['overview']['name'];
        }

        $phone->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Phone updated successfully',
            'data' => $phone->fresh()
        ]);
    }

    // DELETE /api/admin/phones/{id} - Delete phone (Admin only)
    public function destroy(Phone $phone)
    {
        $phone->update(['is_active' => false]); // Soft delete approach

        return response()->json([
            'success' => true,
            'message' => 'Phone deleted successfully'
        ]);
    }

    // GET /api/phones/filters - Get available filter options
    public function getFilterOptions()
    {
        $brands = Phone::active()->distinct()->pluck('brand')->filter()->sort()->values();
        $ramOptions = Phone::active()->distinct()->pluck('ram_gb')->filter()->sort()->values();
        $storageOptions = Phone::active()->distinct()->pluck('storage_gb')->filter()->sort()->values();
        $osOptions = Phone::active()->distinct()->pluck('os')->filter()->sort()->values();
        $colors = Phone::active()->distinct()->pluck('color')->filter()->sort()->values();

        $priceRange = Phone::active()->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        return response()->json([
            'success' => true,
            'filters' => [
                'brands' => $brands,
                'ram_options' => $ramOptions,
                'storage_options' => $storageOptions,
                'os_options' => $osOptions,
                'colors' => $colors,
                'price_range' => [
                    'min' => $priceRange->min_price ?? 0,
                    'max' => $priceRange->max_price ?? 10000
                ]
            ]
        ]);
    }
}
