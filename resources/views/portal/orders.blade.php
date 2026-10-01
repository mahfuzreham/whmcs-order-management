@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>Orders</h1><p class="muted">Your recent orders and their payment status.</p></div><a class="btn" href="{{ route('cart') }}">New Order</a></div>
<div class="list">
@forelse($orders as $order)
<div class="card row-card">
    <div>
        <strong>Order #{{ $order['id'] ?? '—' }}</strong>
        <div class="muted">{{ $order['date'] ?? '—' }} · {{ $order['paymentmethod'] ?? 'Payment' }}</div>
    </div>
    <div style="text-align:right">
        <strong>{{ $order['currencyprefix'] ?? '' }}{{ $order['amount'] ?? $order['total'] ?? '0.00' }}{{ $order['currencysuffix'] ?? '' }}</strong>
        <div style="margin-top:5px"><span class="badge">{{ $order['status'] ?? $order['paymentstatus'] ?? 'Pending' }}</span></div>
    </div>
</div>
@empty<div class="card empty">No orders found.</div>@endforelse
</div>
@endsection
