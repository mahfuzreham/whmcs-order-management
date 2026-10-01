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

        // Authentication endpoint will be finalized against the target WHMCS version.
        $client = $whmcs->findClientByEmail($data['email']);

        if (!$client) {
            return back()->withErrors(['email' => 'Customer account was not found.']);
        }

        session([
            'whmcs_client' => $client,
            'portal_email' => $data['email'],
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
