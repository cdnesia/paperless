<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermission
{
    /**
     * Daftar route name yang tidak perlu dicek permission.
     */
    private array $excludedRoutes = [
        'login',
        'login.post',
        'logout',
        'dashboard',
        'storage.local',
        'storage.local.upload',
        'up',
        'tanda-tangan-digital.verify',
        'profile.index',
        'profile.update',
        'profile.delete',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = Route::currentRouteName();

        // Jika route tidak punya nama, lewati
        if (!$routeName) {
            return $next($request);
        }

        // Route yang dikecualikan
        if (in_array($routeName, $this->excludedRoutes, true)) {
            return $next($request);
        }

        // User harus login
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        // Super-admin punya akses penuh
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Cek permission berdasarkan route name
        if (!$user->can($routeName)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke halaman ini.',
                ], 403);
            }

            return response()->view('errors.403', [
                'routeName' => $routeName,
            ], 403);
        }

        return $next($request);
    }
}
