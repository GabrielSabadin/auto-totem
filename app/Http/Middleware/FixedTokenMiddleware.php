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
        $fixedToken = env('PRODUCT_API_TOKEN');

        logger()->info('Token' . $tokenFromHeader);
        logger()->info('Token fixed' . $fixedToken);

        if (!$tokenFromHeader || $tokenFromHeader !== $fixedToken) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
