<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cache\RateLimiter;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request, RateLimiter $limiter)
    {
        $key = 'login:'.$request->ip();

        if ($limiter->tooManyAttempts($key, 5)) {
            $seconds = $limiter->availableIn($key);
            return back()->with('retryAfter', $seconds);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $limiter->clear($key);
            $request->session()->regenerate();

            // Log login history
            if (Auth::check()) {
                LoginHistory::create([
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'login_at' => now(),
                ]);
            }

            return redirect()->intended('/dashboard');
        }

        $limiter->hit($key, 60);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Update login history with logout timestamp
        if ($user) {
            LoginHistory::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->orderBy('login_at', 'desc')
                ->first()
                ?->update(['logout_at' => now()]);
        }

        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}
