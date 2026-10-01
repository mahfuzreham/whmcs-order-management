@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>Domains</h1><p class="muted">Manage your registered domains and renewal dates.</p></div><a class="btn" href="{{ route('cart') }}">Register / Renew</a></div>
<div class="list">
@forelse($domains as $domain)
<div class="card">
    <div class="row-card">
        <div><strong style="font-size:16px">{{ $domain['domainname'] ?? 'Domain' }}</strong><div class="muted">Expires {{ $domain['expirydate'] ?? '—' }}</div></div>
        <span class="badge">{{ $domain['status'] ?? 'Unknown' }}</span>
    </div>
    <div class="row-card" style="margin-top:16px;padding-top:14px;border-top:1px solid #f0f1f4">
        <span class="muted">Registration: {{ $domain['registrationdate'] ?? '—' }}</span>
        <span class="muted">Auto Renew: {{ !empty($domain['donotrenew']) ? 'Off' : 'On' }}</span>
    </div>
</div>
@empty
<div class="card empty">No domains found.</div>
@endforelse
</div>
@endsection
