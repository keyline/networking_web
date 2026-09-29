<?php

namespace App\Services\Member;

use App\Models\User\UserMaster;
use App\Services\LoginOTPService;
use App\Services\DigitalSmsOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;



class MemberLoginService
{
    // Maximum number of login attempts allowed.
    protected $maxAttempts = 5;
    // How many seconds to wait before another attempt is allowed.
    protected $decaySeconds = 60;

    public function __construct(private LoginOTPService $otpService, private DigitalSmsOtpService $digitalSms)
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
        try {
            $this->otpService->sendEmailOTP($user->um_id);
        } catch (\Throwable $exception) {
            Log::error('Member login email OTP could not be sent', [
                'user_id' => $user->um_id,
                'message' => $exception->getMessage(),
            ]);
            $this->clearOtp($user);
            throw ValidationException::withMessages([
                'email' => ['We could not send an email OTP right now. Please check the mail settings, use mobile OTP or password, or try again later.'],
            ]);
        }
        RateLimiter::hit($key, $this->decaySeconds);

        return $user->fresh();
    }

    public function sendMobileOtp(Request $request): UserMaster
    {
        $data = $request->validate(['mobile' => ['required', 'string', 'max:30']]);
        $mobile = preg_replace('/\D+/', '', $data['mobile']);
        if (strlen($mobile) < 10) {
            throw ValidationException::withMessages(['mobile' => ['Enter a valid registered mobile number.']]);
        }

        $lookup = substr($mobile, -10);
        $key = $this->throttleKey($lookup, $request->ip(), 'send-mobile');
        $this->ensureIsNotRateLimited($key, 3, 'mobile');
        $user = $this->activeUsers()->whereRaw(
            "RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(um_mobile_no, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), 10) = ?",
            [$lookup]
        )->first();

        if (!$user) {
            RateLimiter::hit($key, $this->decaySeconds);
            throw ValidationException::withMessages(['mobile' => ['No active account was found for this mobile number.']]);
        }

        $this->otpService->generateOTP($user->um_id);
        if (app()->environment('testing')) {
            $request->session()->put('testing_member_mobile_otp', $user->fresh()->um_otp);
        } else {
            try {
                $this->digitalSms->send($user->um_mobile_no, (string) $user->fresh()->um_otp);
            } catch (\Throwable $exception) {
                Log::error('Member login SMS could not be sent', [
                    'user_id' => $user->um_id,
                    'message' => $exception->getMessage(),
                ]);
                $this->clearOtp($user);
                throw ValidationException::withMessages([
                    'mobile' => ['We could not send an SMS right now. Please use email OTP or password, or try again later.'],
                ]);
            }
        }
        RateLimiter::hit($key, $this->decaySeconds);
        return $user->fresh();
    }

    public function loginWithPassword(Request $request): UserMaster
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $email = strtolower(trim($data['email']));
        $key = $this->throttleKey($email, $request->ip(), 'password');
        $this->ensureIsNotRateLimited($key, $this->maxAttempts);
        $user = $this->activeUsers()->whereRaw('LOWER(um_email_id) = ?', [$email])->first();

        if (!$user || !$user->um_password || !Hash::check($data['password'], $user->um_password)) {
            RateLimiter::hit($key, $this->decaySeconds);
            throw ValidationException::withMessages(['email' => ['The email address or password is incorrect.']]);
        }

        RateLimiter::clear($key);
        $this->authenticate($request, $user);
        return $user;
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

    public function verifyMobileOtp(Request $request): UserMaster
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'otp' => ['required', 'digits:4'],
        ]);
        $key = $this->throttleKey((string) $data['user_id'], $request->ip(), 'verify-mobile');
        $this->ensureIsNotRateLimited($key, $this->maxAttempts, 'otp');
        $user = $this->activeUsers()->where('um_id', $data['user_id'])->first();

        if (!$user || !$this->otpService->verifyOTP($user->um_id, $data['otp'])) {
            RateLimiter::hit($key, $this->decaySeconds);
            throw ValidationException::withMessages(['otp' => ['The OTP is invalid or has expired.']]);
        }

        RateLimiter::clear($key);
        $request->session()->forget('testing_member_mobile_otp');
        $this->authenticate($request, $user);
        return $user;
    }

    public function authenticate(Request $request, UserMaster $user): void
    {
        Auth::guard('member')->login($user);
        $request->session()->regenerate();
    }

    /**
     * Ensure the login attempt is not rate limited.
     */
    protected function ensureIsNotRateLimited(string $key, int $maxAttempts, string $field = 'email'): void
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                $field => ["Too many attempts. Please try again in {$seconds} seconds."],
            ]);
        }
    }

    private function activeUsers()
    {
        return UserMaster::query()->where('um_status', 2);
    }

    private function clearOtp(UserMaster $user): void
    {
        $user->update(['um_otp' => null, 'um_otp_secret' => null, 'um_otp_expires_at' => null]);
    }


    /**
     * Build a unique throttle key for the login attempt.
     */
    protected function throttleKey(string $email, string $ip, string $action): string
    {
        return "member-otp:{$action}:{$email}|{$ip}";
    }
}
