@extends('layouts.app')
@section('content')
<div class="page-head">
    <div><h1>{{ $service['name'] ?? 'Service' }}</h1><p class="muted">{{ $service['domain'] ?? 'Service details' }}</p></div>
    <a class="btn btn-light" href="{{ route('services') }}">← Services</a>
</div>
<div class="grid" style="margin-bottom:18px">
    <div class="card stat"><div class="label">Status</div><div class="value" style="font-size:21px">{{ $service['status'] ?? 'Unknown' }}</div></div>
    <div class="card stat"><div class="label">Billing Cycle</div><div class="value" style="font-size:21px">{{ $service['billingcycle'] ?? '—' }}</div></div>
    <div class="card stat"><div class="label">Next Due Date</div><div class="value" style="font-size:21px">{{ $service['nextduedate'] ?? '—' }}</div></div>
    <div class="card stat"><div class="label">Recurring Amount</div><div class="value" style="font-size:21px">{{ $service['recurringamount'] ?? $service['amount'] ?? '—' }}</div></div>
</div>
<div class="grid-2">
    <div class="card">
        <div class="section-title"><h2>Service Information</h2></div>
        <div class="list">
            <div class="row-card"><span class="muted">Product</span><strong>{{ $service['name'] ?? '—' }}</strong></div>
            <div class="row-card"><span class="muted">Domain</span><strong>{{ $service['domain'] ?? '—' }}</strong></div>
            <div class="row-card"><span class="muted">Username</span><strong>{{ $service['username'] ?? '—' }}</strong></div>
            <div class="row-card"><span class="muted">Registration</span><strong>{{ $service['regdate'] ?? '—' }}</strong></div>
            <div class="row-card"><span class="muted">Next Due</span><strong>{{ $service['nextduedate'] ?? '—' }}</strong></div>
        </div>
    </div>
    <div class="card">
        <div class="section-title"><h2>Need assistance?</h2></div>
        <p class="muted">For provisioning, suspension, renewal or service problems, contact support.</p>
        <a class="btn" href="{{ route('tickets') }}">Contact Support</a>
    </div>
</div>
@endsection
