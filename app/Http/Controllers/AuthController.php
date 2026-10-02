<?php

namespace App\Http\Controllers;

use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected function throttleKey(Request $request, string $email = ''): string
    {
        return 'portal-login:'.Str::lower(trim($email)).'|'.$request->ip();
    }

    public function showLogin()
    {
        if (session()->has('whmcs_client')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request, WhmcsClient $whmcs)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:500'],
        ]);

        $key = $this->throttleKey($request, $data['email']);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many login attempts. Please try again later.'])->onlyInput('email');
        }

        $auth = $whmcs->authenticateCustomer($data['email'], $data['password']);

        if (($auth['requires_2fa'] ?? false) === true) {
            RateLimiter::clear($key);
            session([
                'pending_2fa_email' => $data['email'],
                'pending_2fa_state' => $auth['api_state'],
            ]);

            return redirect()->route('login.2fa');
        }

        if (!($auth['authenticated'] ?? false)) {
            RateLimiter::hit($key, 900);
            return back()->withErrors([
                'email' => $auth['message'] ?? 'Unable to authenticate with WHMCS.',
            ])->onlyInput('email');
        }

        $client = $whmcs->findClientByEmail($data['email']);

        if (!$client) {
            RateLimiter::hit($key, 900);
            return back()->withErrors([
                'email' => 'WHMCS authenticated the account, but the customer profile could not be loaded.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        session([
            'whmcs_client' => $client,
            'whmcs_api_state' => $auth['api_state'] ?? null,
        ]);

        return redirect()->route('dashboard');
    }

    public function showTwoFactor()
    {
        if (!session()->has('pending_2fa_email') || !session()->has('pending_2fa_state')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    public function verifyTwoFactor(Request $request, WhmcsClient $whmcs)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'regex:/^[0-9A-Za-z -]+$/'],
        ]);

        $email = (string) session('pending_2fa_email', '');
        $state = (string) session('pending_2fa_state', '');
        if ($email === '' || $state === '') return redirect()->route('login');

        $key = $this->throttleKey($request, $email.'|2fa');
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['code' => 'Too many verification attempts. Please try again later.']);
        }

        $auth = $whmcs->verifyTwoFactor($state, $data['code']);

        if (!($auth['authenticated'] ?? false)) {
            RateLimiter::hit($key, 900);
            return back()->withErrors([
                'code' => $auth['message'] ?? 'Invalid verification code.',
            ]);
        }

        $client = $whmcs->findClientByEmail($email);

        if (!$client) {
            RateLimiter::hit($key, 900);
            return back()->withErrors([
                'code' => 'Authentication succeeded, but the customer profile could not be loaded.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        session()->forget(['pending_2fa_email', 'pending_2fa_state']);
        session([
            'whmcs_client' => $client,
            'whmcs_api_state' => $auth['api_state'] ?? $state,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
