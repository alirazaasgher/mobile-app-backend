<?php
use App\Http\Controllers\Api\PhoneApiController;
use Illuminate\Support\Facades\Route;
Route::get('/homepage', [PhoneApiController::class, 'index']);
Route::get('/getPhoneBySlug', [PhoneApiController::class, 'getPhoneBySlug']);
Route::get('/getStaticFilters', [PhoneApiController::class, 'getStaticFilters']);
Route::post('/phones', [PhoneApiController::class, 'phones']);
Route::get('/phones/filters', [PhoneApiController::class, 'getFilterOptions']);
Route::get('/phones/{slug}', [PhoneApiController::class, 'show'])->name('phones.show');
Route::get('/brands', [PhoneApiController::class, 'brands']);
Route::get('/count', [PhoneApiController::class, 'count']);
Route::post('/phones/compare', [PhoneApiController::class, 'compare']);
Route::get('/getAllCompareSlugs', [PhoneApiController::class, 'getAllCompareSlugs']);
Route::get('/search', [PhoneApiController::class, 'search']);

// // Admin routes (protected by auth middleware)
// Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
//     Route::post('/phones', [PhoneApiController::class, 'store']);
//     Route::put('/phones/{phone}', [PhoneApiController::class, 'update']);
//     Route::delete('/phones/{phone}', [PhoneApiController::class, 'destroy']);
// });

