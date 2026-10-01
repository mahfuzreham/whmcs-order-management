@extends('layouts.app')
@section('content')
<h1>Invoice #{{ $invoice['invoicenum'] ?: $invoice['invoiceid'] }}</h1>
<div class="card">
<p><strong>Status:</strong> {{ $invoice['status'] }}</p>
<p><strong>Issued:</strong> {{ $invoice['date'] }}</p>
<p><strong>Due:</strong> {{ $invoice['duedate'] }}</p>
<p><strong>Total:</strong> {{ $invoice['total'] }}</p>
<p><strong>Balance:</strong> {{ $invoice['balance'] }}</p>
@if(($invoice['status'] ?? '') !== 'Paid')
<a class="btn" href="{{ route('payment.show', $invoice['invoiceid']) }}">Pay Invoice</a>
@endif
</div>
@endsection