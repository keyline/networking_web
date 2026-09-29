<?php

namespace App\Http\Controllers\Member\User;

use App\Http\Controllers\Controller;
use App\Services\Member\MemberLoginService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class MemberController extends Controller
{
    private const VIEW_PATH = 'Member.LoginPage.';
    private const MODULE = 'member';
    // private Service $service;
    public function __construct(private MemberLoginService $loginService)
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

    public function index()
    {
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

    public function verifyOtp(Request $request)
    {
        $this->loginService->verifyOtp($request);
        $request->session()->forget('member_otp_email');

        return redirect()->route('dashboard.index');
    }

    public function resendOtp(Request $request)
    {
        return $this->sendOtp($request);
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
}
