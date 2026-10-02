<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\Staff;
use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class UnifiedDashboardController extends Controller
{
    public function show()
    {
        if (session()->has('admin_staff')) return redirect()->route('admin.dashboard');
        if (session()->has('whmcs_client')) return app(DashboardController::class)();
        return view('dashboard.login');
    }

    public function login(Request $request, WhmcsClient $whmcs)
    {
        $data = $request->validate(['email'=>'required|email|max:190','password'=>'required|string|max:500']);
        $key='unified-login:'.Str::lower(trim($data['email'])).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) return back()->withErrors(['email'=>'Too many login attempts. Please try again later.'])->onlyInput('email');

        $staff = Staff::where('email',$data['email'])->where('active',true)->first();
        $masterEmail = AdminSetting::get('admin_master_email', '');
        $masterPassword = AdminSetting::get('admin_master_password', '');
        $masterOk=$data['email']===$masterEmail && $masterPassword && (Hash::check($data['password'],(string)$masterPassword) || hash_equals((string)$masterPassword,$data['password']));

        if (($staff && $staff->checkPassword($data['password'])) || (!$staff && $masterOk)) {
            RateLimiter::clear($key);
            $request->session()->regenerate();
            session(['admin_staff'=>[
                'id'=>$staff?->id ?? 0,
                'name'=>$staff?->name ?? 'Master Admin',
                'email'=>$data['email'],
                'role'=>$staff?->role ?? 'super_admin',
                'permissions'=>$staff ? ($staff->role==='super_admin'?['*']:($staff->permissions??[])) : ['*'],
            ]]);
            return redirect()->route('admin.dashboard');
        }

        $auth = $whmcs->authenticateCustomer($data['email'], $data['password']);
        if (($auth['requires_2fa'] ?? false) === true) {
            RateLimiter::clear($key);
            session(['pending_2fa_email'=>$data['email'],'pending_2fa_state'=>$auth['api_state'] ?? null]);
            return redirect()->route('login.2fa');
        }
        if (!($auth['authenticated'] ?? false)) {
            RateLimiter::hit($key,900);
            return back()->withErrors(['email'=>'Invalid email or password.'])->onlyInput('email');
        }

        $client = $whmcs->findClientByEmail($data['email']);
        if (!$client) {
            RateLimiter::hit($key,900);
            return back()->withErrors(['email'=>'Customer profile could not be loaded from WHMCS.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        session(['whmcs_client'=>$client,'whmcs_api_state'=>$auth['api_state'] ?? null]);
        return redirect()->route('dashboard');
    }
}
