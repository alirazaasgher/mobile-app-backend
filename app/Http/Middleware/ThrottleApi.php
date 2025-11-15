<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class ThrottleApi extends ThrottleRequests
{
    /**
     * Resolve the number of attempts if the user is authenticated or not.
     */
    protected function resolveMaxAttempts($request, $maxAttempts)
    {
        // Different rate limits for different endpoints
        if ($request->is('api/v1/search/*')) {
            return 60; // 60 searches per minute
        }
        
        if ($request->is('api/v1/phones/*')) {
            return 120; // 120 requests per minute for phone data
        }
        
        return $maxAttempts;
    }
}