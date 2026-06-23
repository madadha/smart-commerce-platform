<?php

namespace App\Http\Middleware;

use App\Support\Localization\ActiveLanguageRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = app(ActiveLanguageRegistry::class)->defaultCode();

        App::setLocale($locale);

        return $next($request);
    }
}
