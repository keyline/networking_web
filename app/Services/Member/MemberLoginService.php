<?php

namespace App\Services\Member;

use App\Models\User\UserMaster;
use App\Services\LoginOTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;



class MemberLoginService
{
    // Maximum number of login attempts allowed.
    protected $maxAttempts = 5;
    // How many seconds to wait before another attempt is allowed.
    protected $decaySeconds = 60;

    public function __construct(private LoginOTPService $otpService)
    {
    }

    /**
     * Handle the login request.
     */
    public function sendOtp(Request $request): UserMaster
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = strtolower(trim($data['email']));
        $key = $this->throttleKey($email, $request->ip(), 'send');

        $this->ensureIsNotRateLimited($key, 3);

        $user = UserMaster::whereRaw('LOWER(um_email_id) = ?', [$email])
            ->where('um_status', 2)
            ->first();

        if (!$user) {
            RateLimiter::hit($key, $this->decaySeconds);
            throw ValidationException::withMessages([
                'email' => ['No active account was found for this email address.'],
            ]);
        }

        $this->otpService->generateOTP($user->um_id);
        $this->otpService->sendEmailOTP($user->um_id);
        RateLimiter::hit($key, $this->decaySeconds);

        return $user->fresh();
    }

    public function verifyOtp(Request $request): UserMaster
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:4'],
        ]);
        $email = strtolower(trim($data['email']));
        $key = $this->throttleKey($email, $request->ip(), 'verify');

        $this->ensureIsNotRateLimited($key, $this->maxAttempts);

        $user = UserMaster::whereRaw('LOWER(um_email_id) = ?', [$email])
            ->where('um_status', 2)
            ->first();

        if (!$user || !$this->otpService->verifyOTP($user->um_id, $data['otp'])) {
            RateLimiter::hit($key, $this->decaySeconds);
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or has expired.'],
            ]);
        }

        RateLimiter::clear($key);
        Auth::guard('member')->login($user);
        $request->session()->regenerate();

        return $user;
    }

    /**
     * Ensure the login attempt is not rate limited.
     */
    protected function ensureIsNotRateLimited(string $key, int $maxAttempts): void
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Too many attempts. Please try again in {$seconds} seconds."],
            ]);
        }
    }


    /**
     * Build a unique throttle key for the login attempt.
     */
    protected function throttleKey(string $email, string $ip, string $action): string
    {
        return "member-otp:{$action}:{$email}|{$ip}";
    }
}
