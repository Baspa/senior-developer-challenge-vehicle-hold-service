<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class EnsureApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');
        $validApiKey = config('hold.api_key');

        if (! is_string($validApiKey) || $validApiKey === '') {
            return new JsonResponse(['message' => 'API_KEY_NOT_CONFIGURED', 'code' => 500], 500);
        }

        if ($apiKey === null) {
            return new JsonResponse(['message' => 'API_KEY_MISSING', 'code' => 400], 400);
        }

        if (!hash_equals($validApiKey, $apiKey)) {
            return new JsonResponse(['message' => 'INVALID_API_KEY', 'code' => 401], 401);
        }

        return $next($request);
    }
}
