<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('locales.supported', []));
        $default = config('locales.default', 'bs');
        $routeLocale = $request->segment(1);
        $usesSessionLocale = in_array($request->segment(1), ['dashboard', 'admin', 'jezik'], true);
        $locale = in_array($routeLocale, $supported, true)
            ? $routeLocale
            : ($usesSessionLocale ? $request->session()->get('locale', $default) : $default);

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);
        app('translator')->setFallback($default);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
