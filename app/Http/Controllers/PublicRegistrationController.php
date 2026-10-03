<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\GeneralSetting;
use App\Models\Page;
use App\Models\PublicRegistrationSetting;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use App\Services\DigitalSmsOtpService;
use App\Services\LoginOTPService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PublicRegistrationController extends Controller
{
    public function __construct(private DigitalSmsOtpService $sms, private LoginOTPService $loginOtp)
    {
    }

    public function create()
    {
        return view('front.join', $this->viewData());
    }

    public function store(Request $request)
    {
        abort_unless(PublicRegistrationSetting::current()->enabled, 403, 'Public registration is currently closed.');

        $data = $request->validate([
            'salutation' => ['nullable', Rule::in(['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Mx.'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:user_master,um_email_id'],
            'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_master,um_mobile_no'],
            'whatsapp' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'business_name' => ['required', 'string', 'max:255'],
            'category_ids' => ['required', 'array', 'min:1', 'max:3'],
            'category_ids.*' => ['integer', 'distinct', 'exists:business_category_master,bcm_id'],
            'business_email' => ['nullable', 'email:rfc', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:15'],
            'business_description' => ['nullable', 'string', 'max:3000'],
            'business_address_line_1' => ['nullable', 'string', 'max:255'],
            'business_address_line_2' => ['nullable', 'string', 'max:255'],
            'business_city' => ['nullable', 'string', 'max:100'],
            'business_country' => ['nullable', 'integer', 'exists:countries,id'],
            'business_state' => ['nullable', 'integer', Rule::exists('states', 'id')->where(fn ($query) => $query->where('country_id', $request->input('business_country')))],
            'business_pincode' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ], [
            'profile_photo.uploaded' => 'Your photo could not be uploaded. Choose a JPG, PNG or WebP image no larger than 5 MB.',
            'profile_photo.image' => 'Your photo must be a valid image file.',
            'profile_photo.mimes' => 'Your photo must be a JPG, PNG or WebP file.',
            'profile_photo.max' => 'Your photo must not be larger than 5 MB.',
        ]);

        $profilePhotoName = null;
        if ($request->hasFile('profile_photo')) {
            try {
                $directory = public_path('uploads/user');
                File::ensureDirectoryExists($directory);
                $profilePhotoName = Str::uuid().'.'.$request->file('profile_photo')->extension();
                $request->file('profile_photo')->move($directory, $profilePhotoName);
            } catch (\Throwable $exception) {
                report($exception);
                return back()->withInput()->withErrors([
                    'profile_photo' => 'Your photo could not be saved. Please try another image.',
                ]);
            }
        }

        try {
            $registrationNumber = DB::transaction(function () use ($data, $profilePhotoName) {
                $user = UserMaster::create([
                    'um_utm_id' => 2,
                    'um_email_id' => strtolower($data['email']),
                    'um_mobile_no' => $data['mobile'],
                    'um_password' => Hash::make(Str::random(40)),
                    'um_status' => 1,
                    'um_profile_type' => 'O',
                ]);
                $registrationNumber = 'EN'.str_pad((string) $user->um_id, 6, '0', STR_PAD_LEFT);
                $user->update(['um_user_name' => $registrationNumber]);

                UserDetails::create([
                    'ud_um_id' => $user->um_id,
                    'ud_salutation' => $data['salutation'] ?? null,
                    'ud_first_name' => $data['first_name'],
                    'ud_last_name' => $data['last_name'] ?? null,
                    'ud_whatsapp_no' => ($data['whatsapp'] ?? null) ?: $data['mobile'],
                    'ud_profile_image' => $profilePhotoName,
                ]);

                $company = CompaniesMaster::create([]);
                CompaniesDetail::create([
                    'cmpd_cmp_id' => $company->cmp_id,
                    'cmpd_name' => $data['business_name'],
                    'cmpd_description' => $data['business_description'] ?? 'Business profile pending approval.',
                    'cmpd_email' => $data['business_email'] ?? $data['email'],
                    'cmpd_phone' => $data['business_phone'] ?? $data['mobile'],
                    'cmpd_address1' => $data['business_address_line_1'] ?? null,
                    'cmpd_address2' => $data['business_address_line_2'] ?? null,
                    'cmpd_address3' => $data['business_city'] ?? null,
                    'cmpd_country' => $data['business_country'] ?? null,
                    'cmpd_state' => $data['business_state'] ?? null,
                    'cmpd_pincode' => $data['business_pincode'] ?? null,
                    'cmpd_status' => 0,
                    'cmpd_is_document_valid' => '0',
                ]);
                $company->users()->attach($user->um_id);
                $company->categories()->attach($data['category_ids']);

                return $registrationNumber;
            });
        } catch (\Throwable $exception) {
            if ($profilePhotoName) {
                File::delete(public_path('uploads/user/'.$profilePhotoName));
            }
            throw $exception;
        }

        return redirect()->route('join.create')->with([
            'registration_success' => $registrationNumber,
            'registration_requires_approval' => true,
        ]);
    }

    public function guestCreate(Request $request)
    {
        if (Auth::guard('member')->check()) {
            return redirect()->route('dashboard.index');
        }

        $redirect = (string) $request->query('redirect', '');
        if ($redirect !== '' && str_starts_with($redirect, url('/'))) {
            $request->session()->put('guest_registration_intended', $redirect);
        }

        return view('front.guest-register', array_merge($this->viewData(), [
            'title' => 'Guest registration · Net-Works',
            'guestMobile' => $request->session()->get('guest_registration_mobile'),
            'guestLoginMode' => $request->session()->get('guest_login_mode'),
            'guestLoginDestination' => $request->session()->get('guest_login_destination'),
            'guestVerified' => (bool) $request->session()->get('guest_registration_verified'),
        ]));
    }

    public function guestSendOtp(Request $request)
    {
        $data = $request->validate(['identifier' => ['required', 'string', 'max:255']]);
        $identifier = trim($data['identifier']);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        $mobile = preg_replace('/\D+/', '', $identifier);
        if (! $isEmail && ! preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
            throw ValidationException::withMessages(['identifier' => 'Enter a valid 10-digit mobile number or email address.']);
        }

        $user = $isEmail
            ? UserMaster::whereRaw('LOWER(um_email_id) = ?', [strtolower($identifier)])->first()
            : UserMaster::whereRaw("RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(um_mobile_no, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), 10) = ?", [$mobile])->first();
        if ($user) {
            if ((int) $user->um_status !== 2) {
                throw ValidationException::withMessages(['identifier' => 'This account is not active yet. Please contact the administrator.']);
            }
            $key = 'guest-login:send:'.$user->um_id.'|'.$request->ip();
            if (RateLimiter::tooManyAttempts($key, 3)) {
                throw ValidationException::withMessages(['identifier' => 'Too many attempts. Please try again later.']);
            }
            $this->loginOtp->generateOTP($user->um_id);
            if (app()->environment('testing')) {
                $request->session()->put('testing_guest_login_otp', $user->fresh()->um_otp);
            } elseif ($isEmail) {
                $this->loginOtp->sendEmailOTP($user->um_id);
            } else {
                $this->sms->send($user->um_mobile_no, (string) $user->fresh()->um_otp);
            }
            $request->session()->forget(['guest_registration_mobile', 'guest_registration_verified']);
            $request->session()->put([
                'guest_login_user_id' => $user->um_id,
                'guest_login_mode' => $isEmail ? 'email' : 'mobile',
                'guest_login_destination' => $isEmail ? $user->um_email_id : 'mobile ending in '.substr($mobile, -4),
            ]);
            RateLimiter::hit($key, 60);
            return redirect()->route('guest.register')->with('success', 'Account found. We sent you a login OTP.');
        }
        if ($isEmail) {
            throw ValidationException::withMessages(['identifier' => 'No account was found for this email. Enter your mobile number to register as a guest.']);
        }

        $key = 'guest-registration:send:'.$mobile.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages(['mobile' => 'Too many attempts. Please try again later.']);
        }

        $otp = (string) random_int(1000, 9999);
        if (!app()->environment('testing')) {
            try {
                $this->sms->send($mobile, $otp);
            } catch (\Throwable $exception) {
                throw ValidationException::withMessages(['mobile' => 'We could not send the OTP right now. Please try again.']);
            }
        }

        $request->session()->put([
            'guest_registration_mobile' => $mobile,
            'guest_registration_otp_hash' => Hash::make($otp),
            'guest_registration_otp_expires_at' => now()->addMinutes(10)->timestamp,
            'guest_registration_verified' => false,
        ]);
        if (app()->environment('testing')) {
            $request->session()->put('testing_guest_registration_otp', $otp);
        }
        RateLimiter::hit($key, 60);

        return redirect()->route('guest.register')->with('success', 'OTP sent to your mobile number.');
    }

    public function guestVerifyOtp(Request $request)
    {
        $data = $request->validate(['otp' => ['required', 'digits:4']]);
        $loginUserId = $request->session()->get('guest_login_user_id');
        if ($loginUserId) {
            $user = UserMaster::where('um_id', $loginUserId)->where('um_status', 2)->first();
            if (! $user || ! $this->loginOtp->verifyOTP($user->um_id, $data['otp'])) {
                throw ValidationException::withMessages(['otp' => 'The OTP is invalid or has expired.']);
            }
            Auth::guard('member')->login($user);
            $intended = $request->session()->pull('guest_registration_intended', route('dashboard.index'));
            $request->session()->forget(['guest_login_user_id', 'guest_login_mode', 'guest_login_destination', 'testing_guest_login_otp']);
            $request->session()->regenerate();
            return redirect()->to($intended)->with('success', 'You are now logged in.');
        }
        $hash = $request->session()->get('guest_registration_otp_hash');
        $expiresAt = (int) $request->session()->get('guest_registration_otp_expires_at');
        if (!$hash || !$expiresAt || Carbon::createFromTimestamp($expiresAt)->isPast() || !Hash::check($data['otp'], $hash)) {
            throw ValidationException::withMessages(['otp' => 'The OTP is invalid or has expired.']);
        }

        $request->session()->put('guest_registration_verified', true);
        $request->session()->forget(['guest_registration_otp_hash', 'guest_registration_otp_expires_at', 'testing_guest_registration_otp']);
        return redirect()->route('guest.register')->with('success', 'Mobile number verified. Complete your profile.');
    }

    public function guestStore(Request $request)
    {
        abort_unless($request->session()->get('guest_registration_verified'), 403, 'Verify your mobile number first.');
        $mobile = (string) $request->session()->get('guest_registration_mobile');
        $data = $request->validate([
            'salutation' => ['nullable', Rule::in(['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Mx.'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:user_master,um_email_id'],
            'country' => ['required', 'integer', 'exists:countries,id'],
            'state' => ['required', 'integer', Rule::exists('states', 'id')->where(fn ($query) => $query->where('country_id', $request->input('country')))],
            'consent' => ['accepted'],
        ]);

        if (UserMaster::where('um_mobile_no', $mobile)->exists()) {
            throw ValidationException::withMessages(['email' => 'This mobile number is already registered. Please sign in.']);
        }

        $guest = DB::transaction(function () use ($data, $mobile) {
            $guest = UserMaster::create([
                'um_utm_id' => 1,
                'um_email_id' => strtolower($data['email']),
                'um_mobile_no' => $mobile,
                'um_password' => Hash::make(Str::random(40)),
                'um_status' => 2,
                'um_profile_type' => 'G',
            ]);
            $guest->update(['um_user_name' => 'GU'.str_pad((string) $guest->um_id, 6, '0', STR_PAD_LEFT)]);
            UserDetails::create([
                'ud_um_id' => $guest->um_id,
                'ud_salutation' => $data['salutation'] ?? null,
                'ud_first_name' => $data['first_name'],
                'ud_last_name' => $data['last_name'] ?? null,
                'ud_whatsapp_no' => $mobile,
                'ud_country_id' => $data['country'],
                'ud_state_id' => $data['state'],
            ]);
            return $guest;
        });

        Auth::guard('member')->login($guest);
        $request->session()->regenerate();
        $intended = $request->session()->pull('guest_registration_intended', route('dashboard.index'));
        $request->session()->forget(['guest_registration_mobile', 'guest_registration_verified']);
        return redirect()->to($intended)->with('success', 'Your guest account is ready.');
    }

    private function viewData(): array
    {
        $generalSetting = GeneralSetting::find(1);
        $registrationSettings = PublicRegistrationSetting::current();

        return [
            'title' => 'Join Net-Works',
            'generalSetting' => $generalSetting,
            'registrationOpen' => $registrationSettings->enabled,
            'registrationSettings' => $registrationSettings,
            'countries' => Country::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'businessCategories' => BusinessCategoryMaster::where('status', 1)->orderBy('name')->get(['bcm_id', 'name']),
            'defaultCountryId' => Country::where('name', 'India')->value('id'),
            'headerNavigation' => $this->navigationFor('header'),
            'footerNavigation' => $this->navigationFor('footer'),
        ];
    }

    private function navigationFor(string $location)
    {
        return Page::with(['children' => fn ($query) => $query->where('status', 1)->whereIn('nav_location', [$location, 'both'])])
            ->whereNull('parent_id')->where('status', 1)->whereIn('nav_location', [$location, 'both'])
            ->orderBy('nav_order')->orderBy('page_name')->get();
    }
}
