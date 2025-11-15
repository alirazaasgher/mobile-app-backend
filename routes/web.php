
<?php

use App\Http\Controllers\Admin\MobileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
Route::group(['prefix' => 'admin'], function () {
     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('mobiles', MobileController::class);
});