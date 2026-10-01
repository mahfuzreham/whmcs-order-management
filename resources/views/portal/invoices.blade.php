@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>Invoices</h1><p class="muted">View, pay and track all WHMCS invoices.</p></div></div>
<div class="list">
@forelse($invoices as $invoice)
<div class="card row-card">
    <div><strong><a href="{{ route('invoice', $invoice['id'] ?? 0) }}" style="text-decoration:none">Invoice #{{ $invoice['id'] ?? '—' }}</a></strong><div class="muted">{{ $invoice['date'] ?? '—' }} · Due {{ $invoice['duedate'] ?? '—' }}</div></div>
    <div style="text-align:right"><strong>{{ $invoice['currencyprefix'] ?? '' }}{{ $invoice['total'] ?? '0.00' }}{{ $invoice['currencysuffix'] ?? '' }}</strong><div style="margin-top:5px"><span class="badge">{{ $invoice['status'] ?? 'Unknown' }}</span></div></div>
</div>
@empty<div class="card empty">No invoices found.</div>@endforelse
</div>
@endsection
