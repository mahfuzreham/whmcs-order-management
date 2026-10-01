@extends('layouts.app')
@section('content')
<div class="page-head"><h1>Pay Invoice #{{ $invoice['invoicenum'] ?: $invoice['invoiceid'] }}</h1></div>
<div class="card">
    <p><strong>Status:</strong> {{ $invoice['status'] }}</p>
    <p><strong>Balance:</strong> {{ $invoice['balance'] ?? $invoice['total'] }}</p>
    <form method="POST" action="{{ route('payment.start',$invoice['invoiceid']) }}">
        @csrf
        <button class="btn" type="submit">Pay with bKash</button>
    </form>
</div>
@endsection
