<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->input('lang', session('locale', 'ar'));

        if (in_array($locale, ['en', 'ar'])) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
            if ($locale === 'ar') {
                config(['app.direction' => 'rtl']);
            } else {
                config(['app.direction' => 'ltr']);
            }
        }

        return $next($request);

    }
}
