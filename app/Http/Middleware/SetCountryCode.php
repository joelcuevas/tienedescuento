<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetCountryCode
{
    public function handle(Request $request, Closure $next): Response
    {
        URL::defaults(['countryCode' => config('params.default_country')]);

        return $next($request);
    }
}
