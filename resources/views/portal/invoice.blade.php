@extends('layouts.app')
@section('content')
<h1>Invoice #{{ $invoice['invoicenum'] ?: $invoice['invoiceid'] }}</h1>
<div class="card">
<p><strong>Status:</strong> {{ $invoice['status'] }}</p>
<p><strong>Issued:</strong> {{ $invoice['date'] }}</p>
<p><strong>Due:</strong> {{ $invoice['duedate'] }}</p>
<p><strong>Total:</strong> {{ $invoice['total'] }}</p>
<p><strong>Balance:</strong> {{ $invoice['balance'] }}</p>
@if(strtolower($invoice['status'] ?? '') !== 'paid' && (float)($invoice['balance'] ?? 0) > 0)
<a class="btn" href="{{ route('payment.show', $invoice['invoiceid']) }}">Pay Invoice</a>
@endif

@if($payment && $payment->completed_at && !$serviceActive && $payment->refund_status !== 'completed')
<div class="card" style="margin-top:18px;border:1px solid #f59e0b">
    <strong>Instant Refund</strong>
    <p id="refund-note">Service is not active. Refund is available for <strong id="refund-timer">5:00</strong>.</p>
    <form method="POST" action="{{ route('payment.refund',$payment->id) }}" id="refund-form">
        @csrf
        <button class="btn" type="submit" id="refund-button">Refund Payment</button>
    </form>
</div>
<script>
(function(){
 const expires={{ $payment->completed_at->timestamp }}+({{ (int)config('services.refund.window_minutes',5) }}*60);
 const timer=document.getElementById('refund-timer'), form=document.getElementById('refund-form'), button=document.getElementById('refund-button');
 function tick(){let left=Math.max(0,expires-Math.floor(Date.now()/1000));let m=Math.floor(left/60),s=left%60;timer.textContent=m+':'+String(s).padStart(2,'0');if(left<=0){form.style.display='none';document.getElementById('refund-note').textContent='The instant refund window has expired.';}}
 tick();setInterval(tick,1000);
})();
</script>
@endif
</div>
@endsection