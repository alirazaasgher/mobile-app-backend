<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class VerifyApiSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $blocked_ips = ['13.52.98.180'];
        $client_ip = $_SERVER['REMOTE_ADDR'];

        if (in_array($client_ip, $blocked_ips)) {
            http_response_code(403);
            exit('Access Denied');
        }

        return $next($request);

        $clientId = $request->header('X-CLIENT-ID');
        $timestamp = $request->header('X-TIMESTAMP');
        $nonce = $request->header('X-NONCE');
        $signature = $request->header('X-SIGNATURE');
        if (!$clientId || !$timestamp || !$nonce || !$signature) {
            return response()->json([
                'error' => 'Invalid API signature'
            ], 401);
        }

        if (abs(time() - $timestamp) > 300) {
            return response()->json([
                'error' => 'Invalid API signature'
            ], 401);
        }

        if (Cache::has("nonce:$nonce")) {
            return response()->json([
                'error' => 'Invalid API signature'
            ], 401);
        }
        Cache::put("nonce:$nonce", true, 300);

        $secret = config('services.api_clients')[$clientId] ?? null;

        if (!$secret) {
            return response()->json([
                'error' => 'Invalid API signature'
            ], 401);
        }

        $body = $request->getContent(); // raw JSON
// Minify JSON if body is JSON
        $jsonDecoded = json_decode($body, true);
        if ($jsonDecoded !== null) {
            $body = json_encode($jsonDecoded, JSON_UNESCAPED_SLASHES);
        }
        $payload =
            $request->method()
            . '/' . $request->path()
            . $timestamp
            . $nonce
            . $body;

        $expected = hash_hmac('sha256', $payload, $secret);

        // dd([
        //     'PHP Method: ' . $request->method(),
        //     'PHP Timestamp: ' . $timestamp,
        //     'PHP Nonce: ' . $nonce,
        //     'PHP Body: ' . var_export($body, true),
        //     'PHP Payload: ' . $payload,
        //     'PHP Secret: ' . substr($secret, 0, 5) . '...',
        //     'PHP Signature: ' . $expected,
        //     'Received Signature: ' . $signature

        // ]);
        // echo "<pre>";
        // print_r($expected);
        // echo "---";
        // print_r($signature);
        // exit;
        if (!hash_equals($expected, $signature)) {
            return response()->json([
                'error' => 'Invalid API signature'
            ], 401);
        }

        return $next($request);
    }
}
