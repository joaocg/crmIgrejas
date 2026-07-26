<?php

namespace App\Http\Middleware;

use App\Support\Locale\LocaleContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUserPreference
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(app(LocaleContext::class)->resolve($request->user()));

        return $next($request);
    }
}
