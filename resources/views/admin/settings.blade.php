@extends('admin.layout')

@section('content')
<h1>Portal Settings</h1>

@if($errors->any())
<div class="card" style="border-left:4px solid #dc2626">
@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
</div>
@endif

<div class="card">
<form method="POST" action="{{ route('admin.settings.save') }}">
@csrf

<h2>WHMCS API</h2>
<label>WHMCS URL</label>
<input class="input" type="url" name="whmcs_url" value="{{ $settings['whmcs_url'] }}" placeholder="https://billing.example.com">
<label>API Identifier</label>
<input class="input" name="whmcs_identifier" value="{{ $settings['whmcs_identifier'] }}">
<label>API Secret</label>
<input class="input" type="password" name="whmcs_secret" placeholder="{{ $settings['whmcs_secret_configured'] ? 'Configured — leave blank to keep current secret' : 'Enter API secret' }}">
<label>API Timeout</label>
<input class="input" type="number" min="5" max="120" name="whmcs_timeout" value="{{ $settings['whmcs_timeout'] }}">
<label>Support Department ID</label>
<input class="input" type="number" min="1" name="whmcs_support_dept_id" value="{{ $settings['whmcs_support_dept_id'] }}">

<hr style="border:0;border-top:1px solid #eee;margin:24px 0">

<hr style="border:0;border-top:1px solid #eee;margin:24px 0">

<h2>Web Hosting Control Panel</h2>
<label><input type="checkbox" name="hosting_panel_enabled" value="1" @checked($settings['hosting_panel_enabled'])> Enable custom Hosting Panel</label>
<p class="muted">Disabled by default. When disabled, customers still get the cPanel fallback from their WHMCS hosting service. Enable this when the custom WHM/cPanel API panel is ready.</p>

<h2>bKash Tokenized Checkout</h2>
<label><input type="checkbox" name="bkash_enabled" value="1" @checked($settings['bkash_enabled'])> Enable bKash</label>
<label>Base URL</label>
<input class="input" type="url" name="bkash_base_url" value="{{ $settings['bkash_base_url'] }}">
<label>App Key</label>
<input class="input" name="bkash_app_key" value="{{ $settings['bkash_app_key'] }}">
<label>App Secret</label>
<input class="input" type="password" name="bkash_app_secret" placeholder="{{ $settings['bkash_app_secret_configured'] ? 'Configured — leave blank to keep current secret' : 'Enter App Secret' }}">
<label>Username</label>
<input class="input" type="text" name="bkash_username" placeholder="{{ $settings['bkash_username_configured'] ? 'Configured — leave blank to keep current username' : 'Enter username' }}">
<label>Password</label>
<input class="input" type="password" name="bkash_password" placeholder="{{ $settings['bkash_password_configured'] ? 'Configured — leave blank to keep current password' : 'Enter password' }}">
<label>Callback URL</label>
<input class="input" type="url" name="bkash_callback_url" value="{{ $settings['bkash_callback_url'] }}">
<label>Request Timeout</label>
<input class="input" type="number" min="5" max="120" name="bkash_timeout" value="{{ $settings['bkash_timeout'] }}">

<hr style="border:0;border-top:1px solid #eee;margin:24px 0">

<h2>Checkout & Orders</h2>
<label>Payment method</label>
<input class="input" name="checkout_payment_method" value="{{ $settings['checkout_payment_method'] }}" placeholder="bkash">
<label><input type="checkbox" name="auto_accept_order" value="1" @checked($settings['auto_accept_order'])> Automatically accept order after successful payment</label>
<label><input type="checkbox" name="auto_setup_order" value="1" @checked($settings['auto_setup_order'])> Automatically setup/provision order</label>
<label><input type="checkbox" name="order_email" value="1" @checked($settings['order_email'])> Send WHMCS order email</label>

<hr style="border:0;border-top:1px solid #eee;margin:24px 0">

<h2>Refund</h2>
<label><input type="checkbox" name="refund_enabled" value="1" @checked($settings['refund_enabled'])> Enable instant refund</label>
<label>Instant refund window (minutes)</label>
<input class="input" type="number" min="1" max="60" name="refund_window_minutes" value="{{ $settings['refund_window_minutes'] }}">

<hr style="border:0;border-top:1px solid #eee;margin:24px 0">

<h2>Cart</h2>
<label><input type="checkbox" name="cart_enabled" value="1" @checked($settings['cart_enabled'])> Enable /cart</label>
<label><input type="checkbox" name="cart_show_descriptions" value="1" @checked($settings['cart_show_descriptions'])> Show product descriptions</label>
<label><input type="checkbox" name="cart_allow_quantity" value="1" @checked($settings['cart_allow_quantity'])> Allow quantity selection</label>
<label>Cart currency</label>
<input class="input" maxlength="8" name="cart_currency" value="{{ $settings['cart_currency'] }}">
<label>Checkout notice</label>
<textarea class="input" name="cart_checkout_notice" rows="4">{{ $settings['cart_checkout_notice'] }}</textarea>
<label>Terms & Conditions URL</label>
<input class="input" type="url" name="cart_terms_url" value="{{ $settings['cart_terms_url'] }}" placeholder="https://resellnom.com/terms">

<hr style="border:0;border-top:1px solid #eee;margin:24px 0">

<h2>Master Admin Login</h2>
<label>Master Admin Email</label>
<input class="input" type="email" name="admin_master_email" value="{{ $settings['admin_master_email'] }}">
<label>Master Admin Password</label>
<input class="input" type="password" name="admin_master_password" placeholder="{{ $settings['admin_master_password_configured'] ? 'Configured — leave blank to keep current password' : 'Set master password' }}">
<p class="muted">Secrets are encrypted before being stored in the database.</p>

<button class="btn" type="submit">Save All Settings</button>
</form>
</div>
@endsection
