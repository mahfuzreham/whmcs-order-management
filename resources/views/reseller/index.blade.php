@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>Reseller Hosting</h1><div class="muted">Manage your hosting business from one branded control center.</div></div></div>
<div class="grid">
<div class="card stat"><div class="label">Hosting Accounts</div><div class="value">{{ count($accounts) }}</div></div>
<div class="card stat"><div class="label">Packages</div><div class="value">{{ count($packages) }}</div></div>
<div class="card stat"><div class="label">WHM API</div><div class="value">{{ count($accounts)>=0 && count($packages)>=0 ? 'Ready' : '—' }}</div></div>
<div class="card stat"><div class="label">Panel</div><div class="value">White-label</div></div>
</div>
<div class="links" style="margin-top:20px">
<a href="{{ route('reseller.accounts') }}">Hosting Accounts</a><a href="{{ route('reseller.packages') }}">Packages</a>
</div>
<div class="card">
<div class="section-title"><h2>Quick Create Account</h2></div>
<form method="POST" action="{{ route('reseller.accounts.create') }}" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">@csrf
<input class="input" name="domain" placeholder="example.com" required><input class="input" name="username" placeholder="cpuser" required>
<input class="input" type="email" name="email" placeholder="customer@example.com" required><input class="input" type="password" name="password" placeholder="Temporary password" required>
<select class="input" name="pkg" required><option value="">Select package</option>@foreach($packages as $p)<option value="{{ $p['name']??$p['pkg']??'' }}">{{ $p['name']??$p['pkg']??'Package' }}</option>@endforeach</select>
<button class="btn" type="submit">Create Hosting Account</button>
</form>
</div>
<div class="card" style="margin-top:16px"><h2>Future-ready controls</h2><p class="muted">Account lifecycle, package management, usage monitoring, branded cPanel access, DNS/SSL/email/database tools, backups, API access, WHMCS automation and notifications are designed to plug into this panel without exposing WHM internals.</p></div>
@endsection