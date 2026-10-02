<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use App\Services\DigitalSmsOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuestRegistrationController extends Controller
{
    public function __construct(private DigitalSmsOtpService $sms)
    {
    }

    public function sendOtp(Request $request): JsonResponse
    {
        if (!hash_equals((string) env('PROJECT_KEY'), (string) $request->header('key'))) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated request.', 'data' => null]);
        }
        $data = $request->validate(['mobile_no' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_master,um_mobile_no']]);
        $otp = (string) random_int(1000, 9999);
        Cache::put($this->otpKey($data['mobile_no']), Hash::make($otp), now()->addMinutes(10));
        if (!app()->environment('testing')) {
            $this->sms->send($data['mobile_no'], $otp);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your mobile number.',
            'data' => app()->environment('testing') ? ['testing_otp' => $otp] : null,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        if (!hash_equals((string) env('PROJECT_KEY'), (string) $request->header('key'))) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated request.', 'data' => null]);
        }
        $data = $request->validate([
            'mobile_no' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'otp' => ['required', 'digits:4'],
        ]);
        $hash = Cache::get($this->otpKey($data['mobile_no']));
        if (!$hash || !Hash::check($data['otp'], $hash)) {
            return response()->json(['status' => false, 'message' => 'The OTP is invalid or has expired.', 'data' => null]);
        }

        Cache::forget($this->otpKey($data['mobile_no']));
        $token = Str::random(64);
        Cache::put($this->verifiedKey($token), $data['mobile_no'], now()->addMinutes(20));
        return response()->json(['status' => true, 'message' => 'Mobile number verified.', 'data' => ['verification_token' => $token]]);
    }

    public function complete(Request $request): JsonResponse
    {
        if (!hash_equals((string) env('PROJECT_KEY'), (string) $request->header('key'))) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated request.', 'data' => null]);
        }
        $data = $request->validate([
            'verification_token' => ['required', 'string', 'size:64'],
            'salutation' => ['nullable', Rule::in(['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Mx.'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:user_master,um_email_id'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')->where(fn ($query) => $query->where('country_id', $request->input('country_id')))],
        ]);
        $mobile = Cache::pull($this->verifiedKey($data['verification_token']));
        if (!$mobile) {
            return response()->json(['status' => false, 'message' => 'Mobile verification has expired. Please verify again.', 'data' => null]);
        }
        if (UserMaster::where('um_mobile_no', $mobile)->exists()) {
            return response()->json(['status' => false, 'message' => 'This mobile number is already registered.', 'data' => null]);
        }

        $guest = DB::transaction(function () use ($data, $mobile) {
            $guest = UserMaster::create([
                'um_utm_id' => 1, 'um_email_id' => strtolower($data['email']),
                'um_mobile_no' => $mobile, 'um_password' => Hash::make(Str::random(40)),
                'um_status' => 2, 'um_profile_type' => 'G',
            ]);
            $guest->update(['um_user_name' => 'GU'.str_pad((string) $guest->um_id, 6, '0', STR_PAD_LEFT)]);
            UserDetails::create([
                'ud_um_id' => $guest->um_id, 'ud_first_name' => $data['first_name'],
                'ud_salutation' => $data['salutation'] ?? null,
                'ud_last_name' => $data['last_name'] ?? null, 'ud_whatsapp_no' => $mobile,
                'ud_country_id' => $data['country_id'], 'ud_state_id' => $data['state_id'],
            ]);
            return $guest;
        });

        return response()->json([
            'status' => true,
            'message' => 'Guest registration completed. You can now sign in with mobile OTP.',
            'data' => ['user_id' => $guest->um_id, 'mobile_no' => $guest->um_mobile_no, 'user_type_id' => 1],
        ]);
    }

    private function otpKey(string $mobile): string
    {
        return 'guest-registration:otp:'.$mobile;
    }

    private function verifiedKey(string $token): string
    {
        return 'guest-registration:verified:'.hash('sha256', $token);
    }
}
