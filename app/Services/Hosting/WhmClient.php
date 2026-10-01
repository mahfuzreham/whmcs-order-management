<?php

namespace App\Services\Hosting;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhmClient
{
    protected function request(string $function, array $params=[]): array
    {
        $host=rtrim((string)AdminSetting::get('whm_host',''),'/');
        $token=(string)AdminSetting::get('whm_api_token','');
        $port=(int)AdminSetting::get('whm_port',2087);
        $verify=AdminSetting::bool('whm_verify_ssl',true);
        if($host===''||$token==='') throw new RuntimeException('WHM API is not configured.');

        $host=preg_replace('#^https?://#i','',$host);
        $url='https://'.$host.':'.$port.'/json-api/'.ltrim($function,'/');
        $response=Http::timeout((int)AdminSetting::get('whm_timeout',20))
            ->withHeaders(['Authorization'=>'whm root:'.$token])
            ->withOptions(['verify'=>$verify])
            ->get($url,$params);
        if($response->failed()) throw new RuntimeException('WHM API HTTP '.$response->status());
        $data=$response->json();
        if(!is_array($data)) throw new RuntimeException('Invalid WHM API response.');
        return $data;
    }

    public function configured(): bool { return AdminSetting::configured('whm_host') && AdminSetting::configured('whm_api_token'); }

    public function listAccounts(): array
    {
        $d=$this->request('listaccts',['api.version'=>1]);
        return $d['data']['acct'] ?? [];
    }

    public function account(string $user): ?array
    {
        foreach($this->listAccounts() as $a) if(strcasecmp((string)($a['user']??''),$user)===0) return $a;
        return null;
    }

    public function createAccount(array $data): array { return $this->request('createacct',$data); }
    public function terminate(string $user): array { return $this->request('removeacct',['user'=>$user,'keepdns'=>'0']); }
    public function suspend(string $user,string $reason=''): array { return $this->request('suspendacct',['user'=>$user,'reason'=>$reason]); }
    public function unsuspend(string $user): array { return $this->request('unsuspendacct',['user'=>$user]); }
    public function changePackage(string $user,string $pkg): array { return $this->request('changepackage',['user'=>$user,'pkg'=>$pkg]); }
    public function password(string $user,string $password): array { return $this->request('passwd',['user'=>$user,'pass'=>$password]); }
    public function listPackages(): array { $d=$this->request('listpkgs'); return $d['data']['pkg'] ?? []; }
    public function createPackage(array $data): array { return $this->request('addpkg',$data); }
    public function deletePackage(string $pkg): array { return $this->request('killpkg',['pkg'=>$pkg]); }
    public function systemHealth(): array
    {
        try { return $this->request('loadavg'); } catch (\Throwable $e) { return ['error'=>$e->getMessage()]; }
    }
}