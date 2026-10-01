<?php

namespace App\Http\Controllers;

use App\Models\PortalPayment;
use App\Models\AdminSetting;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public const PERMISSIONS=['dashboard.view','payments.view','payments.refund','staff.view','staff.manage','settings.manage','hosting.manage','reseller.manage'];

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

    public function settings()
    {
        return view('admin.settings',['settings'=>[
            'whmcs_url'=>AdminSetting::get('whmcs_url',''),
            'whmcs_identifier'=>AdminSetting::get('whmcs_identifier',''),
            'whmcs_secret_configured'=>AdminSetting::configured('whmcs_secret'),
            'whmcs_timeout'=>AdminSetting::get('whmcs_timeout',15),
            'whmcs_support_dept_id'=>AdminSetting::get('whmcs_support_dept_id',1),
            'hosting_panel_enabled'=>AdminSetting::bool('hosting_panel_enabled',false),
            'reseller_enabled'=>AdminSetting::bool('reseller_enabled',false),
            'whm_host'=>AdminSetting::get('whm_host',''),
            'whm_port'=>AdminSetting::get('whm_port',2087),
            'whm_api_token_configured'=>AdminSetting::configured('whm_api_token'),
            'whm_verify_ssl'=>AdminSetting::bool('whm_verify_ssl',true),
            'whm_timeout'=>AdminSetting::get('whm_timeout',20),
            'bkash_enabled'=>AdminSetting::bool('bkash_enabled',false),
            'bkash_base_url'=>AdminSetting::get('bkash_base_url','https://tokenized.pay.bka.sh/v1.2.0-beta'),
            'bkash_app_key'=>AdminSetting::get('bkash_app_key',''),
            'bkash_app_secret_configured'=>AdminSetting::configured('bkash_app_secret'),
            'bkash_username_configured'=>AdminSetting::configured('bkash_username'),
            'bkash_password_configured'=>AdminSetting::configured('bkash_password'),
            'bkash_callback_url'=>AdminSetting::get('bkash_callback_url',url('/payment/callback/bkash')),
            'bkash_timeout'=>AdminSetting::get('bkash_timeout',20),
            'checkout_payment_method'=>AdminSetting::get('checkout_payment_method','bkash'),
            'refund_window_minutes'=>AdminSetting::get('refund_window_minutes',5),
            'refund_enabled'=>AdminSetting::bool('refund_enabled',true),
            'cart_enabled'=>AdminSetting::bool('cart_enabled',true),
            'cart_show_descriptions'=>AdminSetting::bool('cart_show_descriptions',true),
            'cart_allow_quantity'=>AdminSetting::bool('cart_allow_quantity',false),
            'cart_currency'=>AdminSetting::get('cart_currency','BDT'),
            'cart_checkout_notice'=>AdminSetting::get('cart_checkout_notice',''),
            'cart_terms_url'=>AdminSetting::get('cart_terms_url',''),
            'auto_accept_order'=>AdminSetting::bool('auto_accept_order',true),
            'auto_setup_order'=>AdminSetting::bool('auto_setup_order',true),
            'order_email'=>AdminSetting::bool('order_email',true),
            'admin_master_email'=>AdminSetting::get('admin_master_email',''),
            'admin_master_password_configured'=>AdminSetting::configured('admin_master_password'),
        ]]);
    }

    public function saveSettings(Request $request)
    {
        $d=$request->validate([
            'whmcs_url'=>'required|url|max:500','whmcs_identifier'=>'required|string|max:190','whmcs_secret'=>'nullable|string|max:500','whmcs_timeout'=>'required|integer|min:5|max:120','whmcs_support_dept_id'=>'required|integer|min:1',
            'bkash_base_url'=>'required|url|max:500','whm_host'=>'nullable|string|max:255','whm_port'=>'required|integer|min:1|max:65535','whm_api_token'=>'nullable|string|max:1000','whm_timeout'=>'required|integer|min:5|max:120','bkash_app_key'=>'nullable|string|max:500','bkash_app_secret'=>'nullable|string|max:500','bkash_username'=>'nullable|string|max:500','bkash_password'=>'nullable|string|max:500','bkash_callback_url'=>'required|url|max:500','bkash_timeout'=>'required|integer|min:5|max:120',
            'checkout_payment_method'=>'required|string|max:50','refund_window_minutes'=>'required|integer|min:1|max:60','cart_currency'=>'required|string|max:8','cart_checkout_notice'=>'nullable|string|max:1000','cart_terms_url'=>'nullable|url|max:500','admin_master_email'=>'nullable|email|max:190','admin_master_password'=>'nullable|string|min:8|max:500',
        ]);
        foreach(['whmcs_url','whmcs_identifier','whmcs_timeout','whmcs_support_dept_id','whm_host','whm_port','whm_timeout','bkash_base_url','bkash_app_key','bkash_callback_url','bkash_timeout','checkout_payment_method','refund_window_minutes','cart_currency','cart_checkout_notice','cart_terms_url','admin_master_email'] as $key){ $value=$d[$key]??''; if($key==='cart_currency')$value=strtoupper($value); AdminSetting::put($key,$value); }
        foreach(['whmcs_secret','whm_api_token','bkash_app_secret','bkash_username','bkash_password','admin_master_password'] as $key){if(!empty($d[$key]))AdminSetting::put($key,$d[$key]);}
        foreach(['hosting_panel_enabled','reseller_enabled','whm_verify_ssl','bkash_enabled','refund_enabled','cart_enabled','cart_show_descriptions','cart_allow_quantity','auto_accept_order','auto_setup_order','order_email'] as $key)AdminSetting::put($key,$request->boolean($key));
        return back()->with('success','All portal settings saved successfully.');
    }
}
