<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ResellNom Billing' }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif;background:#f6f8fc;color:#172033}
        a{color:inherit}
        .topbar{height:64px;background:#111827;color:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 24px;position:sticky;top:0;z-index:20}
        .brand{font-size:18px;font-weight:800;text-decoration:none}
        .top-actions{display:flex;align-items:center;gap:16px}
        .top-actions a{color:#dbe3f0;text-decoration:none;font-size:14px}
        .layout{display:flex;min-height:calc(100vh - 64px)}
        .sidebar{width:235px;background:#fff;border-right:1px solid #e5e7eb;padding:22px 14px;flex:none}
        .menu-title{font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;padding:10px 12px 7px}
        .menu{display:grid;gap:4px}
        .menu a{display:flex;align-items:center;gap:10px;text-decoration:none;color:#4b5563;padding:11px 12px;border-radius:10px;font-size:14px}
        .menu a:hover,.menu a.active{background:#eef2ff;color:#3730a3;font-weight:650}
        .content{width:100%;min-width:0}
        .wrap{max-width:1180px;margin:0 auto;padding:30px 24px}
        .page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;margin-bottom:22px}
        .page-head h1{margin:0 0 6px;font-size:27px}
        .muted{color:#6b7280}
        .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
        .grid-2{display:grid;grid-template-columns:1.35fr 1fr;gap:16px}
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;box-shadow:0 3px 16px #11182708}
        .stat{min-height:118px}
        .stat .label{color:#6b7280;font-size:13px}
        .stat .value{font-size:28px;font-weight:800;margin-top:9px}
        .links{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 22px}
        .links a{background:#fff;border:1px solid #e5e7eb;padding:9px 13px;border-radius:9px;text-decoration:none;font-size:14px}
        .row-card{display:flex;justify-content:space-between;align-items:center;gap:16px}
        .list{display:grid;gap:10px}
        .badge{display:inline-block;background:#eef2ff;color:#3730a3;padding:5px 9px;border-radius:999px;font-size:12px}
        .btn{display:inline-block;background:#111827;color:#fff;text-decoration:none;border:0;border-radius:9px;padding:10px 15px;cursor:pointer}
        .btn-light{background:#f3f4f6;color:#172033}
        button{font:inherit;border:0;cursor:pointer}
        .section-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:13px}
        .section-title h2{font-size:17px;margin:0}
        .empty{padding:20px;text-align:center;color:#6b7280}
        .mobile-menu{display:none}
        @media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}.grid-2{grid-template-columns:1fr}.sidebar{width:205px}}
        @media(max-width:700px){
            .topbar{padding:0 15px}.top-actions a{display:none}.mobile-menu{display:block}
            .layout{display:block}.sidebar{width:100%;border-right:0;border-bottom:1px solid #e5e7eb;padding:10px;display:none}
            .sidebar.open{display:block}.menu{grid-template-columns:repeat(2,1fr)}
            .menu-title{grid-column:1/-1}.wrap{padding:22px 14px}.grid{grid-template-columns:1fr 1fr}.card{padding:16px}
            .page-head h1{font-size:23px}
        }
        @media(max-width:430px){.grid{grid-template-columns:1fr}.menu{grid-template-columns:1fr}.row-card{align-items:flex-start;flex-direction:column}}
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('dashboard') }}">ResellNom Billing</a>
    <div class="top-actions">
        @if(session()->has('whmcs_client'))
            <a href="{{ route('cart') }}">Cart</a>
            <a href="{{ route('tickets') }}">Support</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button style="background:none;color:#dbe3f0">Logout</button></form>
        @elseif(session()->has('admin_staff'))
            <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
        @endif
        <button class="mobile-menu" type="button" onclick="document.querySelector('.sidebar').classList.toggle('open')" style="background:none;color:#fff;font-size:22px">☰</button>
    </div>
</header>

@if(session()->has('whmcs_client'))
<aside class="sidebar">
    <div class="menu-title">Account</div>
    <nav class="menu">
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Overview</a>
        <a class="{{ request()->routeIs('services','service') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
        <a class="{{ request()->routeIs('domains') ? 'active' : '' }}" href="{{ route('domains') }}">Domains</a>
        <a class="{{ request()->routeIs('orders') ? 'active' : '' }}" href="{{ route('orders') }}">Orders</a>
        <a class="{{ request()->routeIs('invoices','invoice','payment.show') ? 'active' : '' }}" href="{{ route('invoices') }}">Invoices</a>
        <a class="{{ request()->routeIs('transactions') ? 'active' : '' }}" href="{{ route('transactions') }}">Transactions</a>
        <a class="{{ request()->routeIs('tickets','ticket') ? 'active' : '' }}" href="{{ route('tickets') }}">Support Tickets</a>
        <a class="{{ request()->routeIs('hosting','hosting.service') ? 'active' : '' }}" href="{{ route('hosting') }}">Web Hosting</a>
        @if(\App\Models\AdminSetting::bool('reseller_enabled',false))<a class="{{ request()->routeIs('reseller*') ? 'active' : '' }}" href="{{ route('reseller') }}">Reseller Hosting</a>@endif
    </nav>
    <div class="menu-title" style="margin-top:18px">Shop</div>
    <nav class="menu">
        <a href="{{ route('cart') }}">🛒 Shopping Cart</a>
    </nav>
</aside>
@endif

<main class="content"><div class="wrap">@yield('content')</div></main>
</body>
</html>
