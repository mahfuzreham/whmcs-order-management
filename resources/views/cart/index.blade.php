<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cart · ResellNom</title>
<style>
body{margin:0;background:#f5f7fb;color:#172033;font-family:Inter,system-ui,sans-serif}
.wrap{max-width:1180px;margin:auto;padding:24px 18px}.top{display:flex;justify-content:space-between;gap:15px;align-items:center;margin-bottom:22px}
.grid{display:grid;grid-template-columns:1fr 320px;gap:18px}.products{display:grid;grid-template-columns:repeat(2,1fr);gap:15px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:15px;padding:18px;box-shadow:0 5px 20px #00000008}
.btn{display:inline-block;border:0;border-radius:9px;padding:10px 14px;background:#111827;color:#fff;text-decoration:none;cursor:pointer}
.price{font-size:24px;font-weight:700;margin:12px 0}.muted{color:#6b7280}.notice{padding:12px;border-radius:10px;background:#eef6ff;margin-bottom:15px}
@media(max-width:800px){.grid,.products{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
<div class="top"><div><strong>ResellNom Billing</strong><div class="muted">Choose a product</div></div><a class="btn" href="{{ route('dashboard') }}">Dashboard</a></div>

@if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
@if($settings['checkout_notice'])<div class="notice">{{ $settings['checkout_notice'] }}</div>@endif

<div class="grid">
<div>
@forelse($groups as $gid => $items)
<h2>Products</h2>
<div class="products">
@foreach($items as $product)
@php
$pricing = $product['pricing'][$currency] ?? reset($product['pricing']) ?: [];
$cycle = collect(['monthly','quarterly','semiannually','annually','biennially','triennially','onetime'])
    ->first(fn($c) => isset($pricing[$c]) && (float)$pricing[$c] >= 0);
$price = $cycle ? $pricing[$cycle] : null;
@endphp
<div class="card">
<h3>{{ $product['name'] ?? 'Product' }}</h3>
@if($settings['show_descriptions'] && !empty($product['description']))
<div class="muted">{!! nl2br(e(strip_tags($product['description']))) !!}</div>
@endif
@if($price !== null)<div class="price">{{ $pricing['prefix'] ?? '' }}{{ number_format((float)$price,2) }} {{ $pricing['suffix'] ?? $currency }}</div>@endif
<form method="POST" action="{{ route('cart.add') }}">@csrf
<input type="hidden" name="pid" value="{{ $product['pid'] }}">
<input type="hidden" name="billingcycle" value="{{ $cycle ?? 'monthly' }}">
@if($settings['allow_quantity'])<input type="number" name="qty" value="1" min="1" max="20" style="width:70px;padding:8px">@endif
<button class="btn" type="submit">Add to Cart</button>
</form>
</div>
@endforeach
</div>
@endforelse
</div>

<div>
<div class="card">
<h3>Your Cart</h3>
@forelse(session('billing_cart', []) as $key => $item)
<div style="padding:10px 0;border-bottom:1px solid #eee">
<strong>Product #{{ $item['pid'] }}</strong><br>
<span class="muted">{{ $item['billingcycle'] }} · Qty {{ $item['qty'] }}</span>
<form method="POST" action="{{ route('cart.remove',$key) }}" style="margin-top:7px">@csrf<button type="submit">Remove</button></form>
</div>
@empty
<p class="muted">Your cart is empty.</p>
@endforelse
@if(session('billing_cart'))
<a class="btn" style="margin-top:14px" href="{{ route('dashboard') }}">Continue to Checkout</a>
@endif
@if($settings['terms_url'])<p class="muted">By ordering you agree to <a href="{{ $settings['terms_url'] }}">Terms</a>.</p>@endif
</div>
</div>
</div>
</div>
</body>
</html>
