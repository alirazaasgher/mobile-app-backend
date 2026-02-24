<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChipsetResource;
use App\Models\Chipset;
use Illuminate\Http\JsonResponse;
class ChipsetApiController extends Controller
{
    public function index(): JsonResponse
    {
        $baseSelect = [
            'id',
            'name',
            'brand_id',
            'primary_image',
            'slug',
            'tier',
            'updated_at',
            'announced_year',
        ];

        // Fetch all collections in parallel queries
        $latestChipset = Chipset::with('brand')->select($baseSelect)
            ->limit(12)
            ->get();


        return response()->json([
            'success' => true,
            'data' => ChipsetResource::collection($latestChipset)
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $phone = Chipset::with([
            'brand:id,name',
            'specifications',
            'mobiles' => function ($query) {
                $query->select(
                    'phones.id',
                    'phones.name',
                    'phones.slug',
                    'phones.chipset_id',
                    'phones.brand_id',
                    'phones.primary_image',
                    'phones.status',
                )->where('phones.deleted', 0)
                    ->with([
                        'brand:id,name',
                        'searchIndex:phone_id,min_ram as ram,min_storage as storage,min_price_usd,min_price_pkr',
                    ]);
            }
        ])->where('slug', $slug)
            ->firstOrFail();
        return response()->json([
            'success' => true,
            'data' => new ChipsetResource(resource: $phone),
        ]);
    }

}
