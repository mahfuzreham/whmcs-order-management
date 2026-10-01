@extends('layouts.app')
@section('content')
<div class="page-head">
    <div><h1>Checkout</h1><p class="muted">Review your order and continue to secure payment.</p></div>
    <a class="btn btn-light" href="{{ route('cart') }}">← Back to Cart</a>
</div>

@if($errors->any())<div class="card" style="margin-bottom:16px;color:#b91c1c">{{ $errors->first() }}</div>@endif
@if($notice)<div class="card" style="margin-bottom:16px">{{ $notice }}</div>@endif

<div class="grid-2">
    <section class="card">
        <div class="section-title"><h2>Order Summary</h2></div>
        <div class="list">
        @foreach($products as $product)
            <div class="row-card" style="padding:12px 0;border-bottom:1px solid #f0f1f4">
                <div>
                    <strong>{{ $product['name'] }}</strong>
                    <div class="muted">{{ ucfirst($product['billingcycle']) }} · Qty {{ $product['qty'] }}</div>
                </div>
                <strong>{{ $product['prefix'] }}{{ number_format($product['price']*$product['qty'],2) }} {{ $product['suffix'] }}</strong>
            </div>
        @endforeach
        </div>
        <p class="muted" style="margin-top:18px">Final taxes, promotions and WHMCS pricing rules are calculated by WHMCS when the order is created.</p>
    </section>

    <section class="card">
        <div class="section-title"><h2>Customer</h2></div>
        <div class="list">
            <div class="row-card"><span class="muted">Name</span><strong>{{ trim(($client['firstname'] ?? '').' '.($client['lastname'] ?? '')) ?: 'Customer' }}</strong></div>
            <div class="row-card"><span class="muted">Email</span><strong>{{ $client['email'] ?? '—' }}</strong></div>
            <div class="row-card"><span class="muted">Phone</span><strong>{{ $client['phonenumber'] ?? '—' }}</strong></div>
        </div>

        <form method="POST" action="{{ route('cart.checkout.place') }}" style="margin-top:22px">
            @csrf
            <label style="display:block;margin-bottom:7px;font-weight:650">Payment Method</label>
            <select name="paymentmethod" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:9px">
                <option value="bkash">bKash</option>
            </select>

            <label style="display:block;margin:16px 0 7px;font-weight:650">Promo Code <span class="muted">(optional)</span></label>
            <input name="promo_code" value="{{ old('promo_code') }}" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:9px">

            <label style="display:flex;gap:9px;align-items:flex-start;margin:16px 0">
                <input type="checkbox" required style="margin-top:4px">
                <span>I confirm the order details and agree to the billing terms.</span>
            </label>

            @if($termsUrl)<div class="muted" style="margin-bottom:14px"><a href="{{ $termsUrl }}" target="_blank">Read Terms & Conditions</a></div>@endif
            <button class="btn" type="submit" style="width:100%">Place Order & Continue to Payment</button>
        </form>
    </section>
</div>
@endsection
