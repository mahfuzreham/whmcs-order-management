<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;

class UnifiedDashboardController extends Controller
{
    public function show()
    {
        if (session()->has('admin_staff')) {
            return redirect()->route('admin.dashboard');
        }

        if (session()->has('whmcs_client')) {
            return app(DashboardController::class)();
        }

        return view('dashboard.login');
    }

    public function login(Request $request, WhmcsClient $whmcs)
    {
        $data = $request->validate(['email'=>'required|email','password'=>'required|string']);

        $staff = Staff::where('email',$data['email'])->where('active',true)->first();
        $masterEmail = AdminSetting::get('admin_master_email', '');
        $masterPassword = AdminSetting::get('admin_master_password', '');

        if (($staff && $staff->checkPassword($data['password'])) ||
            (!$staff && $masterEmail && $data['email'] === $masterEmail && $masterPassword && hash_equals($masterPassword,$data['password']))) {
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
            session(['pending_2fa_email'=>$data['email'],'pending_2fa_state'=>$auth['api_state'] ?? null]);
            return redirect()->route('login.2fa');
        }

        if (!($auth['authenticated'] ?? false)) {
            return back()->withErrors(['email'=>'Invalid email or password.'])->onlyInput('email');
        }

        $client = $whmcs->findClientByEmail($data['email']);
        if (!$client) {
            return back()->withErrors(['email'=>'Customer profile could not be loaded from WHMCS.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        session(['whmcs_client'=>$client,'whmcs_api_state'=>$auth['api_state'] ?? null]);

        return redirect()->route('dashboard');
    }
}
