<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitApi
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->ip();

        if ($this->limiter->tooManyAttempts($key, 60)) {
            return response()->json([
                'message' => 'Too Many Requests',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429);
        }

        $this->limiter->hit($key, 60);

        $response = $next($request);

        $response->headers->add([
            'X-RateLimit-Limit' => 60,
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, 60),
        ]);

        return $response;
    }
}
