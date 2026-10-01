@extends('layouts.app')
@section('content')
<div class="page-head"><div><h1>Shopping Cart</h1><p class="muted">Choose a service and continue to checkout.</p></div><a class="btn btn-light" href="{{ route('dashboard') }}">Dashboard</a></div>
@if(session('success'))<div class="card" style="margin-bottom:16px">{{ session('success') }}</div>@endif
@if($errors->any())<div class="card" style="margin-bottom:16px;color:#b91c1c">{{ $errors->first() }}</div>@endif
@if($settings['checkout_notice'])<div class="card" style="margin-bottom:16px">{{ $settings['checkout_notice'] }}</div>@endif

<div class="grid-2">
<section>
@foreach($groups as $gid=>$items)
<div class="section-title"><h2>Available Products</h2></div>
<div class="grid" style="grid-template-columns:repeat(2,1fr)">
@foreach($items as $product)
@php
$pricing=$product['pricing'][$currency] ?? reset($product['pricing']) ?: [];
$cycles=collect(['monthly','quarterly','semiannually','annually','biennially','triennially','onetime'])->filter(fn($c)=>isset($pricing[$c]) && is_numeric($pricing[$c]) && (float)$pricing[$c]>=0);
$cycle=$cycles->first();
@endphp
<div class="card">
<h3>{{ $product['name'] ?? 'Product' }}</h3>
@if($settings['show_descriptions'] && !empty($product['description']))<div class="muted">{!! nl2br(e(strip_tags($product['description']))) !!}</div>@endif
@if($cycle)<div class="price">{{ $pricing['prefix'] ?? '' }}{{ number_format((float)$pricing[$cycle],2) }} {{ $pricing['suffix'] ?? $currency }}</div><div class="muted" style="margin-bottom:12px">Billed {{ $cycle }}</div>@endif
<form method="POST" action="{{ route('cart.add') }}">@csrf
<input type="hidden" name="pid" value="{{ $product['pid'] }}">
<select name="billingcycle" style="width:100%;padding:10px;margin-bottom:9px;border:1px solid #d1d5db;border-radius:8px">
@foreach($cycles as $c)<option value="{{ $c }}">{{ ucfirst($c) }} — {{ $pricing['prefix'] ?? '' }}{{ number_format((float)$pricing[$c],2) }} {{ $pricing['suffix'] ?? $currency }}</option>@endforeach
</select>
@if($settings['allow_quantity'])<input type="number" name="qty" value="1" min="1" max="20" style="width:70px;padding:9px;margin-bottom:9px">@endif
<button class="btn" type="submit">Add to Cart</button>
</form>
</div>
@endforeach
</div>
@endforeach
</section>

<aside class="card" style="height:max-content">
<div class="section-title"><h2>Your Cart</h2></div>
@forelse(session('billing_cart',[]) as $key=>$item)
<div style="padding:11px 0;border-bottom:1px solid #eee">
<strong>{{ $item['name'] ?? 'Product #'.$item['pid'] }}</strong>
<div class="muted">{{ ucfirst($item['billingcycle']) }} · Qty {{ $item['qty'] }}</div>
<form method="POST" action="{{ route('cart.remove',$key) }}" style="margin-top:7px">@csrf<button type="submit" class="btn btn-light">Remove</button></form>
</div>
@empty<p class="muted">Your cart is empty.</p>@endforelse
@if(session('billing_cart'))
<a class="btn" style="margin-top:16px;width:100%;text-align:center" href="{{ route('cart.checkout') }}">Continue to Checkout</a>
@endif
@if($settings['terms_url'])<p class="muted" style="margin-top:15px">By ordering you agree to <a href="{{ $settings['terms_url'] }}">Terms</a>.</p>@endif
</aside>
</div>
@endsection
