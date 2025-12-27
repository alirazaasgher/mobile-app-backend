<?php

use App\Http\Controllers\Api\PhoneApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['verify.api.signature'])->group(function () {

    // 📄 Listing endpoints - 100/min
    Route::middleware('throttle:100,1')->group(function () {
        Route::get('/homepage', [PhoneApiController::class, 'index']);
        Route::get('/brands', [PhoneApiController::class, 'brands']);
        Route::get('/phones/filters', [PhoneApiController::class, 'getFilterOptions']);
        Route::get('/getStaticFilters', [PhoneApiController::class, 'getStaticFilters']);
        Route::get('/getAllCompareSlugs', [PhoneApiController::class, 'getAllCompareSlugs']);
        Route::get('/phones/{slug}', [PhoneApiController::class, 'show'])->name('phones.show');
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


// // Admin routes (protected by auth middleware)
// Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
//     Route::post('/phones', [PhoneApiController::class, 'store']);
//     Route::put('/phones/{phone}', [PhoneApiController::class, 'update']);
//     Route::delete('/phones/{phone}', [PhoneApiController::class, 'destroy']);
// });

