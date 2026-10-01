@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>Domains</h1><p class="muted">Your registered domains.</p></div></div>
<div class="list">
@forelse($domains as $domain)
<div class="card row-card">
    <div><strong>{{ $domain['domainname'] ?? 'Domain' }}</strong><div class="muted">Expires: {{ $domain['expirydate'] ?? '—' }}</div></div>
    <span class="badge">{{ $domain['status'] ?? 'Unknown' }}</span>
</div>
@empty
<div class="card"><p class="muted">No domains found.</p></div>
@endforelse
</div>
@endsection
