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

        if ($apiKey === null) {
            return new JsonResponse(['message' => 'API key is missing', 'code' => 400], 400);
        }

        if (!hash_equals($validApiKey, $apiKey)) {
            return new JsonResponse(['message' => 'Invalid API key'], 401);
        }

        return $next($request);
    }
}
