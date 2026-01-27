<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = env('SENSOR_API_KEY');
        $provided = $request->header('X-API-KEY');

        if (!$expected) {
            return response()->json([
                'ok' => false,
                'message' => 'Server API key is not configured.',
            ], 500);
        }

        if (!$provided || !hash_equals($expected, $provided)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
