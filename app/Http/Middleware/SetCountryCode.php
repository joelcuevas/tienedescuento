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
        $path = $request->path(); // Get the request path
        $allowedCountries = array_keys(config('params.countries'));
        $pattern = '#^('.implode('|', $allowedCountries).')(?=/|$)#';

        if (preg_match($pattern, $path, $matches)) {
            $country = $matches[1];
            session(['app.country' => $country]);
        } else {
            if (session('app.coutry')) {
                $country = session('app.country');
            } else {
                $country = config('params.default_country');
            }
        }

        URL::defaults(['countryCode' => $country]);

        return $next($request);
    }
}
