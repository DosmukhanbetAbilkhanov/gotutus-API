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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Supported locales.
     */
    private const array SUPPORTED_LOCALES = ['kz', 'ru', 'en'];

    /**
     * Default locale.
     */
    private const string DEFAULT_LOCALE = 'ru';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', self::DEFAULT_LOCALE);

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = self::DEFAULT_LOCALE;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
