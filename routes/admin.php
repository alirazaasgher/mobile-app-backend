<?php
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MobileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
Route::prefix('admin')->name('admin.')->group(function () {

    // Login routes (no middleware)
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Admin-only routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('mobiles', MobileController::class);  // CRUD
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
