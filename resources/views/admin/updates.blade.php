@extends('admin.layout')

@section('content')
<h1>System Updates</h1>

@if(isset($info['error']))
<div class="card danger"><strong>Update check failed:</strong><br>{{ $info['error'] }}</div>
@endif

<div class="grid">
    <div class="card"><strong>Current Version</strong><h2>{{ $info['current'] ?? config('version.version') }}</h2></div>
    <div class="card"><strong>Latest Version</strong><h2>{{ $info['latest'] ?? '—' }}</h2></div>
    <div class="card"><strong>Status</strong><h2>{{ !empty($info['available']) ? 'Update available' : 'Up to date' }}</h2></div>
</div>

<div class="card" style="margin-top:18px">
    <h2>Update Center</h2>
    @if(!empty($info['available']))
        <p><strong>{{ $info['name'] ?? $info['latest'] }}</strong></p>
        @if(!empty($info['published_at']))<p class="muted">Released: {{ $info['published_at'] }}</p>@endif
        @if(!empty($info['notes']))<div style="white-space:pre-wrap">{{ $info['notes'] }}</div>@endif
        <form method="POST" action="{{ route('admin.updates.install') }}" style="margin-top:18px" onsubmit="return confirm('Backup and install this update now?')">
            @csrf
            <button class="btn" type="submit">Backup & Install Update</button>
        </form>
    @else
        <p class="muted">No new release is available.</p>
    @endif
    <form method="POST" action="{{ route('admin.updates.check') }}" style="margin-top:12px">
        @csrf
        <button class="btn" type="submit">Check Again</button>
    </form>
</div>

<div class="card" style="margin-top:18px">
    <h2>Private GitHub Repository</h2>
    <p class="muted">If you make the repository private, create a GitHub fine-grained token with read-only access to this repository and save it here. The token is encrypted in the database and is never shown to customers.</p>
    <form method="POST" action="{{ route('admin.updates.settings') }}">
        @csrf
        <label>GitHub Read Token</label>
        <input class="input" type="password" name="github_update_token" placeholder="{{ AdminSetting::configured('github_update_token') ? 'Configured — leave blank to keep current token' : 'ghp_... / fine-grained token' }}">
        <label>GitHub/API Timeout</label>
        <input class="input" type="number" min="10" max="120" name="update_timeout" value="{{ AdminSetting::get('update_timeout',30) }}">
        <button class="btn" type="submit">Save Update Settings</button>
    </form>
</div>
@endsection
