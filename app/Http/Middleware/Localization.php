<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authLocale = auth('user')->check() ? auth('user')->user()->locale : null;
        $acceptLanguage = $authLocale ?? $request->header('Accept-Language', 'ar');

        $locale = $this->parseLocale($acceptLanguage);
        if (in_array($locale, array_keys(config('app.locales')))) {
            app()->setLocale($locale);
        } else {
            app()->setLocale('ar');
        }

        return $next($request);
    }

    /**
     * Parse the locale from Accept-Language header
     */
    private function parseLocale(string $acceptLanguage): string
    {
        // Handle cases like "en_US,en;q=0.5" or "en-US,en;q=0.9,*;q=0.8"
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $language) {
            // Remove quality values and whitespace
            $lang = trim(explode(';', $language)[0]);

            // Extract primary language code (en from en-US or en_US)
            $primaryLang = explode('-', explode('_', $lang)[0])[0];

            // Return first supported language
            if (in_array($primaryLang, array_keys(config('app.locales')))) {
                return $primaryLang;
            }
        }

        return config('app.fallback_locale'); // Default fallback
    }
}
