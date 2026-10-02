<?php

use App\Http\Middleware\PortalAuth;
use App\Http\Middleware\AdminAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'portal.auth' => PortalAuth::class,
            'admin.auth' => AdminAuth::class,
            'admin.permission' => AdminAuth::class,
        ]);

        $middleware->append(\Illuminate\Http\Middleware\TrustProxies::class);
        $middleware->append(function ($request, $next) {
            $response = $next($request);
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
            if ($request->isSecure()) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }
            return $response;
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
    })->create();
