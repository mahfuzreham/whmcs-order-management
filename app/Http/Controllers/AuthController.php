<?php

namespace App\Http\Controllers;

use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;

class AuthController extends Controller
{
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
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $auth = $whmcs->authenticateCustomer($data['email'], $data['password']);

        if (($auth['requires_2fa'] ?? false) === true) {
            session([
                'pending_2fa_email' => $data['email'],
                'pending_2fa_state' => $auth['api_state'] ?? null,
            ]);

            return back()->withErrors([
                'email' => 'Two-factor verification is required. 2FA UI is the next authentication milestone.',
            ]);
        }

        if (!($auth['authenticated'] ?? false)) {
            return back()->withErrors([
                'email' => $auth['message'] ?? 'Unable to authenticate with WHMCS.',
            ])->onlyInput('email');
        }

        $client = $whmcs->findClientByEmail($data['email']);

        if (!$client) {
            return back()->withErrors([
                'email' => 'WHMCS authenticated the account, but the customer profile could not be loaded.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        session([
            'whmcs_client' => $client,
            'whmcs_api_state' => $auth['api_state'] ?? null,
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
