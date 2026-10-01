<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin(){ return view('admin.login'); }

    public function login(Request $request)
    {
        $data=$request->validate(['email'=>'required|email','password'=>'required|string']);
        $staff=Staff::where('email',$data['email'])->where('active',true)->first();
        if(!$staff){
            $masterEmail=config('services.admin.master_email');
            $masterPassword=config('services.admin.master_password');
            if($data['email']===$masterEmail && $masterPassword && hash_equals($masterPassword,$data['password'])){
                $request->session()->regenerate();
                session(['admin_staff'=>['id'=>0,'name'=>'Master Admin','email'=>$masterEmail,'role'=>'super_admin','permissions'=>['*']]]);
                return redirect()->intended(route('admin.dashboard'));
            }
            return back()->withErrors(['email'=>'Invalid admin credentials.'])->withInput();
        }
        if(!$staff->checkPassword($data['password'])) return back()->withErrors(['email'=>'Invalid admin credentials.'])->withInput();
        $request->session()->regenerate();
        session(['admin_staff'=>['id'=>$staff->id,'name'=>$staff->name,'email'=>$staff->email,'role'=>$staff->role,'permissions'=>$staff->role==='super_admin'?['*']:($staff->permissions??[])]]);
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request){ $request->session()->forget('admin_staff'); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('admin.login'); }
}
