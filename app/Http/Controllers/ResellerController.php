<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\Hosting\WhmClient;
use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResellerController extends Controller
{
    public function __construct(protected WhmClient $whm, protected WhmcsClient $whmcs) {}

    private function clientId(): int { return (int)(session('whmcs_client.id') ?? 0); }

    private function allowed(): bool
    {
        if(!AdminSetting::bool('reseller_enabled',false)) return false;
        $data=$this->whmcs->call('GetClientsProducts',['clientid'=>$this->clientId()]);
        foreach(($data['products']['product']??[]) as $p){
            if(strtolower((string)($p['status']??''))!=='active') continue;
            $name=strtolower((string)($p['name']??''));
            $group=strtolower((string)($p['groupname']??''));
            if(str_contains($name,'reseller')||str_contains($group,'reseller')) return true;
        }
        return false;
    }

    private function guard(): void { abort_unless($this->allowed(),403,'Reseller Hosting is not enabled for this account.'); }

    public function index(): View
    {
        $this->guard();
        $accounts=$this->whm->configured()?$this->whm->listAccounts():[];
        $packages=$this->whm->configured()?$this->whm->listPackages():[];
        return view('reseller.index',compact('accounts','packages'));
    }

    public function accounts(): View { $this->guard(); return view('reseller.accounts',['accounts'=>$this->whm->configured()?$this->whm->listAccounts():[]]); }
    public function packages(): View { $this->guard(); return view('reseller.packages',['packages'=>$this->whm->configured()?$this->whm->listPackages():[]]); }

    public function createPackage(Request $request){
        $this->guard();
        $d=$request->validate(['name'=>'required|string|max:100','quota'=>'required|integer|min:0','bwlimit'=>'required|integer|min:0','maxpop'=>'required|integer|min:-1','maxsql'=>'required|integer|min:-1']);
        $this->whm->createPackage($d); return back()->with('success','Package creation request sent to WHM.');
    }

    public function deletePackage(string $pkg){$this->guard();$this->whm->deletePackage($pkg);return back()->with('success','Package deleted.');}
    public function changePackage(Request $request,string $user){$this->guard();$d=$request->validate(['pkg'=>'required|string|max:100']);$this->whm->changePackage($user,$d['pkg']);return back()->with('success','Account package updated.');}

    public function createAccount(Request $request)
    {
        $this->guard();
        $d=$request->validate(['domain'=>'required|hostname','username'=>'required|alpha_num|max:16','password'=>'required|string|min:12|max:100','email'=>'required|email|max:190','pkg'=>'required|string|max:100']);
        $result=$this->whm->createAccount($d);
        return back()->with('success',(($result['metadata']['result']??1)==1)?'Hosting account created.':'WHM could not create the account.');
    }

    public function suspend(string $user){$this->guard();$this->whm->suspend($user,'Suspended from reseller panel');return back()->with('success','Account suspended.');}
    public function unsuspend(string $user){$this->guard();$this->whm->unsuspend($user);return back()->with('success','Account unsuspended.');}
    public function terminate(string $user){$this->guard();$this->whm->terminate($user);return back()->with('success','Account terminated.');}
}