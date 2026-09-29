<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(auth('admin')->check() && auth('admin')->user()->type === 'ma', 403, 'Super Admin access is required.');

        return $next($request);
    }
}
