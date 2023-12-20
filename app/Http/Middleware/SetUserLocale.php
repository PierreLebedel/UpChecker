<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {

            $locale = $request->session()->get('locale');

            if ($locale && array_key_exists($locale, config('app.locales'))) {
                app()->setLocale($locale);
            }

            return $next($request);
        }

        $locale = $request->user()->locale ?? config('app.locale');

        if (isset($locale) && array_key_exists($locale, config('app.locales'))) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
