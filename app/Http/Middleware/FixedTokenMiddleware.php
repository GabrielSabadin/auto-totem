<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixedTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tokenFromHeader = $request->bearerToken(); 
        $fixedToken = config('app.product_api_token');

        if (!$tokenFromHeader || $tokenFromHeader !== $fixedToken) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
