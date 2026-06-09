<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Route names yang dikecualikan dari pengecekan.
     */
    private array $excluded = [
        'login',
        'login.post',
        'logout',
        'password.change',
        'password.update',
    ];

    /**
     * Beberapa password default yang wajib diganti.
     */
    private array $defaultPasswords = [
        'password',
        'umjambi60825',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Route yang dikecualikan — tetap lanjut
        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $this->excluded, true)) {
            return $next($request);
        }

        // Cek apakah password masih default
        foreach ($this->defaultPasswords as $default) {
            if (Hash::check($default, $user->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Anda wajib mengganti password default.',
                        'redirect' => route('password.change'),
                    ], 403);
                }
                return redirect()->route('password.change')
                    ->with('warning', 'Demi keamanan, Anda wajib mengganti password default sebelum melanjutkan.');
            }
        }

        return $next($request);
    }
}
