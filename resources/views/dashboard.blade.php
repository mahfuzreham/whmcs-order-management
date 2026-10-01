@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Welcome back, {{ $client['firstname'] ?? 'Customer' }}</h1>
        <p class="muted">Manage your services, domains, invoices and support from one place.</p>
    </div>
    <a class="btn" href="{{ route('cart') }}">Browse Services</a>
</div>

<div class="grid" style="margin-bottom:22px">
    <div class="card stat"><div class="label">Active / Total Services</div><div class="value">{{ $summary['services'] }}</div></div>
    <div class="card stat"><div class="label">Domains</div><div class="value">{{ $summary['domains'] }}</div></div>
    <div class="card stat"><div class="label">Unpaid Invoices</div><div class="value">{{ $summary['unpaid'] }}</div></div>
    <div class="card stat"><div class="label">Account</div><div class="value" style="font-size:20px">Active</div></div>
</div>

<div class="links">
    <a href="{{ route('services') }}">My Services</a>
    <a href="{{ route('domains') }}">My Domains</a>
    <a href="{{ route('invoices') }}">Pay Invoice</a>
    <a href="{{ route('tickets') }}">Contact Support</a>
    <a href="{{ route('cart') }}">Shop Products</a>
</div>

<div class="grid-2">
    <section class="card">
        <div class="section-title"><h2>Recent Payments</h2><a class="muted" href="{{ route('transactions') }}">View all</a></div>
        <div class="list">
            @forelse($recentTransactions as $tx)
                <div class="row-card" style="padding:10px 0;border-bottom:1px solid #f0f1f4">
                    <div><strong>{{ $tx['transid'] ?? 'Transaction' }}</strong><div class="muted">{{ $tx['date'] ?? '—' }} · {{ $tx['gateway'] ?? 'Payment' }}</div></div>
                    <strong>{{ $tx['amountin'] ?? $tx['amount'] ?? '—' }}</strong>
                </div>
            @empty
                <div class="empty">No payment transactions yet.</div>
            @endforelse
        </div>
    </section>

    <section class="card">
        <div class="section-title"><h2>Recent Support</h2><a class="muted" href="{{ route('tickets') }}">View all</a></div>
        <div class="list">
            @forelse($recentTickets as $ticket)
                <a href="{{ route('ticket',$ticket['id']) }}" style="text-decoration:none;padding:10px 0;border-bottom:1px solid #f0f1f4">
                    <strong>#{{ $ticket['tid'] ?? $ticket['id'] }} · {{ $ticket['subject'] ?? 'Support ticket' }}</strong>
                    <div class="muted">{{ $ticket['status'] ?? 'Open' }} · {{ $ticket['lastreply'] ?? $ticket['date'] ?? '—' }}</div>
                </a>
            @empty
                <div class="empty">No support tickets yet.</div>
            @endforelse
        </div>
    </section>
</div>

<div class="card" style="margin-top:16px">
    <div class="section-title"><h2>Need help?</h2></div>
    <p class="muted">Open a support ticket for billing, hosting, domain or account assistance.</p>
    <a class="btn" href="{{ route('tickets') }}">Open Support Ticket</a>
</div>
@endsection
