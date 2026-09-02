<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // This shared key is intentionally minimal for the MVP; production would use stronger authentication, rotation, and merchant-level authorization.
        $configuredKey = (string) config('api.key');
        $providedKey = (string) $request->header('X-API-Key');

        if ($configuredKey === '' || $providedKey === '' || !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'A valid API key is required.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
