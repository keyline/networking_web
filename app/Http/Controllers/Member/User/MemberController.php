<?php

namespace App\Http\Controllers\Member\User;

use App\Http\Controllers\Controller;
use App\Services\Member\MemberLoginService;
use App\Services\Member\GoogleMemberAuthService;
use App\Models\User\UserMaster;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    private const VIEW_PATH = 'Member.LoginPage.';
    private const MODULE = 'member';
    // private Service $service;
    public function __construct(private MemberLoginService $loginService, private GoogleMemberAuthService $googleAuth)
    {
        $this->shareViewData();
    }

    protected function shareViewData(): void
    {
        View::share([
            'module' => self::MODULE,
            'root_url' => route(self::MODULE . '.index')
        ]);
    }

    public function index(Request $request)
    {
        if ($request->boolean('reset')) {
            $request->session()->forget(['member_otp_email', 'member_otp_user_id', 'member_otp_mobile', 'testing_member_mobile_otp']);
        }
        return view(self::VIEW_PATH . 'login', [
            'title' => 'Login',
        ]);
    }

    public function show(string $id)
    {
        try {
            return view(self::VIEW_PATH . 'show', [
                'title' => ''
            ]);
        } catch (Exception $e) {
            Log::warning("show: Record not found for ID $id");
            return redirect()->route(self::MODULE . '.index')
                ->with('error', 'record not found');
        }
    }


    public function sendOtp(Request $request)
    {
        $user = $this->loginService->sendOtp($request);
        $request->session()->put('member_otp_email', $user->um_email_id);

        return redirect()->route('member.index')
            ->with('success', 'A 4-digit OTP has been sent to your email address.');
    }

    public function sendMobileOtp(Request $request)
    {
        $user = $this->loginService->sendMobileOtp($request);
        $request->session()->put([
            'member_otp_user_id' => $user->um_id,
            'member_otp_mobile' => $this->maskMobile($user->um_mobile_no),
        ]);
        return redirect()->route('member.index')->with('success', 'A 4-digit OTP has been sent to your mobile number.');
    }

    public function verifyMobileOtp(Request $request)
    {
        $this->loginService->verifyMobileOtp($request);
        $request->session()->forget(['member_otp_user_id', 'member_otp_mobile']);
        return redirect()->route('dashboard.index');
    }

    public function loginWithPassword(Request $request)
    {
        $this->loginService->loginWithPassword($request);
        return redirect()->intended(route('dashboard.index'));
    }

    public function googleRedirect(Request $request)
    {
        try {
            $state = Str::random(40);
            $request->session()->put('member_google_state', $state);
            return redirect()->away($this->googleAuth->authorizationUrl($state));
        } catch (ValidationException $exception) {
            return redirect()->route('member.index')->withErrors($exception->errors());
        }
    }

    public function googleCallback(Request $request)
    {
        $expected = (string) $request->session()->pull('member_google_state');
        if (!$expected || !$request->filled('state') || !hash_equals($expected, (string) $request->state)) {
            return redirect()->route('member.index')->withErrors(['google' => 'The Google login session expired. Please try again.']);
        }
        if ($request->filled('error') || !$request->filled('code')) {
            return redirect()->route('member.index')->withErrors(['google' => 'Google login was cancelled or could not be completed.']);
        }

        try {
            $profile = $this->googleAuth->profileFromCode((string) $request->code);
            $user = UserMaster::whereRaw('LOWER(um_email_id) = ?', [strtolower($profile['email'])])
                ->where('um_status', 2)->first();
            if (!$user) {
                return redirect()->route('member.index')->withErrors(['google' => 'No active Net-Works account uses this Google email address.']);
            }
            $user->update(['um_profile_type' => 'G', 'um_social_token' => $profile['sub'] ?? $user->um_social_token]);
            $this->loginService->authenticate($request, $user);
            return redirect()->intended(route('dashboard.index'));
        } catch (ValidationException $exception) {
            return redirect()->route('member.index')->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            Log::warning('Member Google login failed', ['message' => $exception->getMessage()]);
            return redirect()->route('member.index')->withErrors(['google' => 'Google login could not be completed. Please try another method.']);
        }
    }

    public function verifyOtp(Request $request)
    {
        $this->loginService->verifyOtp($request);
        $request->session()->forget('member_otp_email');

        return redirect()->route('dashboard.index');
    }

    public function resendOtp(Request $request)
    {
        return $request->filled('mobile') ? $this->sendMobileOtp($request) : $this->sendOtp($request);
    }

    /*

    public function create()
    {
        return view(self::VIEW_PATH . 'form', [
            'title' => 'Create User',
            'row' => null
        ]);
    }

    public function store(StoreRequest $request)
    {
        try {
            $this->service->createUser($request->validated());
            return redirect()->route(self::MODULE . '.index')
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'User creation failed. Please try again.');
        }
    }



    public function edit(string $id)
    {
        try {
            return view(self::VIEW_PATH . 'form', [
                'title' => 'Edit User',
                'row' => $this->service->findUserOrFail($id),
                'types' => $this->service->getUserTypes()
            ]);
        } catch (Exception $e) {
            Log::warning("User edit: Record not found for ID $id");
            return redirect()->route(self::MODULE . '.index')
                ->with('error', 'User not found');
        }
    }

    public function update(UpdateRequest $request, string $id)
    {
        try {
            $this->service->updateUser($id, $request->validated());
            return redirect()->route(self::MODULE . '.index')
                ->with('success', 'User updated successfully');
        } catch (Exception $e) {
            Log::warning("User update: Record not found for ID $id");
            return redirect()->route(self::MODULE . '.index')
                ->with('error', 'User not found');
        } catch (\Exception $e) {
            Log::error("User update failed: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'User update failed. Please try again.');
        }
    }

    */
    public function destroy(Request $request)
    {

        Auth::guard('member')->logout(); // Logout the user from the 'member' guard

        $request->session()->invalidate(); // Invalidate the session
        $request->session()->regenerateToken(); // Regenerate the CSRF token

        return redirect()->route('member.index'); // Redirect to login page after logout

        // try {
        //     $this->service->deleteUser($id);
        //     return redirect()->route(self::MODULE . '.index')
        //         ->with('success', 'User deleted successfully');
        // } catch (Exception $e) {
        //     Log::warning("User delete: Record not found for ID $id");
        //     return redirect()->route(self::MODULE . '.index')
        //         ->with('error', 'User not found');
        // } catch (\Exception $e) {
        //     Log::error("User deletion failed: " . $e->getMessage());
        //     return redirect()->back()
        //         ->with('error', 'User deletion failed. Please try again.');
        // }
    }

    private function maskMobile(?string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', (string) $mobile);
        return $digits ? str_repeat('•', max(strlen($digits) - 4, 0)).substr($digits, -4) : 'your registered mobile';
    }
}
