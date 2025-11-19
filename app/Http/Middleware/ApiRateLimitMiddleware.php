<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        $maxAttempts = auth()->check() ? 60 : 30; // 60 for authenticated, 30 for guests
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $maxAttempts));

        return $response;
    }

    protected function resolveRequestSignature(Request $request): string
    {
        if (auth()->check()) {
            return 'api_rate_limit:user:' . auth()->id();
        }

        return 'api_rate_limit:ip:' . $request->ip();
    }
}