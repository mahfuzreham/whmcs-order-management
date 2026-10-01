@extends('admin.layout') @section('content')
<h1>Payments & Refunds</h1><div class="card"><table><tr><th>ID</th><th>Invoice</th><th>Client</th><th>Gateway</th><th>Amount</th><th>Status</th><th>Refund</th><th>Completed</th></tr>@foreach($payments as $p)<tr><td>{{ $p->id }}</td><td>#{{ $p->invoice_id }}</td><td>{{ $p->client_id }}</td><td>{{ $p->gateway }}</td><td>৳{{ number_format($p->amount,2) }}</td><td>{{ $p->status }}</td><td>{{ $p->refund_status ?: '—' }}</td><td>{{ $p->completed_at ?: '—' }}</td></tr>@endforeach</table></div>{{ $payments->links() }}
@endsection
