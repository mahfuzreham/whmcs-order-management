@extends('layouts.app')
@section('content')
<h1>{{ $service['name'] ?? 'Service' }}</h1>
<p class="muted">{{ $service['domain'] ?? 'No domain' }}</p>
<div class="card">
<p><strong>Status:</strong> {{ $service['status'] ?? '—' }}</p>
<p><strong>Billing:</strong> {{ $service['billingcycle'] ?? '—' }}</p>
<p><strong>Next Due:</strong> {{ $service['nextduedate'] ?? '—' }}</p>
<p><strong>Amount:</strong> {{ $service['recurringamount'] ?? '—' }}</p>
</div>
@endsection