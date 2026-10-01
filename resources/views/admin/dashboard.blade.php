@extends('admin.layout') @section('content')
<h1>Administration</h1><div class="grid"><div class="card"><strong>Staff</strong><h2>{{ $staffCount }}</h2></div><div class="card"><strong>Recent Payments</strong><h2>{{ $payments->count() }}</h2></div><div class="card"><strong>Refund Window</strong><h2>{{ config('services.refund.window_minutes',5) }} min</h2></div></div>
<div class="card" style="margin-top:18px"><h3>Recent payments</h3><table><tr><th>Invoice</th><th>Amount</th><th>Status</th><th>Refund</th></tr>@foreach($payments as $p)<tr><td>#{{ $p->invoice_id }}</td><td>৳{{ number_format($p->amount,2) }}</td><td>{{ $p->status }}</td><td>{{ $p->refund_status ?: '—' }}</td></tr>@endforeach</table></div>
@endsection
