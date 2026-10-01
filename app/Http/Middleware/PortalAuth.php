<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('whmcs_client')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
