<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        $staff = session('admin_staff');
        if (!$staff) return redirect()->route('admin.login');
        if ($permission && !in_array($permission, $staff['permissions'] ?? [], true) && ($staff['role'] ?? '') !== 'super_admin') {
            abort(403);
        }
        return $next($request);
    }
}
