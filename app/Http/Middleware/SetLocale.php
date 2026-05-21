<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only citizens get the language toggle — admin + office portals stay in English.
        if ($user && !$user->isCitizen()) {
            App::setLocale('en');
            return $next($request);
        }

        $locale = $request->session()->get('locale', config('app.locale', 'en'));

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
