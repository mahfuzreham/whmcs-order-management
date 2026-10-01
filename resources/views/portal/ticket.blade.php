@extends('layouts.app')
@section('content')
<h1>#{{ $ticket['tid'] }} — {{ $ticket['subject'] }}</h1>
<p class="muted">Status: {{ $ticket['status'] }} · Priority: {{ $ticket['priority'] }}</p>
<div class="list">
@foreach(($ticket['replies']['reply'] ?? []) as $reply)<div class="card"><strong>{{ $reply['requestor_name'] ?: ($reply['admin'] ?: 'Support') }}</strong><div class="muted">{{ $reply['date'] ?? '' }}</div><p style="white-space:pre-wrap">{{ $reply['message'] ?? '' }}</p></div>@endforeach
</div>
<div class="card"><form method="POST" action="{{ route('ticket.reply',$ticket['ticketid']) }}">@csrf<textarea name="message" required placeholder="Write a reply..." style="width:100%;height:120px;padding:11px;box-sizing:border-box"></textarea><button class="btn" type="submit" style="margin-top:10px">Send Reply</button></form></div>
@endsection