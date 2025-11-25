<?php
use App\Http\Controllers\Api\PhoneApiController;
use App\Http\Controllers\Api\BrandApiController;
use Illuminate\Support\Facades\Route;
Route::get('/homepage', [PhoneApiController::class, 'index']);
Route::get('/getPhoneBySlug', [PhoneApiController::class, 'getPhoneBySlug']);
Route::get('/getStaticFilters', [PhoneApiController::class, 'getStaticFilters']);
Route::post('/phones', [PhoneApiController::class, 'phones']);
Route::get('/phones/filters', [PhoneApiController::class, 'getFilterOptions']);
Route::get('/phones/{slug}', [PhoneApiController::class, 'show'])->name('phones.show');

// Admin routes (protected by auth middleware)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('/phones', [PhoneApiController::class, 'store']);
    Route::put('/phones/{phone}', [PhoneApiController::class, 'update']);
    Route::delete('/phones/{phone}', [PhoneApiController::class, 'destroy']);
});
