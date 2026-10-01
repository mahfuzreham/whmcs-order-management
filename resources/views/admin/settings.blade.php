@extends('admin.layout')

@section('content')
<h1>Billing Portal Settings</h1>

<div class="card">
<form method="POST" action="{{ route('admin.settings.save') }}">
@csrf

<h2>Cart</h2>
<label><input type="checkbox" name="cart_enabled" value="1" @checked($settings['cart_enabled'])> Enable /cart</label>
<br><br>
<label><input type="checkbox" name="cart_show_descriptions" value="1" @checked($settings['cart_show_descriptions'])> Show product descriptions</label>
<br><br>
<label><input type="checkbox" name="cart_allow_quantity" value="1" @checked($settings['cart_allow_quantity'])> Allow quantity selection</label>

<label>Cart currency</label>
<input class="input" maxlength="8" name="cart_currency" value="{{ $settings['cart_currency'] }}">

<label>Checkout notice</label>
<textarea class="input" name="cart_checkout_notice" rows="4" placeholder="Message shown above checkout/cart">{{ $settings['cart_checkout_notice'] }}</textarea>

<label>Terms & Conditions URL</label>
<input class="input" type="url" name="cart_terms_url" value="{{ $settings['cart_terms_url'] }}" placeholder="https://resellnom.com/terms">

<hr style="border:0;border-top:1px solid #eee;margin:24px 0">

<h2>Refund</h2>
<label>Instant refund window (minutes)</label>
<input class="input" type="number" min="1" max="60" name="refund_window_minutes" value="{{ $settings['refund_window_minutes'] }}">
<p class="muted">The production-safe refund check is performed server-side.</p>

<button class="btn" type="submit">Save Settings</button>
</form>
</div>
@endsection
