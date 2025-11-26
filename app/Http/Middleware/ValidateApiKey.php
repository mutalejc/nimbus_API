<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Missing API key',
                'message' => 'X-API-Key header is required'
            ], 401);
        }

        $hashedKey = hash('sha256', $apiKey);
        $key = ApiKey::where('key_hash', $hashedKey)
            ->where('is_active', true)
            ->first();

        if (!$key) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is invalid or inactive'
            ], 401);
        }

        // Mark key as used
        $key->markAsUsed();

        return $next($request);
    }
}
