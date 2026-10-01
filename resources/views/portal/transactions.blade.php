@extends('layouts.app')
@section('content')
<h1>Payment History</h1>
<div class="list">
@forelse($transactions as $tx)
<div class="card row-card"><div><strong>{{ $tx['transid'] ?? 'Transaction' }}</strong><div class="muted">{{ $tx['date'] ?? '—' }}</div></div><div>{{ $tx['amountin'] ?? $tx['amount'] ?? '—' }}</div></div>
@empty<div class="card"><p class="muted">No transactions found.</p></div>@endforelse
</div>
@endsection