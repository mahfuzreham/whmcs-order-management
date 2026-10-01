<?php

namespace App\Http\Controllers;

use App\Models\PortalPayment;
use App\Models\AdminSetting;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public const PERMISSIONS=['dashboard.view','payments.view','payments.refund','staff.view','staff.manage','settings.manage'];

    public function dashboard(){ return view('admin.dashboard',['payments'=>PortalPayment::latest()->limit(10)->get(),'staffCount'=>Staff::count()]); }

    public function payments(){ return view('admin.payments',['payments'=>PortalPayment::latest()->paginate(30)]); }

    public function staff(){ return view('admin.staff',['staff'=>Staff::latest()->get(),'permissions'=>self::PERMISSIONS]); }

    public function createStaff(Request $request)
    {
        $d=$request->validate(['name'=>'required|string|max:100','email'=>'required|email|max:190|unique:staff,email','password'=>'required|string|min:8','role'=>'required|in:staff,manager,super_admin','permissions'=>'array']);
        $d['password']=Hash::make($d['password']); $d['active']=true; $d['permissions']=$d['role']==='super_admin'?['*']:array_values(array_intersect($d['permissions']??[],self::PERMISSIONS));
        Staff::create($d); return back()->with('success','Staff account created.');
    }

    public function toggleStaff(int $id)
    {
        $staff=Staff::findOrFail($id); $staff->update(['active'=>!$staff->active]); return back()->with('success','Staff status updated.');
    }

    public function deleteStaff(int $id){ Staff::findOrFail($id)->delete(); return back()->with('success','Staff account deleted.'); }

    public function settings(){ return view('admin.settings',['settings'=>[
        'refund_window_minutes'=>AdminSetting::get('refund_window_minutes',config('services.refund.window_minutes',5)),
        'bkash_enabled'=>config('services.bkash.enabled',false),
    ]]); }

    public function saveSettings(Request $request)
    {
        $d=$request->validate(['refund_window_minutes'=>'required|integer|min:1|max:60']);
        AdminSetting::put('refund_window_minutes',$d['refund_window_minutes']);
        return back()->with('success','Settings saved. Put permanent values in .env for production.');
    }
}
