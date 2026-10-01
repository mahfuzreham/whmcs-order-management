@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>Services</h1><p class="muted">Your WHMCS products and service status.</p></div></div>
<div class="list">
@forelse($products as $product)
<div class="card row-card">
    <div><strong>{{ $product['name'] ?? 'Service' }}</strong><div class="muted">{{ $product['domain'] ?? 'No domain' }}</div></div>
    <span class="badge">{{ $product['status'] ?? 'Unknown' }}</span>
</div>
@empty
<div class="card"><p class="muted">No services found.</p></div>
@endforelse
</div>
@endsection
