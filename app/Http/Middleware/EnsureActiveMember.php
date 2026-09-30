<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User\UserMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = Auth::guard('member')->user();
        $isActive = $member && UserMaster::where('um_id', $member->um_id)
            ->where('um_status', 2)
            ->exists();

        if (!$isActive) {
            Auth::guard('member')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('member.index')
                ->withErrors(['account' => 'Your account is awaiting approval or is no longer active.']);
        }

        return $next($request);
    }
}
