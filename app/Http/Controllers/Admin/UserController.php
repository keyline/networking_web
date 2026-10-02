<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

use App\Models\Admin;
use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\Center;
use App\Models\CenterTimeSlot;
use App\Models\Student;
use App\Models\GeneralSetting;
use App\Models\EmailLog;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\Banner;
use App\Models\Teacher;
use App\Models\GalleryCategory;
use App\Models\Gallery;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Enquiry;
use App\Models\UserActivity;
use App\Models\MembershipPlan;
use App\Models\MembershipSetting;
use App\Models\Source;
use App\Models\Notice;

use Auth;
use Mail;
use App\Mail\ForgotPwdMail;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\ClientCheckIn;
use App\Models\ClientOrder;
use App\Models\ClientType;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Models\User\UserMaster;
use App\Models\PublicRegistrationSetting;
use Dompdf\Dompdf;
use PDF;
use Session;
use Helper;
use Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\TextUI\Help;

class UserController extends Controller
{
    /* authentication */
    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'max:255'],
            ]);
            $admin = Admin::where('status', 1)->where('email', $credentials['email'])->first();

            if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
                return back()->withInput($request->only('email'))->with('error_message', 'The email or password is incorrect.');
            }

            if ($admin->type !== 'ma' && $admin->user_master_id && !UserMaster::query()
                ->where('um_id', $admin->user_master_id)
                ->where('um_status', 2)
                ->whereHas('companies')
                ->exists()) {
                return back()->withInput($request->only('email'))->with('error_message', 'Administrative access is not available for this account.');
            }

            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
            if (Schema::hasColumn('admins', 'last_login_at')) {
                $admin->update(['last_login_at' => now()]);
            }
            $this->establishAdminSession($request, $admin);
            $this->recordAdminLoginActivity($request, $admin, 'Email and password');

            return redirect('admin/dashboard');
        }
        $data                           = [];
        $title                          = 'Sign In';
        $page_name                      = 'signin';
        echo $this->admin_before_login_layout($title, $page_name, $data);
    }

    private function establishAdminSession(Request $request, Admin $admin): void
    {
        $request->session()->forget(['admin_otp_admin_id', 'admin_otp_channel', 'admin_otp_display', 'testing_admin_mobile_otp']);
        $request->session()->put([
            'user_id' => $admin->id,
            'name' => $admin->name,
            'type' => $admin->type,
            'email' => $admin->email,
            'company_id' => $admin->company_id,
            'is_admin_login' => 1,
            'admin_login_source' => 'credentials',
        ]);
    }

    private function recordAdminLoginActivity(Request $request, Admin $admin, string $method): void
    {
        UserActivity::insert([
            'user_email' => $admin->email,
            'user_name' => $admin->name,
            'user_type' => 'ADMIN',
            'ip_address' => $request->ip(),
            'activity_type' => 1,
            'activity_details' => 'Login Success via '.$method,
            'platform_type' => 'WEB',
        ]);
    }

    private function maskEmail(?string $email): string
    {
        if (!$email || !str_contains($email, '@')) return 'your registered email';
        [$name, $domain] = explode('@', $email, 2);
        return substr($name, 0, 2).str_repeat('•', max(2, strlen($name) - 2)).'@'.$domain;
    }

    private function maskMobile(?string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', (string) $mobile);
        return $digits ? str_repeat('•', max(0, strlen($digits) - 4)).substr($digits, -4) : 'your registered mobile';
    }
    public function forgotPassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $postData = $request->all();
            $rules = [
                'email' => 'required|email|max:255',
            ];
            if ($this->validate($request, $rules)) {
                $checkEmail                   = Admin::where('email', '=', $postData['email'])->get();
                if (count($checkEmail) > 0) {
                    $row     =  Admin::where('email', '=', $postData['email'])->first();
                    $otp     =  rand(999, 10000);
                    $fields  =  [
                        'remember_token' => $otp
                    ];
                    Admin::where('id', '=', $row->id)->update($fields);
                    $to = $row->email;
                    $subject = "Reset Password";
                    $message = "Your Reset Password is :" . $otp;
                    // $this->sendMail('avijit@keylines.net',$subject,$message);
                    return redirect('/admin/validateOtp/' . Helper::encoded($row->id))->with('success_message', 'OTP Sent To Your Registered Email !!!');
                } else {
                    return redirect()->back()->with('error_message', 'We Don\'t Recognized Your Email !!!');
                }
            } else {
                return redirect()->back()->with('error_message', 'All Fields Required !!!');
            }
        }
        $data                           = [];
        $title                          = 'Forgot Password';
        $page_name                      = 'forgot-password';
        echo $this->admin_before_login_layout($title, $page_name, $data);
    }
    public function validateOtp(Request $request, $id)
    {
        $id                             = Helper::decoded($id);
        $data['id']                     = $id;
        $checkUser                      = Admin::where('id', '=', $id)->first();
        $data['email']                  = (($checkUser) ? $checkUser->email : '');

        if ($request->isMethod('post')) {
            $postData = $request->all();
            $rules = [
                'otp1'     => 'required|max:1',
                'otp2'     => 'required|max:1',
                'otp3'     => 'required|max:1',
                'otp4'     => 'required|max:1',
            ];
            if ($this->validate($request, $rules)) {
                // $id     = $postData['id'];
                $otp1   = $postData['otp1'];
                $otp2   = $postData['otp2'];
                $otp3   = $postData['otp3'];
                $otp4   = $postData['otp4'];
                $newotp    = ($otp1 . $otp2 . $otp3 . $otp4);
                $checkUser = Admin::where('id', '=', $id)->first();
                if ($checkUser) {
                    $otp = $checkUser->remember_token;
                    if ($otp == $newotp) {
                        $postData = [
                            'remember_token'        => '',
                        ];
                        Admin::where('id', '=', $checkUser->id)->update($postData);
                        return redirect('/admin/changePassword/' . Helper::encoded($checkUser->id))->with('success_message', 'OTP Validated. Just Reset Your Password !!!');
                    } else {
                        return redirect()->back()->with('error_message', 'OTP Mismatched !!!');
                    }
                } else {
                    return redirect()->back()->with('error_message', 'We Don\'t Recognize You !!!');
                }
            } else {
                return redirect()->back()->with('error_message', 'All Fields Required !!!');
            }
        }
        $title                          = 'Validate OTP';
        $page_name                      = 'validotp';
        echo $this->admin_before_login_layout($title, $page_name, $data);
    }
    public function resendOtp(Request $request, $email)
    {
        $email                          = Helper::decoded($email);
        $checkEmail                     = Admin::where('email', '=', $email)->first();
        if (count($checkEmail) > 0) {
            $row     =  Admin::where('email', '=', $email)->first();
            $otp     =  rand(999, 10000);
            $fields  =  [
                'remember_token' => $otp
            ];
            Admin::where('id', '=', $row->id)->update($fields);
            $to = $row->email;
            $subject = "Reset Password";
            $message = "Your Reset Password is :" . $otp;
            // $this->sendMail('avijit@keylines.net',$subject,$message);
            return redirect('/admin/validateOtp/' . Helper::encoded($row->id))->with('success_message', 'OTP Resend To Your Registered Email !!!');
        } else {
            return redirect()->back()->with('error_message', 'We Don\'t Recognized Your Email !!!');
        }
    }
    public function changePassword(Request $request, $id)
    {
        $ID = Helper::decoded($id);
        if ($request->isMethod('post')) {
            $postData = $request->all();
            $getAdmin                     = Admin::where('id', '=', $ID)->first();
            if ($postData['new_password'] != $postData['confirm_password']) {
                return redirect()->back()->with('error_message', 'Password Doesn\'t match !!!');
            } else {
                if (!Hash::check($postData['new_password'], $getAdmin->password)) {
                    $postData = [
                        'password'        => Hash::make($postData['new_password']),
                    ];
                    Admin::where('id', '=', $ID)->update($postData);
                    return redirect('/admin')->with('success_message', 'Password Reset Successfully. Please Sign In !!!');
                } else {
                    return redirect()->back()->with('error_message', 'New Password Can\'t Be Same With Existing Password !!!');
                }
            }
        }
        $data                           = [];
        $title                          = 'Reset Password';
        $page_name                      = 'reset-password';
        echo $this->admin_before_login_layout($title, $page_name, $data);
    }
    public function logout(Request $request)
    {
        $user_email                             = $request->session()->get('email');
        $user_name                              = $request->session()->get('name');
        /* user activity */
        $activityData = [
            'user_email'        => $user_email,
            'user_name'         => $user_name,
            'user_type'         => 'ADMIN',
            'ip_address'        => $request->ip(),
            'activity_type'     => 2,
            'activity_details'  => 'You Are Successfully Logged Out !!!',
            'platform_type'     => 'WEB',
        ];
        UserActivity::insert($activityData);
        /* user activity */
        $request->session()->forget(['user_id', 'name', 'email', 'admin_login_source']);
        // Helper::pr(session()->all());die;
        Auth::guard('admin')->logout();
        return redirect()->back()->with('success_message', 'You Are Successfully Logged Out !!!');
    }
    /* authentication */
    /* dashboard */
    public function dashboard()
    {
        $today                = date('Y-m-d');
        $data['totalattandence']        = Attendance::where('attendance_date', '=', $today)->count();
        $data['totalorder']             = ClientOrder::where('order_timestamp', 'LIKE', '%' . $today . '%')->count();
        $data['totalordervalue']        = ClientOrder::where('order_timestamp', 'LIKE', '%' . $today . '%')->sum('net_total');
        $data['todaydistributor']       = ClientCheckIn::where('client_type_id', '=', 1)->where('checkin_timestamp', 'LIKE', '%' . $today . '%')->count();
        $data['todaydealer']            = ClientCheckIn::where('client_type_id', '=', 2)->where('checkin_timestamp', 'LIKE', '%' . $today . '%')->count();
        $data['todayretailer']          = ClientCheckIn::where('client_type_id', '=', 3)->where('checkin_timestamp', 'LIKE', '%' . $today . '%')->count();
        $data['todayfarmer']            = ClientCheckIn::where('client_type_id', '=', 4)->where('checkin_timestamp', 'LIKE', '%' . $today . '%')->count();
        $data['totaldistributor']       = Client::where('client_type_id', '=', 1)->count();
        $data['totaldealer']            = Client::where('client_type_id', '=', 2)->count();
        $data['totalretailer']          = Client::where('client_type_id', '=', 3)->count();
        $data['totalfarmer']            = Client::where('client_type_id', '=', 4)->count();

        $data['totalGuests']                 = UserMaster::where('um_utm_id', '=', 1)->count();
        $data['totalMembers']                = UserMaster::where('um_utm_id', '=', 2)->count();
        $data['totalTypes']                  = DB::table('business_category_master')->count();
        $data['totalBusiness']               = DB::table('companies_master')->count();
        $data['registrationSettings']        = PublicRegistrationSetting::current();
        $data['pendingRegistrations']        = UserMaster::query()
            ->with([
                'userDetail',
                'companies.details.country:id,name',
                'companies.details.state:id,name',
                'companies.categories:bcm_id,name',
            ])
            ->whereHas('companies')
            ->where(function ($query) {
                $query->where('um_status', '!=', 2)
                    ->orWhereHas('companies.details', fn ($business) => $business->where('cmpd_status', '!=', 1));
            })
            ->orderByDesc('um_id')
            ->limit(12)
            ->get();
        $title                          = 'Dashboard';
        $page_name                      = 'dashboard';

        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    public function todayattandenceDetails(Request $request)
    {
        $apiStatus          = TRUE;
        $apiMessage         = 'Data Available !!!';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $postData           = $request->all();
        $attn_date          = date('Y-m-d');
        $attnDatas          = [];
        $attnList           = Attendance::where('attendance_date', '=', $attn_date)->orderBy('id', 'ASC')->get();
        // Helper::pr($attnList);
        $tot_attn_time      = 0;
        $isPresent          = 0;

        if ($attnList) {
            foreach ($attnList as $attnRow) {
                $attnDatas[]          = [
                    'image'                 => env('UPLOADS_URL') . 'user/' . $attnRow->start_image,
                    'name'                  => Employees::where('id', '=', $attnRow->employee_id)->first()->name,
                    'emp_type'              => EmployeeType::where('id', '=', $attnRow->employee_type_id)->first()->prefix,
                    'address'               => (($attnRow->start_address != '') ? $attnRow->start_address : ''),
                    'time'                  => date_format(date_create($attnRow->start_timestamp), "h:i A"),
                ];
            }
        }
        $data        = [
            'attnDatas'             => $attnDatas,
        ];
        echo $modalHTML = view('admin.maincontents.attandence-modal', $data);
        die;
    }
    public function todayorderDetails(Request $request)
    {
        $apiStatus          = TRUE;
        $apiMessage         = 'Data Available !!!';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $postData           = $request->all();
        $attn_date          = date('Y-m-d');
        $orderDatas          = [];
        $orderList           = ClientOrder::where('order_timestamp', 'LIKE', '%' . $attn_date . '%')->orderBy('id', 'ASC')->get();
        //  Helper::pr($orderList);

        if ($orderList) {
            foreach ($orderList as $orderRow) {
                $orderDatas[]          = [
                    'image'                 => env('UPLOADS_URL') . 'user/' . json_decode($orderRow->order_images)[0],
                    'client_name'           => Client::where('id', '=', $orderRow->client_id)->first()->name,
                    'client_type'           => ClientType::where('id', '=', $orderRow->client_type_id)->first()->name,
                    'client_address'        => Client::where('id', '=', $orderRow->client_id)->first()->address,
                    'emp_name'              => Employees::where('id', '=', $orderRow->employee_id)->first()->name,
                    'emp_type'              => EmployeeType::where('id', '=', $orderRow->employee_type_id)->first()->prefix,
                    'order_no'              => $orderRow->order_no,
                    'net_total'             => $orderRow->net_total,
                    'time'                  => date_format(date_create($orderRow->order_timestamp), "h:i A"),
                ];
            }
        }
        $data        = [
            'orderDatas'             => $orderDatas,
        ];
        echo $modalHTML = view('admin.maincontents.order-modal', $data);
        die;
    }
    public function todayclientDetails(Request $request)
    {
        $apiStatus          = TRUE;
        $apiMessage         = 'Data Available !!!';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $postData           = $request->all();
        $attn_date          = date('Y-m-d');
        $clienttype         = $postData['clienttype'];
        $clientDatas          = [];
        $clientList           = ClientCheckIn::where('checkin_timestamp', 'LIKE', '%' . $attn_date . '%')->where('client_type_id', '=', $clienttype)->orderBy('id', 'ASC')->get();
        //   Helper::pr($clientList);

        if ($clientList) {
            foreach ($clientList as $clientRow) {
                $employee_with_name = [];
                $employee_with_id   = json_decode($clientRow->employee_with_id);
                if (!empty($employee_with_id)) {
                    for ($k = 0; $k < count($employee_with_id); $k++) {
                        $getEmployee = DB::table('employees')
                            ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                            ->select('employees.name as employee_name', 'employee_types.prefix as employee_type_prefix')
                            ->where('employees.id', '=', $employee_with_id[$k])
                            ->first();
                        if ($getEmployee) {
                            $employee_with_name[] = $getEmployee->employee_name . ' (' . $getEmployee->employee_type_prefix . ')';
                        }
                    }
                }
                $clientDatas[]          = [
                    'image'                 => env('UPLOADS_URL') . 'user/' . $clientRow->checkin_image,
                    'client_name'           => Client::where('id', '=', $clientRow->client_id)->first()->name,
                    'client_type'           => ClientType::where('id', '=', $clientRow->client_type_id)->first()->name,
                    'client_address'        => Client::where('id', '=', $clientRow->client_id)->first()->address,
                    'emp_name'              => Employees::where('id', '=', $clientRow->employee_id)->first()->name,
                    'emp_type'              => EmployeeType::where('id', '=', $clientRow->employee_type_id)->first()->prefix,
                    'wi_emp_name'           => $employee_with_name ?? '',
                    // 'wi_emp_type'           => $wi_emp_type ?? '',
                    'note'                  => $clientRow->note,
                    'time'                  => date_format(date_create($clientRow->checkin_timestamp), "h:i A"),
                ];
            }
        }
        $data        = [
            'clientDatas'             => $clientDatas,
        ];
        //  Helper::pr($data);
        echo $modalHTML = view('admin.maincontents.client-modal', $data);
        die;
    }
    /* dashboard */
    /* settings */
    public function settings(Request $request)
    {
        $uId                            = $request->session()->get('user_id');
        $data['setting']                = GeneralSetting::where('id', '=', 1)->first();
        $data['admin']                  = Admin::where('id', '=', $uId)->first();
        $data['membershipPlans']        = MembershipPlan::orderBy('duration_months')->get();
        $data['membershipSetting']      = MembershipSetting::firstOrCreate(['id' => 1], ['renewal_basis' => 'joining_date']);
        $title                          = 'Settings';
        $page_name                      = 'settings';
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    public function profile_settings(Request $request)
    {
        $uId        = $request->session()->get('user_id');
        $postData   = $request->all();
        $rules      = [
            'name'            => 'required',
            'mobile'          => 'required',
            'email'           => 'required',
            'site_logo'       => 'nullable|file|mimes:jpg,jpeg,png,webp,ico|max:2048',
        ];
        if ($this->validate($request, $rules)) {
            $fields = [
                'name'                  => $postData['name'],
                'mobile'                => $postData['mobile'],
                'email'                 => $postData['email'],
            ];
            Admin::where('id', '=', $uId)->update($fields);

            $generalSetting = GeneralSetting::findOrFail(1);
            $siteLogo = $this->storeSettingImage($request, 'site_logo', $generalSetting->site_logo);
            GeneralSetting::where('id', 1)->update(['site_logo' => $siteLogo]);

            return redirect()->back()->with('success_message', 'Profile and site logo updated successfully.');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }
    public function general_settings(Request $request)
    {
        $row        = GeneralSetting::where('id', '=', 1)->first();
        $postData   = $request->all();
        $rules      = [
            'site_name'            => 'required',
            'site_phone'           => 'required',
            'site_mail'            => 'required',
            'system_email'         => 'required',
            'site_url'             => 'required',
            'site_logo'            => 'nullable|file|mimes:jpg,jpeg,png,webp,ico|max:2048',
            'site_footer_logo'     => 'nullable|file|mimes:jpg,jpeg,png,webp,ico|max:2048',
            'site_favicon'         => 'nullable|file|mimes:jpg,jpeg,png,webp,ico|max:1024',
        ];
        if ($this->validate($request, $rules)) {
            $site_logo = $this->storeSettingImage($request, 'site_logo', $row->site_logo);
            $site_footer_logo = $this->storeSettingImage($request, 'site_footer_logo', $row->site_footer_logo);
            $site_favicon = $this->storeSettingImage($request, 'site_favicon', $row->site_favicon);

            $fields = [
                'site_name'                         => $postData['site_name'],
                'site_phone'                        => $postData['site_phone'],
                'site_phone2'                       => $postData['site_phone2'],
                'site_mail'                         => $postData['site_mail'],
                'system_email'                      => $postData['system_email'],
                'site_url'                          => $postData['site_url'],
                'description'                       => $postData['description'],
                'copyright_statement'               => $postData['copyright_statement'],
                'google_map_api_code'               => $postData['google_map_api_code'],
                'google_analytics_code'             => $postData['google_analytics_code'],
                'google_pixel_code'                 => $postData['google_pixel_code'],
                'facebook_tracking_code'            => $postData['facebook_tracking_code'],
                'twitter_profile'                   => $postData['twitter_profile'],
                'facebook_profile'                  => $postData['facebook_profile'],
                'instagram_profile'                 => $postData['instagram_profile'],
                'linkedin_profile'                  => $postData['linkedin_profile'],
                'youtube_profile'                   => $postData['youtube_profile'],
                'site_logo'                         => $site_logo,
                'site_footer_logo'                  => $site_footer_logo,
                'site_favicon'                      => $site_favicon,
            ];
            // Helper::pr($fields);
            GeneralSetting::where('id', '=', 1)->update($fields);
            return redirect()->back()->with('success_message', 'General Settings Updated Successfully !!!');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }

    private function storeSettingImage(Request $request, string $field, ?string $currentFile): ?string
    {
        if (! $request->hasFile($field)) {
            return $currentFile;
        }

        $uploadDirectory = public_path('uploads');
        if (! is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0775, true);
        }

        $file = $request->file($field);
        $filename = Str::uuid().'.'.strtolower($file->getClientOriginalExtension());
        $file->move($uploadDirectory, $filename);

        return $filename;
    }
    public function change_password(Request $request)
    {
        $uId        = $request->session()->get('user_id');
        $adminData  = Admin::where('id', '=', $uId)->first();
        $postData   = $request->all();
        $rules      = [
            'old_password'            => 'required',
            'new_password'            => 'required',
            'confirm_password'        => 'required',
        ];
        if ($this->validate($request, $rules)) {
            $old_password       = $postData['old_password'];
            $new_password       = $postData['new_password'];
            $confirm_password   = $postData['confirm_password'];
            if (Hash::check($old_password, $adminData->password)) {
                if ($new_password == $confirm_password) {
                    $fields = [
                        'password'            => Hash::make($new_password)
                    ];
                    Admin::where('id', '=', $uId)->update($fields);
                    return redirect()->back()->with('success_message', 'Password Changed Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'New & Confirm Password Does Not Matched !!!');
                }
            } else {
                return redirect()->back()->with('error_message', 'Current Password Is Incorrect !!!');
            }
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }
    public function email_settings(Request $request)
    {
        $postData = $request->all();
        $rules = [
            'from_email'            => 'required',
            'from_name'             => 'required',
            'smtp_host'             => 'required',
            'smtp_username'         => 'required',
            'smtp_password'         => 'required',
            'smtp_port'             => 'required',
        ];
        if ($this->validate($request, $rules)) {
            $fields = [
                'from_email'            => $postData['from_email'],
                'from_name'             => $postData['from_name'],
                'smtp_host'             => $postData['smtp_host'],
                'smtp_username'         => $postData['smtp_username'],
                'smtp_password'         => $postData['smtp_password'],
                'smtp_port'             => $postData['smtp_port'],
            ];
            GeneralSetting::where('id', '=', 1)->update($fields);
            return redirect()->back()->with('success_message', 'Email Settings Updated Successfully !!!');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }
    public function email_template(Request $request)
    {
        $postData = $request->all();
        $rules = [
            'email_template_user_signup'            => 'required',
            'email_template_forgot_password'        => 'required',
            'email_template_change_password'        => 'required',
            'email_template_failed_login'           => 'required',
        ];
        if ($this->validate($request, $rules)) {
            $fields = [
                'email_template_user_signup_sender_name'            => $postData['email_template_user_signup_sender_name'],
                'email_template_user_signup_subject'                => $postData['email_template_user_signup_subject'],
                'email_template_user_signup'                        => $postData['email_template_user_signup'],
                'email_template_forgot_password'                    => $postData['email_template_forgot_password'],
                'email_template_change_password'                    => $postData['email_template_change_password'],
                'email_template_failed_login'                       => $postData['email_template_failed_login'],
                'email_template_contactus'                          => $postData['email_template_contactus'],
            ];
            GeneralSetting::where('id', '=', 1)->update($fields);
            return redirect()->back()->with('success_message', 'Email Templates Updated Successfully !!!');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }
    public function sms_settings(Request $request)
    {
        $postData = $request->all();
        $rules = [
            'sms_authentication_key'            => 'required',
            'sms_sender_id'                     => 'required',
            'sms_base_url'                      => 'required',
        ];
        if ($this->validate($request, $rules)) {
            $fields = [
                'sms_authentication_key'            => $postData['sms_authentication_key'],
                'sms_sender_id'                     => $postData['sms_sender_id'],
                'sms_base_url'                      => $postData['sms_base_url'],
            ];
            GeneralSetting::where('id', '=', 1)->update($fields);
            return redirect()->back()->with('success_message', 'SMS Settings Updated Successfully !!!');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }
    public function footer_settings(Request $request)
    {
        $postData = $request->all();
        $rules = [
            'footer_text'            => 'required',
        ];
        if ($this->validate($request, $rules)) {
            // A column with no link rows sends nothing, so every list is optional
            [$footer_link_name, $footer_link]   = $this->footerLinkPairs($request, 'footer_link_name', 'footer_link');
            [$footer_link_name2, $footer_link2] = $this->footerLinkPairs($request, 'second_col_link_text', 'second_col_link');
            [$footer_link_name3, $footer_link3] = $this->footerLinkPairs($request, 'footer_link_name3', 'footer_link3');

            $fields = [
                'footer_text'                   => $postData['footer_text'],
                'footer_description'            => $request->input('footer_description', ''),
                'footer_link_name'              => json_encode($footer_link_name),
                'footer_link'                   => json_encode($footer_link),
                'footer_link_name2'             => json_encode($footer_link_name2),
                'footer_link2'                  => json_encode($footer_link2),
                'footer_link_name3'             => json_encode($footer_link_name3),
                'footer_link3'                  => json_encode($footer_link3),
            ];
            // Helper::pr($fields);
            GeneralSetting::where('id', '=', 1)->update($fields);
            return redirect()->back()->with('success_message', 'Footer Settings Updated Successfully !!!');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }

    /**
     * Link text + URL lists for one footer column, keeping only complete
     * rows so a blank field never shifts later links onto the wrong text.
     */
    private function footerLinkPairs(Request $request, string $namesField, string $linksField): array
    {
        $names = array_values((array) $request->input($namesField, []));
        $links = array_values((array) $request->input($linksField, []));

        $keptNames = [];
        $keptLinks = [];
        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $link = trim((string) ($links[$i] ?? ''));
            if ($name !== '' && $link !== '') {
                $keptNames[] = $name;
                $keptLinks[] = $link;
            }
        }

        return [$keptNames, $keptLinks];
    }
    public function seo_settings(Request $request)
    {
        $postData = $request->all();
        $rules = [
            'meta_title'            => 'required',
            'meta_description'      => 'required'
        ];
        if ($this->validate($request, $rules)) {
            $fields = [
                'meta_title'            => $postData['meta_title'],
                'meta_description'      => $postData['meta_description']
            ];
            GeneralSetting::where('id', '=', 1)->update($fields);
            return redirect()->back()->with('success_message', 'SEO Settings Updated Successfully !!!');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }
    public function payment_settings(Request $request)
    {
        $postData = $request->all();
        $rules = [
            'stripe_payment_type'   => 'required',
            'stripe_sandbox_sk'     => 'required',
            'stripe_sandbox_pk'     => 'required',
            'stripe_live_sk'        => 'required',
            'stripe_live_pk'        => 'required',
        ];
        if ($this->validate($request, $rules)) {
            $fields = [
                'stripe_payment_type'   => $postData['stripe_payment_type'],
                'stripe_sandbox_sk'     => $postData['stripe_sandbox_sk'],
                'stripe_sandbox_pk'     => $postData['stripe_sandbox_pk'],
                'stripe_live_sk'        => $postData['stripe_live_sk'],
                'stripe_live_pk'        => $postData['stripe_live_pk'],
            ];
            GeneralSetting::where('id', '=', 1)->update($fields);
            return redirect()->back()->with('success_message', 'Payment Settings Updated Successfully !!!');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }
    public function color_settings(Request $request)
    {
        $postData = $request->all();
        $rules = [
            'theme_color'               => 'required',
            'font_color'                => 'required',
            'sidebar_bgcolor'           => 'required',
            'header_bgcolor'            => 'required',
        ];
        if ($this->validate($request, $rules)) {
            $fields = [
                'theme_color'                       => $postData['theme_color'],
                'font_color'                        => $postData['font_color'],
                'sidebar_bgcolor'                   => $postData['sidebar_bgcolor'],
                'header_bgcolor'                    => $postData['header_bgcolor'],
            ];
            GeneralSetting::where('id', '=', 1)->update($fields);
            return redirect()->back()->with('success_message', 'Color Settings Updated Successfully !!!');
        } else {
            return redirect()->back()->with('error_message', 'All Fields Required !!!');
        }
    }
    public function signature_settings(Request $request)
    {
        $postData = $request->all();
        /* signature */
        $signature                            = $postData['signature'];
        if (!empty($signature)) {
            $data = $signature;
            list($type, $data) = explode(';', $data);
            list(, $data)      = explode(',', $data);
            $data = base64_decode($data);
            $signatureVal   = uniqid() . '.png';
            file_put_contents('public/uploads/' . $signatureVal, $data);
        }
        /* signature */
        $fields = [
            'owner_signature'                       => $signatureVal
        ];
        // Helper::pr($fields);
        GeneralSetting::where('id', '=', 1)->update($fields);
        return redirect()->back()->with('success_message', 'Owner Signature Settings Updated Successfully !!!');
    }
    /* settings */
    /* email logs */
    public function emailLogs()
    {
        $data['rows']                   = EmailLog::where('status', '=', 1)->orderBy('id', 'DESC')->get();
        $title                          = 'Email Logs';
        $page_name                      = 'email-logs';
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    public function emailLogsDetails(Request $request, $id)
    {
        $id = Helper::decoded($id);
        $data['logData']                   = EmailLog::where('id', '=', $id)->orderBy('id', 'DESC')->first();
        $title                          = 'Email Logs Details';
        $page_name                      = 'email-logs-info';
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* email logs */
    /* login logs */
    public function loginLogs()
    {
        $data['rows1']                   = UserActivity::where('activity_type', '=', 0)->orderBy('activity_id', 'DESC')->get();
        $data['rows2']                   = UserActivity::where('activity_type', '=', 1)->orderBy('activity_id', 'DESC')->get();
        $data['rows3']                   = UserActivity::where('activity_type', '=', 2)->orderBy('activity_id', 'DESC')->get();
        $title                          = 'Login Logs';
        $page_name                      = 'login-logs';
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* login logs */
}
