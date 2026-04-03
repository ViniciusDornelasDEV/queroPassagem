<?php

namespace App\Http\Middleware;

use Closure;
use RuntimeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.api.key');
        $provided = $request->header('X-API-KEY');

        if (!is_string($expected) || $expected === '') {
            throw new RuntimeException('API key is not configured.');
        }

        if (!is_string($provided) || !hash_equals($expected, $provided)) {
            throw new UnauthorizedHttpException('', 'Invalid API Key');
        }

        return $next($request);
    }
}
