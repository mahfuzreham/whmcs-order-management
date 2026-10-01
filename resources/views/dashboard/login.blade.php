@extends('layouts.app')
@section('content')
<div style="max-width:430px;margin:70px auto">
<div class="card">
<h1>ResellNom Billing</h1>
<p class="muted">Sign in to your customer, staff, or admin account.</p>
@if($errors->any())<p style="color:#b91c1c">{{ $errors->first() }}</p>@endif
<form method="POST" action="{{ route('dashboard.login') }}">
@csrf
<p><label>Email</label><br><input name="email" type="email" required style="width:100%;padding:12px;box-sizing:border-box"></p>
<p><label>Password</label><br><input name="password" type="password" required style="width:100%;padding:12px;box-sizing:border-box"></p>
<button type="submit" style="background:#111827;color:white;padding:12px 18px;border-radius:8px">Sign in</button>
</form>
<p style="margin-top:18px"><a href="{{ route('cart') }}">Continue shopping</a></p>
</div>
</div>
@endsection
