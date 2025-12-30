<?php

use App\Http\Controllers\Api\PhoneApiController;
use Illuminate\Support\Facades\Route;
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return "All caches cleared!";
});
Route::middleware(['verify.api.signature'])->group(function () {

    // 📄 Listing endpoints - 100/min
    Route::middleware('throttle:100,1')->group(function () {
        Route::get('/homepage', [PhoneApiController::class, 'index']);
        Route::get('/brands', [PhoneApiController::class, 'brands']);
        Route::get('/phones/filters', [PhoneApiController::class, 'getFilterOptions']);
        Route::get('/getStaticFilters', [PhoneApiController::class, 'getStaticFilters']);
        Route::get('/getAllCompareSlugs', [PhoneApiController::class, 'getAllCompareSlugs']);
        Route::get('/phones/{slug}', [PhoneApiController::class, 'show'])->name('phones.show');
        Route::get('/phones/getSimilarMobiles', [PhoneApiController::class, 'similar_mobiles']);
        Route::get('/getPhoneBySlug', [PhoneApiController::class, 'getPhoneBySlug']);
    });

    // 🔍 Search endpoints - 20/min
    Route::middleware('throttle:20,1')->group(function () {
        Route::get('/search', [PhoneApiController::class, 'search']);
        Route::post('/phones', [PhoneApiController::class, 'phones']); // Filtering
    });

    // ⚖️ Compare endpoints - 10/min
    Route::middleware('throttle:100,1')->group(function () {
        Route::post('/phones/compare', [PhoneApiController::class, 'compare']);
    });

    // 📊 Utility - 30/min
    Route::middleware('throttle:30,1')->group(function () {
        Route::get('/count', [PhoneApiController::class, 'count']);
    });
});
Route::fallback(function (Request $request) {
    if ($request->isMethod('OPTIONS')) {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-Token, Accept')
            ->header('Access-Control-Max-Age', '3600');
    }

    return response()->json(['error' => 'Not Found'], 404);
});

// // Admin routes (protected by auth middleware)
// Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
//     Route::post('/phones', [PhoneApiController::class, 'store']);
//     Route::put('/phones/{phone}', [PhoneApiController::class, 'update']);
//     Route::delete('/phones/{phone}', [PhoneApiController::class, 'destroy']);
// });

