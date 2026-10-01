@extends('layouts.app')
@section('content')
<h1>Support</h1>
@if(session('success'))<div class="card" style="margin-bottom:15px">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" style="margin-bottom:15px;color:#b91c1c">{{ $errors->first() }}</div>@endif
<div class="card" style="margin-bottom:20px"><h3>Open a ticket</h3><form method="POST" action="{{ route('tickets.create') }}">@csrf
<input name="subject" placeholder="Subject" required style="width:100%;padding:11px;box-sizing:border-box;margin:5px 0">
<select name="priority" style="padding:11px;margin:5px 0"><option>Medium</option><option>Low</option><option>High</option></select>
<textarea name="message" placeholder="How can we help?" required style="width:100%;height:110px;padding:11px;box-sizing:border-box;margin:5px 0"></textarea>
<button class="btn" type="submit">Submit Ticket</button></form></div>
<div class="list">@forelse($tickets as $ticket)<a class="card row-card" href="{{ route('ticket',$ticket['id']) }}" style="text-decoration:none;color:inherit"><div><strong>#{{ $ticket['tid'] }}</strong> {{ $ticket['subject'] }}<div class="muted">{{ $ticket['lastreply'] ?? $ticket['date'] }}</div></div><span class="badge">{{ $ticket['status'] }}</span></a>@empty<div class="card">No support tickets.</div>@endforelse</div>
@endsection