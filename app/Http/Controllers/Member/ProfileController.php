<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use App\Services\DigitalSmsOtpService;
use App\Services\LoginOTPService;
use App\Services\Member\MemberLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private LoginOTPService $otpService, private DigitalSmsOtpService $digitalSms)
    {
    }

    public function show(Request $request): View
    {
        return view('Member.Profile.show', [
            'member' => $request->user('member')->load('userDetail'),
            'pendingContact' => $request->session()->get('member_profile_contact_change'),
            'otpDigits' => MemberLoginService::usesTestOtp() ? 6 : 4,
        ]);
    }

    public function updateBasics(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $member = $request->user('member');
        $detail = $member->userDetail ?: new UserDetails(['ud_um_id' => $member->um_id]);

        if ($request->hasFile('profile_photo')) {
            $directory = public_path('uploads/user');
            File::ensureDirectoryExists($directory);
            $newPhoto = Str::uuid().'.'.$request->file('profile_photo')->extension();
            $request->file('profile_photo')->move($directory, $newPhoto);
            $oldPhoto = $detail->ud_profile_image;
            $detail->ud_profile_image = $newPhoto;
            if ($oldPhoto) {
                File::delete($directory.DIRECTORY_SEPARATOR.basename($oldPhoto));
            }
        }

        $detail->ud_first_name = trim($data['first_name']);
        $detail->ud_last_name = trim((string) ($data['last_name'] ?? '')) ?: null;
        $detail->save();

        return back()->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $member = $request->user('member');
        if (! $member->um_password || ! Hash::check($data['current_password'], $member->um_password)) {
            throw ValidationException::withMessages(['current_password' => 'The current password is incorrect.']);
        }
        $member->update(['um_password' => Hash::make($data['password'])]);

        return back()->with('success', 'Your password has been changed.');
    }

    public function sendContactOtp(Request $request): RedirectResponse
    {
        $member = $request->user('member');
        $data = $request->validate([
            'contact_type' => ['required', Rule::in(['email', 'phone'])],
            'contact_value' => ['required', 'string', 'max:190'],
        ]);
        $type = $data['contact_type'];
        $value = $type === 'email'
            ? strtolower(trim($data['contact_value']))
            : preg_replace('/\D+/', '', $data['contact_value']);

        if ($type === 'email') {
            validator(['email' => $value], ['email' => ['required', 'email']])->validate();
            if (strcasecmp($value, (string) $member->um_email_id) === 0) {
                throw ValidationException::withMessages(['contact_value' => 'Enter a different email address.']);
            }
            if (UserMaster::whereRaw('LOWER(um_email_id) = ?', [$value])->where('um_id', '!=', $member->um_id)->exists()) {
                throw ValidationException::withMessages(['contact_value' => 'This email address is already in use.']);
            }
        } else {
            if (! preg_match('/^[6-9][0-9]{9}$/', $value)) {
                throw ValidationException::withMessages(['contact_value' => 'Enter a valid 10-digit Indian mobile number.']);
            }
            if (substr(preg_replace('/\D+/', '', (string) $member->um_mobile_no), -10) === $value) {
                throw ValidationException::withMessages(['contact_value' => 'Enter a different mobile number.']);
            }
            if (UserMaster::where('um_id', '!=', $member->um_id)->whereRaw(
                "RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(um_mobile_no, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), 10) = ?",
                [$value]
            )->exists()) {
                throw ValidationException::withMessages(['contact_value' => 'This mobile number is already in use.']);
            }
        }

        $otp = $this->otpService->generateOTP($member->um_id, MemberLoginService::usesTestOtp() ? '123456' : null);
        if (! MemberLoginService::usesTestOtp() && ! app()->environment('testing')) {
            try {
                $type === 'email'
                    ? $this->otpService->sendEmailOTPTo($member->um_id, $value)
                    : $this->digitalSms->send($value, $otp);
            } catch (\Throwable $exception) {
                report($exception);
                $member->update(['um_otp' => null, 'um_otp_secret' => null, 'um_otp_expires_at' => null]);
                throw ValidationException::withMessages(['contact_value' => 'We could not send the OTP right now. Please try again later.']);
            }
        }
        $request->session()->put('member_profile_contact_change', ['type' => $type, 'value' => $value]);

        return back()->with('success', 'We sent an OTP to your new '.$type.'.');
    }

    public function verifyContactOtp(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('member_profile_contact_change');
        if (! is_array($pending) || empty($pending['type']) || empty($pending['value'])) {
            throw ValidationException::withMessages(['otp' => 'Start a new email or phone change first.']);
        }
        $data = $request->validate(['otp' => ['required', 'digits:'.(MemberLoginService::usesTestOtp() ? 6 : 4)]]);
        $member = $request->user('member');
        if (! $this->otpService->verifyOTP($member->um_id, $data['otp'])) {
            throw ValidationException::withMessages(['otp' => 'The OTP is invalid or has expired.']);
        }

        $duplicate = $pending['type'] === 'email'
            ? UserMaster::whereRaw('LOWER(um_email_id) = ?', [strtolower($pending['value'])])->where('um_id', '!=', $member->um_id)->exists()
            : UserMaster::where('um_id', '!=', $member->um_id)->whereRaw(
                "RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(um_mobile_no, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), 10) = ?",
                [$pending['value']]
            )->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['otp' => 'That contact is now in use by another account.']);
        }

        $column = $pending['type'] === 'email' ? 'um_email_id' : 'um_mobile_no';
        $member->update([$column => $pending['value']]);
        $request->session()->forget('member_profile_contact_change');

        return back()->with('success', ucfirst($pending['type']).' updated successfully.');
    }
}
