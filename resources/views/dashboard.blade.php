@extends('layouts.app')
@section('content')
<h1>Welcome, {{ $client['firstname'] ?? 'Customer' }}</h1>
<p class="muted">Your billing account overview.</p>
<div class="links"><a href="{{ route('services') }}">Services</a><a href="{{ route('domains') }}">Domains</a><a href="{{ route('invoices') }}">Invoices</a></div>
<div class="grid">
    <div class="card"><div class="muted">Services</div><div class="value">{{ $summary['services'] }}</div></div>
    <div class="card"><div class="muted">Domains</div><div class="value">{{ $summary['domains'] }}</div></div>
    <div class="card"><div class="muted">Unpaid Invoices</div><div class="value">{{ $summary['unpaid'] }}</div></div>
</div>
@endsection
