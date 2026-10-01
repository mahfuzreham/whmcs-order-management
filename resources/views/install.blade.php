<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>WHMCS Billing Portal Installer</title>
<style>body{font-family:system-ui;background:#f5f7fb;margin:0;color:#172033}.wrap{max-width:720px;margin:45px auto;padding:20px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:28px;box-shadow:0 8px 30px rgba(0,0,0,.06)}.input{width:100%;box-sizing:border-box;padding:12px;border:1px solid #d7dce5;border-radius:10px;margin:7px 0 16px}.btn{border:0;border-radius:10px;padding:12px 18px;background:#111827;color:#fff;font-weight:700;cursor:pointer}.err{background:#fff1f2;border:1px solid #fecdd3;padding:12px;border-radius:10px;margin-bottom:18px}</style></head>
<body><div class="wrap"><div class="card"><h1>WHMCS Billing Portal</h1><p>cPanel / Shared Hosting Installer</p>
@if(isset($error))<div class="err">{{ $error }}</div>@endif
@if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@if(empty($envCreated))
<form method="POST" action="{{ route('install.configure') }}">@csrf
<label>Application Name</label><input class="input" name="app_name" value="WHMCS Billing Portal" required>
<label>Application URL</label><input class="input" type="url" name="app_url" value="{{ request()->getSchemeAndHttpHost() }}" required>
<label>MySQL Host</label><input class="input" name="db_host" value="localhost" required>
<label>MySQL Port</label><input class="input" type="number" name="db_port" value="3306" required>
<label>Database Name</label><input class="input" name="db_database" required>
<label>Database Username</label><input class="input" name="db_username" required>
<label>Database Password</label><input class="input" type="password" name="db_password">
<button class="btn" type="submit">Create Configuration</button>
</form>
@else
<p>Configuration was created. Run the database setup now.</p><a class="btn" href="{{ route('install.run') }}">Run Database Setup</a>
@endif
</div></div></body></html>
