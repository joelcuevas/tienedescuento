<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreIntendedUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        session(['app.intended' => $request->fullUrl()]);

        return $next($request);
    }
}
