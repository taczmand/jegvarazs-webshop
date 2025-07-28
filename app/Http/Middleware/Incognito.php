<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class Incognito
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $incognito_mode = Cookie::get('incognito_mode');
        if ($incognito_mode == 'jegvarazs') {
            return $next($request);
        } else {
            return response()->view('pages.coming_soon', [], 200);
        }
    }
}
