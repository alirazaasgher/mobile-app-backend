<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Add common headers for API responses
        if ($response instanceof JsonResponse) {
            $response->header('Content-Type', 'application/json');
            $response->header('Cache-Control', 'public, max-age=300'); // 5 minutes cache
            $response->header('X-API-Version', 'v1');
        }
        
        return $response;
    }
}