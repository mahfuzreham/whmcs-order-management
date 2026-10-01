@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>{{ $service['domain'] ?? 'Web Hosting' }}</h1><div class="muted">{{ $service['name'] ?? $service['product'] ?? 'Web Hosting' }}</div></div><span class="badge">{{ $service['status'] ?? 'Active' }}</span></div>

<div class="grid">
<div class="card stat"><div class="label">Disk Usage</div><div class="value">—</div><div class="muted">Live server metrics will appear here when Hosting Panel is enabled.</div></div>
<div class="card stat"><div class="label">Bandwidth</div><div class="value">—</div><div class="muted">Live usage</div></div>
<div class="card stat"><div class="label">CPU</div><div class="value">—</div><div class="muted">Live usage</div></div>
<div class="card stat"><div class="label">RAM</div><div class="value">—</div><div class="muted">Live usage</div></div>
</div>

<div class="card" style="margin-top:18px">
<h2>Hosting Control</h2>
<p class="muted">
@if($customPanelEnabled)
The custom hosting panel is enabled. File Manager, email, database, SSL and real-time resource controls will use the hosting API.
@else
The custom Hosting Panel module is currently disabled. cPanel fallback is available so the customer can continue managing the hosting service.
@endif
</p>
<a class="btn" href="{{ route('hosting.cpanel',$service['id']) }}" target="_blank" rel="noopener">Open cPanel</a>
@if($customPanelEnabled)
<a class="btn btn-light" href="{{ route('hosting.service',$service['id']) }}">Refresh Hosting Panel</a>
@endif
</div>
@endsection
