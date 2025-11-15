<?php
namespace App\Providers;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
class RouteServiceProvider extends ServiceProvider
{
public function boot()
{
    $this->configureRateLimiting();
}

protected function configureRateLimiting()
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
    
    RateLimiter::for('search', function (Request $request) {
        return Limit::perMinute(30)->by($request->ip());
    });
}

}