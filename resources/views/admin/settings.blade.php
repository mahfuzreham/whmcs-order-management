@extends('admin.layout') @section('content')
<h1>Portal Settings</h1><div class="card"><form method="POST" action="{{ route('admin.settings.save') }}">@csrf<label>Instant refund window (minutes)</label><input class="input" type="number" min="1" max="60" name="refund_window_minutes" value="{{ $settings['refund_window_minutes'] }}"><p class="muted">The production-safe refund check is performed server-side.</p><button class="btn">Save</button></form></div>
@endsection
