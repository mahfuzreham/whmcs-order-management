@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>My Hosting</h1><div class="muted">Manage your hosting services from your customer dashboard.</div></div></div>
@if(!$services)
<div class="card empty">No active hosting service found.</div>
@else
<div class="list">
@foreach($services as $service)
<div class="card row-card">
<div><strong>{{ $service['domain'] ?? 'Hosting Service' }}</strong><div class="muted">{{ $service['name'] ?? $service['product'] ?? 'Web Hosting' }} · {{ $service['status'] ?? 'Active' }}</div></div>
<a class="btn" href="{{ route('hosting.service',$service['id']) }}">Manage Hosting</a>
</div>
@endforeach
</div>
@endif
@endsection
