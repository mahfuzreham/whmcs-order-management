@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>Invoices</h1><p class="muted">Billing invoices from WHMCS.</p></div></div>
<div class="list">
@forelse($invoices as $invoice)
<div class="card row-card">
    <div><strong>Invoice #{{ $invoice['id'] ?? '—' }}</strong><div class="muted">{{ $invoice['date'] ?? '—' }} · Due {{ $invoice['duedate'] ?? '—' }}</div></div>
    <div style="text-align:right"><strong>{{ $invoice['currencyprefix'] ?? '' }}{{ $invoice['total'] ?? '0.00' }}{{ $invoice['currencysuffix'] ?? '' }}</strong><div class="badge">{{ $invoice['status'] ?? 'Unknown' }}</div></div>
</div>
@empty
<div class="card"><p class="muted">No invoices found.</p></div>
@endforelse
</div>
@endsection
