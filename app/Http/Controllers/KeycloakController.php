<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;

class KeycloakController extends Controller
{
    public function login()
    {
        return Socialite::driver('keycloak')
            ->redirect();
    }

    public function callback(Request $request)
    {
        $keycloakUser = Socialite::driver('keycloak')
            ->user();

        $roles = $keycloakUser->user['roles'] ?? [];

        // if (!in_array('user-biasa', $roles)) {
        //     return $this->keycloakLogout(
        //         $keycloakUser,
        //         'Anda tidak memiliki akses.'
        //     );
        // }

        $localUser = User::where('email', $keycloakUser->email)->first();

        if (!$localUser) {
            return redirect()->route('login')
                ->with('error', 'User tidak terdaftar.');
        }

        Auth::login($localUser);
        $request->session()->regenerate();

        $parts = explode('.', $keycloakUser->token);
        $payload = json_decode(
            base64_decode(
                str_replace(['-', '_'], ['+', '/'], $parts[1])
            ),
            true
        );

        session([
            'keycloak_id'   => $keycloakUser->id,
            'access_token'  => Crypt::encrypt($keycloakUser->token),
            'refresh_token' => Crypt::encrypt($keycloakUser->refreshToken),
            'id_token'      => $keycloakUser->accessTokenResponseBody['id_token'] ?? null,
            'token_exp'     => $payload['exp'] ?? null,
        ]);

        LoginHistory::create([
            'user_id'    => $localUser->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at'   => now(),
        ]);

        return redirect()->intended('/dashboard');
    }

    /**
     * Logout dari Keycloak dengan pesan error.
     */
    private function keycloakLogout($keycloakUser, string $message)
    {
        session()->flash('error', $message);

        $idToken = $keycloakUser->accessTokenResponseBody['id_token'] ?? '';

        $logoutUrl = config('services.keycloak.base_url')
            . '/realms/'
            . config('services.keycloak.realms')
            . '/protocol/openid-connect/logout'
            . '?post_logout_redirect_uri='
            . urlencode(url('/'));

        if ($idToken) {
            $logoutUrl .= '&id_token_hint=' . $idToken;
        }

        return redirect($logoutUrl);
    }
}
