<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PageViewCounter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $sessionKey = 'viewed:' . $path;
        $cacheKey = 'views:' . $path;

        // Verificamos si la sesión actual YA visitó esta página
        if (!$request->session()->has($sessionKey)) {
            
            // 1. Incrementamos el contador global en cache
            $currentViews = Cache::get($cacheKey, 0);
            Cache::put($cacheKey, $currentViews + 1, now()->addDays(30)); // Persiste 30 días
            
            // 2. Registrar la clave para estadísticas
            $allKeys = Cache::get('all_view_keys', []);
            if (!in_array($cacheKey, $allKeys)) {
                $allKeys[] = $cacheKey;
                Cache::put('all_view_keys', $allKeys, now()->addDays(30));
            }
            
            // 3. Marcamos en la sesión del usuario que ya la vio
            // Esto dura hasta que cierren el navegador (o expire la sesión)
            $request->session()->put($sessionKey, true);
        }

        return $next($request);
    }
}
