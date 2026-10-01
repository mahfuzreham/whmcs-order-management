@extends('layouts.app')
@section('content')
<div style="max-width:430px;margin:70px auto">
    <div class="card">
        <h1>Two-Factor Verification</h1>
        <p class="muted">Enter the verification code from your WHMCS authenticator.</p>
        @if($errors->any())<p style="color:#b91c1c">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('login.2fa.verify') }}">
            @csrf
            <p><label>Verification code</label><br><input name="code" inputmode="numeric" autocomplete="one-time-code" required style="width:100%;padding:12px;box-sizing:border-box"></p>
            <button type="submit" style="background:#111827;color:white;padding:12px 18px;border-radius:8px">Verify & Continue</button>
        </form>
    </div>
</div>
@endsection
