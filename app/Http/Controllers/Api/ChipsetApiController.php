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

}
