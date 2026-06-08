<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Idempotency
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply idempotency to state-changing requests
        if (!in_array($request->method(), ['POST', 'PATCH', 'PUT'])) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key');

        // If the client didn't send a key, proceed normally
        if (!$idempotencyKey) {
            return $next($request);
        }

        $cacheKey = 'idempotency_' . $idempotencyKey;

        // If we already processed this exact request, return the cached response
        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);
            return response()->json($cachedResponse['data'], $cachedResponse['status']);
        }

        // Process the request normally
        $response = $next($request);

        // Only cache successful responses (so if it failed, they can retry)
        if ($response->status() >= 200 && $response->status() < 300) {
            Cache::put($cacheKey, [
                'data' => json_decode($response->getContent(), true),
                'status' => $response->status()
            ], 86400); // Save in cache for 24 hours
        }

        return $response;
    }
}
