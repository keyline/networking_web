<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = $request->user('member');
        abort_unless($member && $member->hasActiveBusinessMembership(), 403, 'An active Business Owner membership is required.');

        return $next($request);
    }
}
