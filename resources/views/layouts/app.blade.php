<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Billing Portal' }}</title>
    <style>
        body{margin:0;font-family:Inter,system-ui,sans-serif;background:#f5f7fb;color:#172033}
        .nav{background:#111827;color:#fff;padding:16px 5%;display:flex;justify-content:space-between;align-items:center}
        .wrap{max-width:1100px;margin:35px auto;padding:0 20px}
        .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px;box-shadow:0 4px 20px #00000008}
        .muted{color:#6b7280}.value{font-size:30px;font-weight:700;margin-top:8px}
        button{border:0;background:#fff;color:#111827;cursor:pointer}
        @media(max-width:700px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<nav class="nav">
    <strong>Billing Portal</strong>
    @if(session()->has('whmcs_client'))
        <form method="POST" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
    @endif
</nav>
<main class="wrap">@yield('content')</main>
</body>
</html>
