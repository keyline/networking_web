<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Attendance;
use App\Models\Banner;
use App\Models\Country;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\ClientCheckIn;
use App\Models\ClientOrder;
use App\Models\ClientOrderDetail;
use App\Models\District;
use App\Models\DeleteAccountRequest;
use App\Models\EmailLog;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Models\Enquiry;
use App\Models\GeneralSetting;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\Odometer;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\Quote;
use App\Models\Size;
use App\Models\State;
use App\Models\Unit;
use App\Models\UserActivity;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\Companies\CompaniesMaster;
use App\Models\Companies\CompaniesDetail;
use Auth;
use Session;
use Helper;
// use Hash;
use Illuminate\Support\Facades\Hash;
use App\Libraries\CreatorJwt;
use App\Libraries\JWT;
use App\Models\Enquiries\EnquiryMaster;
use App\Models\User\UserMaster;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Companies\CategoryToCompany;
use App\Models\Review\ReviewMaster;
use App\Models\User\UserDetails;
use App\Models\User\UserTypeMaster;

date_default_timezone_set("Asia/Calcutta");
class ApiController extends Controller
{
    public array $enquiryTypes;

    public function __construct()
    {
        $this->enquiryTypes = [
            2 => 'Public',
            1 => 'Private',
            0 => 'Normal', // Default value
        ];
    }



    /* before login screen */
    public function getAppSetting(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $generalSetting = GeneralSetting::find(1);
            if ($generalSetting) {
                $apiResponse = [
                    'site_name'             => $generalSetting->site_name,
                    'site_phone'            => $generalSetting->site_phone,
                    'site_phone2'           => $generalSetting->site_phone2,
                    'site_mail'             => $generalSetting->site_mail,
                    'system_email'          => $generalSetting->system_email,
                    'site_url'              => $generalSetting->site_url,
                    'site_logo'             => env('UPLOADS_URL') . $generalSetting->site_logo,
                    'site_footer_logo'      => env('UPLOADS_URL') . $generalSetting->site_footer_logo,
                    'site_favicon'          => env('UPLOADS_URL') . $generalSetting->site_favicon,
                    'site_address'          => $generalSetting->description,
                    'theme_color'           => $generalSetting->theme_color,
                    'font_color'            => $generalSetting->font_color,
                    'sidebar_bgcolor'       => $generalSetting->sidebar_bgcolor,
                    'header_bgcolor'        => $generalSetting->header_bgcolor,
                    'twitter_profile'       => $generalSetting->twitter_profile,
                    'facebook_profile'      => $generalSetting->facebook_profile,
                    'instagram_profile'     => $generalSetting->instagram_profile,
                    'linkedin_profile'      => $generalSetting->linkedin_profile,
                    'youtube_profile'       => $generalSetting->youtube_profile,
                ];
            }
            http_response_code(200);
            $apiStatus          = true;
            $apiMessage         = 'Data Available !!!';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        } else {
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }
    public function getStaticPages(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'page_slug'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $page_slug = $requestData['page_slug'];
            $pageContent  = Page::select('page_name', 'page_content')->where('status', '=', 1)->where('page_slug', '=', $page_slug)->first();
            if ($pageContent) {
                $apiResponse[] = [
                    'page_name'                 => $pageContent->page_name,
                    'page_content'              => $pageContent->page_content
                ];
            }
            http_response_code(200);
            $apiStatus          = true;
            $apiMessage         = 'Data Available !!!';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        } else {
            http_response_code(400);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }
    /* before login screen */
    /* authentication */
    public function signin(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'email', 'password', 'device_token', 'fcm_token'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $email                      = $requestData['email'];
            $password                   = $requestData['password'];
            $device_type                = $headerData['source'][0];
            $device_token               = $requestData['device_token'];
            $fcm_token                  = $requestData['fcm_token'];
            $checkUser                  = Employees::where('email', '=', $email)->where('status', '=', 1)->first();
            if ($checkUser) {
                if (Hash::check($password, $checkUser->password)) {
                    $objOfJwt           = new CreatorJwt();
                    $app_access_token   = $objOfJwt->GenerateToken($checkUser->id, $checkUser->email, $checkUser->phone);
                    $user_id                        = $checkUser->id;
                    $fields     = [
                        'user_id'               => $user_id,
                        'device_type'           => $device_type,
                        'device_token'          => $device_token,
                        'fcm_token'             => $fcm_token,
                        'app_access_token'      => $app_access_token,
                    ];
                    $checkUserTokenExist            = UserDevice::where('user_id', '=', $user_id)->where('published', '=', 1)->where('device_type', '=', $device_type)->where('device_token', '=', $device_token)->first();
                    if (!$checkUserTokenExist) {
                        UserDevice::insert($fields);
                    } else {
                        UserDevice::where('id', '=', $checkUserTokenExist->id)->update($fields);
                    }
                    $getEmployeeType        = EmployeeType::select('name')->where('id', '=', $checkUser->employee_type_id)->first();
                    $apiResponse            = [
                        'user_id'               => $user_id,
                        'name'                  => $checkUser->name,
                        'email'                 => $checkUser->email,
                        'phone'                 => $checkUser->phone,
                        'employee_type_name'    => (($getEmployeeType) ? $getEmployeeType->name : ''),
                        'employee_type_id'      => $checkUser->employee_type_id,
                        'device_type'           => $device_type,
                        'device_token'          => $device_token,
                        'fcm_token'             => $fcm_token,
                        'app_access_token'      => $app_access_token,
                    ];
                    $apiStatus                          = true;
                    $apiMessage                         = 'SignIn Successfully !!!';
                } else {
                    $apiStatus                          = false;
                    $apiMessage                         = 'Invalid Password !!!';
                }
            } else {
                $apiStatus                              = false;
                $apiMessage                             = 'We Don\'t Recognize You !!!';
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
    public function signinWithMobile(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['phone'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $phone                      = $requestData['phone'];
            $checkUser                  = Employees::where('phone', '=', $phone)->where('status', '=', 1)->first();
            if ($checkUser) {
                $remember_token  = rand(1000, 9999);
                Employees::where('id', '=', $checkUser->id)->update(['otp' => $remember_token]);
                $mailData                   = [
                    'id'    => $checkUser->id,
                    'email' => $checkUser->email,
                    'phone' => $checkUser->phone,
                    'otp'   => $remember_token,
                ];
                $generalSetting             = GeneralSetting::find('1');
                $subject                    = $generalSetting->site_name . ' :: SignIn Validate OTP';
                $message                    = view('email-templates.otp', $mailData);
                $this->sendMail($checkUser->email, $subject, $message);

                /* email log save */
                $postData2 = [
                    'name'                  => $checkUser->name,
                    'email'                 => $checkUser->email,
                    'subject'               => $subject,
                    'message'               => $message
                ];
                EmailLog::insert($postData2);
                /* email log save */
                /* send sms */
                $name       = $checkUser->name;
                $message    = "Dear " . $name . ", " . $remember_token . " is your verification OTP for ProTime Manager at KEYLINE. Do not share this OTP with anyone for security reasons.";
                $mobileNo   = (($checkUser) ? $checkUser->phone : '');
                $this->sendSMS($mobileNo, $message);
                /* send sms */
                $apiResponse                        = $mailData;
                $apiStatus                          = true;
                $apiMessage                         = 'OTP Sent To Email & Phone Validation !!!';
            } else {
                $apiStatus                              = false;
                $apiMessage                             = 'We Don\'t Recognize You !!!';
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
    public function signinValidateMobile(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['phone', 'otp', 'device_token'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $phone                      = $requestData['phone'];
            $otp                        = $requestData['otp'];
            $device_type                = $headerData['source'][0];
            $device_token               = $requestData['device_token'];
            $fcm_token                  = $requestData['fcm_token'];
            $checkUser                  = Employees::where('phone', '=', $phone)->where('status', '=', 1)->first();
            if ($checkUser) {
                if ($checkUser->otp == $otp) {
                    $objOfJwt               = new CreatorJwt();
                    $app_access_token       = $objOfJwt->GenerateToken($checkUser->id, $checkUser->email, $checkUser->phone);
                    $user_id                = $checkUser->id;
                    Employees::where('id', '=', $user_id)->update(['otp' => 0]);
                    $fields     = [
                        'user_id'               => $user_id,
                        'device_type'           => $device_type,
                        'device_token'          => $device_token,
                        'fcm_token'             => $fcm_token,
                        'app_access_token'      => $app_access_token,
                    ];
                    $checkUserTokenExist            = UserDevice::where('user_id', '=', $user_id)->where('published', '=', 1)->where('device_type', '=', $device_type)->where('device_token', '=', $device_token)->first();
                    if (!$checkUserTokenExist) {
                        UserDevice::insert($fields);
                    } else {
                        UserDevice::where('id', '=', $checkUserTokenExist->id)->update($fields);
                    }
                    $getEmployeeType        = EmployeeType::select('name')->where('id', '=', $checkUser->employee_type_id)->first();
                    $apiResponse            = [
                        'user_id'               => $user_id,
                        'name'                  => $checkUser->name,
                        'email'                 => $checkUser->email,
                        'phone'                 => $checkUser->phone,
                        'employee_type_name'    => (($getEmployeeType) ? $getEmployeeType->name : ''),
                        'employee_type_id'      => $checkUser->employee_type_id,
                        'device_type'           => $device_type,
                        'device_token'          => $device_token,
                        'fcm_token'             => $fcm_token,
                        'app_access_token'      => $app_access_token,
                    ];
                    $apiStatus                          = true;
                    $apiMessage                         = 'SignIn Successfully !!!';
                } else {
                    $apiStatus          = false;
                    http_response_code(200);
                    $apiMessage         = 'OTP Mismatched !!!';
                    $apiExtraField      = 'response_code';
                }
            } else {
                $apiStatus                              = false;
                $apiMessage                             = 'We Don\'t Recognize You !!!';
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
    public function forgotPassword(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'email'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $checkEmail = Employees::where('email', '=', $requestData['email'])->first();
            if ($checkEmail) {
                $remember_token  = rand(1000, 9999);
                Employees::where('id', '=', $checkEmail->id)->update(['otp' => $remember_token]);
                $mailData                   = [
                    'id'    => $checkEmail->id,
                    'email' => $checkEmail->email,
                    'otp'   => $remember_token,
                ];
                $generalSetting             = GeneralSetting::find('1');
                $subject                    = $generalSetting->site_name . ' :: Forgot Password OTP';
                $message                    = view('email-templates.otp', $mailData);
                $this->sendMail($requestData['email'], $subject, $message);

                /* email log save */
                $postData2 = [
                    'name'                  => $checkEmail->name,
                    'email'                 => $checkEmail->email,
                    'subject'               => $subject,
                    'message'               => $message
                ];
                EmailLog::insert($postData2);
                /* email log save */

                $apiResponse                        = $mailData;
                $apiStatus                          = true;
                http_response_code(200);
                $apiMessage                         = 'OTP Sent To Email Validation !!!';
                $apiExtraField                      = 'response_code';
                $apiExtraData                       = http_response_code();
            } else {
                $apiStatus          = false;
                http_response_code(200);
                $apiMessage         = 'Email Not Registered With Us !!!';
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }
        } else {
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }
    public function validateOtp(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'id', 'otp'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $getUser = Employees::where('id', '=', $requestData['id'])->first();
            if ($getUser) {
                $remember_token  = $getUser->otp;
                if ($remember_token == $requestData['otp']) {
                    Employees::where('id', '=', $requestData['id'])->update(['otp' => 0]);
                    // $this->sendMail('subhomoysamanta1989@gmail.com', $requestData['subject'], $requestData['message']);
                    $apiResponse        = [
                        'id'    => $getUser->id,
                        'email' => $getUser->email
                    ];
                    $apiStatus                          = true;
                    http_response_code(200);
                    $apiMessage                         = 'OTP Validated Successfully !!!';
                    $apiExtraField                      = 'response_code';
                    $apiExtraData                       = http_response_code();
                } else {
                    $apiStatus          = false;
                    http_response_code(200);
                    $apiMessage         = 'OTP Mismatched !!!';
                    $apiExtraField      = 'response_code';
                }
            } else {
                $apiStatus          = false;
                http_response_code(200);
                $apiMessage         = 'Teacher Not Found !!!';
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }
        } else {
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }
    public function resendOtp(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'id'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $id         = $requestData['id'];
            $getUser    = Employees::where('id', '=', $id)->first();
            if ($getUser) {
                $remember_token = rand(1000, 9999);
                $postData = [
                    'otp'        => $remember_token
                ];
                Employees::where('id', '=', $id)->update($postData);

                $mailData                   = [
                    'id'    => $getUser->id,
                    'email' => $getUser->email,
                    'otp'   => $remember_token,
                ];
                $generalSetting             = GeneralSetting::find('1');
                $subject                    = $generalSetting->site_name . ' :: Resend OTP';
                $message                    = view('email-templates.otp', $mailData);
                $this->sendMail($getUser->email, $subject, $message);

                /* email log save */
                $postData2 = [
                    'name'                  => $getUser->name,
                    'email'                 => $getUser->email,
                    'subject'               => $subject,
                    'message'               => $message
                ];
                EmailLog::insert($postData2);
                /* email log save */

                $apiResponse                        = $mailData;
                $apiStatus                          = true;
                http_response_code(200);
                $apiMessage                         = 'OTP Resend !!!';
                $apiExtraField                      = 'response_code';
                $apiExtraData                       = http_response_code();
            } else {
                $apiStatus          = false;
                http_response_code(200);
                $apiMessage         = 'Teacher Not Found !!!';
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }
        } else {
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }
    public function resetPassword(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'id', 'password', 'confirm_password'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $getUser = Employees::where('id', '=', $requestData['id'])->first();
            if ($getUser) {
                if ($requestData['password'] == $requestData['confirm_password']) {
                    Employees::where('id', '=', $requestData['id'])->update(['password' => Hash::make($requestData['password'])]);
                    $mailData        = [
                        'id'        => $getUser->id,
                        'name'      => $getUser->name,
                        'email'     => $getUser->email
                    ];

                    $generalSetting             = GeneralSetting::find('1');
                    $subject                    = $generalSetting->site_name . ' :: Reset Password';
                    $message                    = view('email-templates.change-password', $mailData);
                    $this->sendMail($getUser->email, $subject, $message);

                    /* email log save */
                    $postData2 = [
                        'name'                  => $getUser->name,
                        'email'                 => $getUser->email,
                        'subject'               => $subject,
                        'message'               => $message
                    ];
                    EmailLog::insert($postData2);
                    /* email log save */

                    $apiStatus                          = true;
                    http_response_code(200);
                    $apiMessage                         = 'Password Reset Successfully !!!';
                    $apiExtraField                      = 'response_code';
                    $apiExtraData                       = http_response_code();
                } else {
                    $apiStatus          = false;
                    http_response_code(200);
                    $apiMessage         = 'Password & Confirm Password Not Matched !!!';
                    $apiExtraField      = 'response_code';
                }
            } else {
                $apiStatus          = false;
                http_response_code(200);
                $apiMessage         = 'User Not Found !!!';
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }
        } else {
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }
    /* authentication */
    /* after login */
    public function signout(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = [];
        $headerData         = $request->header();
        // Helper::pr($headerData);
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $app_access_token           = $headerData['authorization'][0];
            $checkUserTokenExist        = UserDevice::where('app_access_token', '=', $app_access_token)->where('published', '=', 1)->first();
            if ($checkUserTokenExist) {
                UserDevice::where('app_access_token', '=', $app_access_token)->delete();
                $apiStatus                      = true;
                $apiMessage                     = 'Signout Successfully !!!';
            } else {
                $apiStatus                      = false;
                $apiMessage                     = 'Something Went Wrong !!!';
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }

    public function getProfile(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $app_access_token           = $headerData['authorization'][0];
            $getTokenValue              = $this->tokenAuth($app_access_token);
            if ($getTokenValue['status']) {
                $uId        = $getTokenValue['data'][1];
                $expiry     = date('d/m/Y H:i:s', $getTokenValue['data'][4]);
                $getUser    = Employees::where('id', '=', $uId)->first();
                if ($getUser) {
                    $getEmployeeType     = EmployeeType::select('name', 'is_report')->where('id', '=', $getUser->employee_type_id)->first();
                    $profileData    = [
                        'employee_no'           => $getUser->employee_no,
                        'employee_type_id'      => (($getEmployeeType) ? $getEmployeeType->name : ''),
                        'is_report'             => (($getEmployeeType) ? $getEmployeeType->is_report : 0),
                        'name'                  => $getUser->name,
                        'email'                 => $getUser->email,
                        'alt_email'             => $getUser->alt_email,
                        'phone'                 => $getUser->phone,
                        'whatsapp_no'           => $getUser->whatsapp_no,
                        'short_bio'             => $getUser->short_bio,
                        'dob'                   => (($getUser->dob != '') ? date_format(date_create($getUser->dob), "M d, Y") : ''),
                        'doj'                   => (($getUser->doj != '') ? date_format(date_create($getUser->doj), "M d, Y") : ''),
                        'qualification'         => (($getUser->qualification != '') ? $getUser->qualification : ''),
                        'created_at'            => date_format(date_create($getUser->created_at), "M d, Y h:i A"),
                        'profile_image'         => (($getUser->profile_image != '') ? env('UPLOADS_URL') . 'user/' . $getUser->profile_image : env('NO_USER_IMAGE')),
                    ];
                    $apiStatus          = true;
                    $apiMessage         = 'Data Available !!!';
                    $apiResponse        = $profileData;
                } else {
                    $apiStatus          = false;
                    $apiMessage         = 'User Not Found !!!';
                }
            } else {
                $apiStatus                      = false;
                $apiMessage                     = $getTokenValue['data'];
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
    public function editProfile(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $app_access_token           = $headerData['authorization'][0];
            $getTokenValue              = $this->tokenAuth($app_access_token);
            if ($getTokenValue['status']) {
                $uId        = $getTokenValue['data'][1];
                $expiry     = date('d/m/Y H:i:s', $getTokenValue['data'][4]);
                $getUser    = Employees::where('id', '=', $uId)->first();
                if ($getUser) {
                    $getEmployeeType     = EmployeeType::select('name')->where('id', '=', $getUser->employee_type_id)->first();
                    $profileData    = [
                        'employee_type_id'      => $getUser->employee_type_id,
                        'name'                  => $getUser->name,
                        'email'                 => $getUser->email,
                        'alt_email'             => $getUser->alt_email,
                        'phone'                 => $getUser->phone,
                        'whatsapp_no'           => $getUser->whatsapp_no,
                        'short_bio'             => $getUser->short_bio,
                        'dob'                   => $getUser->dob,
                        'doj'                   => $getUser->doj,
                        'qualification'         => (($getUser->qualification != '') ? $getUser->qualification : ''),
                    ];
                    $apiStatus          = true;
                    $apiMessage         = 'Data Available !!!';
                    $apiResponse        = $profileData;
                } else {
                    $apiStatus          = false;
                    $apiMessage         = 'User Not Found !!!';
                }
            } else {
                $apiStatus                      = false;
                $apiMessage                     = $getTokenValue['data'];
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
    public function updateProfile(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'name', 'whatsapp_no'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $app_access_token           = $headerData['authorization'][0];
            $getTokenValue              = $this->tokenAuth($app_access_token);
            if ($getTokenValue['status']) {
                $uId        = $getTokenValue['data'][1];
                $expiry     = date('d/m/Y H:i:s', $getTokenValue['data'][4]);
                $getUser    = Employees::where('id', '=', $uId)->first();
                if ($getUser) {
                    $postData = [
                        // 'employee_type_id'          => $requestData['employee_type_id'],
                        'name'                      => $requestData['name'],
                        'alt_email'                 => $requestData['alt_email'],
                        'whatsapp_no'               => $requestData['whatsapp_no'],
                        'short_bio'                 => $requestData['short_bio'],
                        // 'dob'                       => $requestData['dob'],
                        // 'doj'                       => $requestData['doj'],
                        'qualification'             => $requestData['qualification'],
                    ];
                    Employees::where('id', '=', $uId)->update($postData);
                    $apiStatus                  = true;
                    $apiMessage                 = 'Profile Updated Successfully !!!';
                } else {
                    $apiStatus          = false;
                    $apiMessage         = 'User Not Found !!!';
                }
            } else {
                $apiStatus                      = false;
                $apiMessage                     = $getTokenValue['data'];
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
    public function getNotification(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['page_no'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $app_access_token           = $headerData['authorization'][0];
            $getTokenValue              = $this->tokenAuth($app_access_token);
            $page_no                    = $requestData['page_no'];
            if ($getTokenValue['status']) {
                $uId        = $getTokenValue['data'][1];
                $expiry     = date('d/m/Y H:i:s', $getTokenValue['data'][4]);
                $getUser    = Employees::where('id', '=', $uId)->first();
                if ($getUser) {
                    $limit          = 15; // per page elements
                    if ($page_no == 1) {
                        $offset = 0;
                    } else {
                        $offset = (($limit * $page_no) - $limit); // ((15 * 3) - 15)
                    }
                    $notifications    = Notification::select('id', 'title', 'description', 'send_timestamp', 'users')->where('to_users', '=', $uId)->where('status', '=', 1)->where('is_send', '=', 1)->orderBy('id', 'DESC')->offset($offset)->limit($limit)->get();
                    if ($notifications) {
                        foreach ($notifications as $notification) {
                            $users = json_decode($notification->users);
                            if (in_array($uId, $users)) {
                                $apiResponse[]        = [
                                    'id'                    => $notification->id,
                                    'title'                 => $notification->title,
                                    'description'           => $notification->description,
                                    'send_timestamp'        => date_format(date_create($notification->send_timestamp), "M d, Y h:i A"),
                                ];
                            }
                        }
                    }
                    $apiStatus          = true;
                    $apiMessage         = 'Data Available !!!';
                } else {
                    $apiStatus          = false;
                    $apiMessage         = 'User Not Found !!!';
                }
            } else {
                $apiStatus                      = false;
                $apiMessage                     = $getTokenValue['data'];
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
    public function getCategory(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $parentCats = BusinessCategoryMaster::select('bcm_id', 'name', 'logo_file')->where('status', '=', 1)->where('parent_id', '=', 0)->orderBy('name', 'ASC')->get();
            // Active businesses per category, so the app can show counts and hide empty ones
            $businessCounts = DB::table('categories_to_companies')
                ->join('companies_details', 'companies_details.cmpd_cmp_id', '=', 'categories_to_companies.ctc_cmp_id')
                ->where('companies_details.cmpd_status', 1)
                ->groupBy('categories_to_companies.ctc_bcm_id')
                ->selectRaw('categories_to_companies.ctc_bcm_id as bcm_id, COUNT(DISTINCT categories_to_companies.ctc_cmp_id) as total')
                ->pluck('total', 'bcm_id');
            if ($parentCats) {
                foreach ($parentCats as $parentCat) {
                    $childCats      = BusinessCategoryMaster::select('bcm_id', 'name', 'logo_file')->where('status', '=', 1)->where('parent_id', '=', $parentCat->bcm_id)->orderBy('name', 'ASC')->get();
                    $sub_category   = [];
                    $parentCount    = (int) ($businessCounts[$parentCat->bcm_id] ?? 0);
                    if ($childCats) {
                        foreach ($childCats as $childCat) {
                            $childCount = (int) ($businessCounts[$childCat->bcm_id] ?? 0);
                            $parentCount += $childCount;
                            $sub_category[] = [
                                'id'        => $childCat->id,
                                'name'      => $childCat->name,
                                'logo_img'  => ($childCat->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $childCat->logo_file : env('NO_CATEGORY_IMAGE'),
                                'business_count' => $childCount,
                            ];
                        }
                    }
                    $apiResponse[] = [
                        'label'             => $parentCat->name,
                        'value'             => $parentCat->bcm_id,
                        'sub_category'      => $sub_category,
                        'logo_img'  => ($parentCat->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $parentCat->logo_file : env('NO_CATEGORY_IMAGE'),
                        'business_count'    => $parentCount,
                    ];
                }
            }
            http_response_code(200);
            $apiStatus          = true;
            $apiMessage         = 'Data Available !!!';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        } else {
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }
    public function dashboard(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $hundredYearsCompanies  = [];
            $topCompanies           = [];
            $currentYear            = date('Y');
            $hundredCompanies       = CompaniesDetail::select('cmpd_id', 'cmpd_cmp_id', 'cmpd_name', 'cmpd_logo', 'cmpd_estd_year')->where('cmpd_status', '=', 1)->where('cmpd_estd_year', '!=', 'null')->orderBy('cmpd_name', 'ASC')->get();
            if ($hundredCompanies) {
                foreach ($hundredCompanies as $hundredCompany) {
                    $yearDiff = ($currentYear - $hundredCompany->cmpd_estd_year);
                    if ($yearDiff > 100) {
                        $categoryName = null;
                        $ratingData = getBusinessRating($hundredCompany->cmpd_cmp_id);



                        $category  = DB::table('business_category_master')
                            ->join('categories_to_companies', 'business_category_master.bcm_id', '=', 'categories_to_companies.ctc_bcm_id')
                            ->where('categories_to_companies.ctc_cmp_id', $hundredCompany->cmpd_cmp_id)->first();

                        if ($category) {
                            $categoryName = [
                                "label" => $category->name,
                                "value" => $category->bcm_id,
                                "sub_category" => [],
                                'logo_img'  => ($category->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $category->logo_file : env('NO_CATEGORY_IMAGE'),
                            ];
                        }

                        $hundredYearsCompanies[] = [
                            'cmpd_id'               => $hundredCompany->cmpd_cmp_id,
                            'cmpd_name'             => $hundredCompany->cmpd_name,
                            'cmpd_estd_year'        => $hundredCompany->cmpd_estd_year,
                            'cmpd_logo'             => (($hundredCompany->cmpd_logo != '') ? env('UPLOADS_URL') . 'company/' . $hundredCompany->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE')),
                            "avg_rating"            => (float) $ratingData->avg_rating,
                            "total_reviews"         => (int) $ratingData->total_reviews,
                            "category" => $categoryName,

                        ];
                    }
                }
            }
            # OLD ORIGINAL LOGIC
            /*  $topCompaniesIds       = DB::table('company_click_master')
                ->select('ccm_cmp_id', DB::raw('COUNT(*) as click_count'))
                ->groupBy('ccm_cmp_id')
                ->orderByDesc('click_count')
                ->limit(12)
                //->get();
                ->pluck('ccm_cmp_id');
            */
            # USE FOR DEVELOPMENT (5-3-25)
            $topCompaniesIds =   CompaniesDetail::where('cmpd_status', '=', 1)->limit(12)->pluck('cmpd_cmp_id');
            // Helper::pr($top_companies);
            if ($topCompaniesIds && count($topCompaniesIds) > 0) {


                $topCompaniesList = CompaniesDetail::select('cmpd_id', 'cmpd_cmp_id', 'cmpd_name', 'cmpd_logo', 'cmpd_estd_year')
                    ->where('cmpd_status', '=', 1)
                    ->whereIn('cmpd_id', $topCompaniesIds)
                    ->orderBy('cmpd_name', 'ASC')
                    ->get();

                foreach ($topCompaniesList as $getCompanyDetails) {
                    if ($getCompanyDetails) {
                        $categoryName = null;
                        $ratingData = getBusinessRating($getCompanyDetails->cmpd_cmp_id);

                        $category  = DB::table('business_category_master')
                            ->join('categories_to_companies', 'business_category_master.bcm_id', '=', 'categories_to_companies.ctc_bcm_id')
                            ->where('categories_to_companies.ctc_cmp_id', $getCompanyDetails->cmpd_cmp_id)->first();
                        // ->value('business_category_master.name'); // Adjust 'name' to the actual column name in your table.
                        if ($category) {
                            $categoryName = [
                                "label" => $category->name,
                                "value" => $category->bcm_id,
                                "sub_category" => [],
                                'logo_img'  => ($category->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $category->logo_file : env('NO_CATEGORY_IMAGE'),
                            ];
                        }


                        $topCompanies[] = [
                            'cmpd_id'               => $getCompanyDetails->cmpd_cmp_id,
                            'cmpd_name'             => $getCompanyDetails->cmpd_name,
                            'cmpd_estd_year'        => $getCompanyDetails->cmpd_estd_year,
                            'cmpd_logo'             => (($getCompanyDetails->cmpd_logo != '') ? env('UPLOADS_URL') . 'company/' . $getCompanyDetails->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE')),
                            "avg_rating"            => (float) $ratingData->avg_rating,
                            "total_reviews"         => (int) $ratingData->total_reviews,
                            "category" => $categoryName,
                        ];
                    }
                }
            }
            $apiResponse = [
                'hundredYearsCompanies' => $hundredYearsCompanies,
                'topCompanies'          => $topCompanies,
            ];
            http_response_code(200);
            $apiStatus          = true;
            $apiMessage         = 'Data Available !!!';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        } else {
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function searchByKeywords(Request $request)
    {

        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'page_no'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, array_merge($requestData, $headerData))) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }

        if ($headerData['key'][0] == env('PROJECT_KEY')) {

            // Capture the keyword and the desired page number from the POST data
            $keyword = $request->input('keywords', ''); // Default to empty if no keyword
            $pageNo = $request->input('page_no', '1');
            $perPage = 10; // Set the number of results you want per page


            // Calculate offset
            $offset = ($pageNo - 1) * $perPage;

            if (!empty($keyword) && ($keyword !== '100 Years' && $keyword !== 'Top Brands')) {

                //$query = DB::table('companies_details'); // Replace with your table name

                /*$query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('cmpd_description', 'like', "%{$keyword}%")
                             ->orWhere('cmpd_name', 'like', "%{$keyword}%");
                });*/


                $query = DB::table('user_companies_map')
                    ->join('user_details', 'user_companies_map.ucm_um_id', '=', 'user_details.ud_um_id')
                    ->join('companies_details', 'user_companies_map.ucm_cmp_id', '=', 'companies_details.cmpd_cmp_id')
                    ->join('districts', 'districts.id', '=', 'companies_details.cmpd_district')
                    ->join('states as st', 'st.id', '=', 'companies_details.cmpd_state')
                    ->join('countries as cu', 'cu.id', '=', 'companies_details.cmpd_country')
                    ->join('categories_to_companies', 'categories_to_companies.ctc_cmp_id', '=', 'companies_details.cmpd_cmp_id')
                    ->join('business_category_master', 'business_category_master.bcm_id', '=', 'categories_to_companies.ctc_bcm_id')
                    ->where(function ($subQuery) use ($keyword) {
                        $subQuery->where('companies_details.cmpd_description', 'like', "%{$keyword}%")
                            ->orWhere('companies_details.cmpd_name', 'like', "%{$keyword}%")
                            ->orWhere('business_category_master.name', 'like', "%{$keyword}%")
                            ->orWhere('user_details.ud_first_name', 'like', "%{$keyword}%")
                        ;
                    })
                    ->where('companies_details.cmpd_status', 1)
                    ->select(
                        DB::raw("CONCAT(COALESCE(user_details.ud_salutation, ''), ' ', user_details.ud_first_name) AS ud_first_name"),
                        'user_details.ud_profile_image',
                        'companies_details.*',
                        'districts.name as District_Name',
                        'st.name as State_Name',
                        'cu.name as Country_Name'
                    )
                    ->orderBy('companies_details.cmpd_name', 'ASC'); // Select the first name and all company details or specific columns




                // Convert to SQL and print it
                //$sql = $query->toSql();

                // Get bindings and print the full query
                //$bindings = $query->getBindings();

                // Combine SQL and bindings (for demonstration)
                //$rawQuery = vsprintf(str_replace('?', '%s', $sql), array_map('addslashes', $bindings));
                //echo "Raw SQL: " . $rawQuery;
                //exit();

                // Get the total count for pagination
                $totalCompanies = $query->count();

                // Fetch the actual data with offset and limit
                $companies = $query->offset($offset)
                    ->limit($perPage)
                    ->get();

                // Calculate total pages
                $totalPages = ceil($totalCompanies / $perPage);


                // Format and return response with data and pagination info
                $resultSet = [];
                foreach ($companies as $company) {
                    $categoryName = null;
                    $ratingData = getBusinessRating($company->cmpd_cmp_id);
                    $category  = DB::table('business_category_master')
                        ->join('categories_to_companies', 'business_category_master.bcm_id', '=', 'categories_to_companies.ctc_bcm_id')
                        ->where('categories_to_companies.ctc_cmp_id', $company->cmpd_cmp_id)->first();
                    // ->value('business_category_master.name'); // Adjust 'name' to the actual column name in your table.
                    if ($category) {
                        $categoryName = [
                            "label" => $category->name,
                            "value" => $category->bcm_id,
                            "sub_category" => [],
                            'logo_img'  => ($category->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $category->logo_file : env('NO_CATEGORY_IMAGE'),
                        ];
                    }


                    $data = [
                        "owner_name" => $company->ud_first_name ?? 'NA',
                        "owner_profile_img" => (($company->ud_profile_image != '') ? env('UPLOADS_URL') . 'user/' . $company->ud_profile_image : env('NO_USER_IMAGE')),
                        "cmpd_id" => $company->cmpd_cmp_id,
                        "cmpd_cmp_id" => $company->cmpd_cmp_id,
                        "cmpd_company_regn_no" => $company->cmpd_company_regn_no,
                        "cmpd_name" => $company->cmpd_name,
                        //"cmpd_description" => $categoryName ?? "Unclassified", //$company->cmpd_description,
                        "cmpd_description" => $company->cmpd_description,
                        "category" => $categoryName, //$categoryName ?? "Unclassified",
                        "cmpd_email" => $company->cmpd_email,
                        "cmpd_alternate_email" => $company->cmpd_alternate_email,
                        "cmpd_phone" => $company->cmpd_phone,
                        "cmpd_whatsapp_no" => $company->cmpd_whatsapp_no,
                        "cmpd_logo" => (($company->cmpd_logo != '') ? env('UPLOADS_URL') . 'company/' . $company->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE')),
                        "cmpd_address1" => $company->cmpd_address1 ?? '',
                        "cmpd_address2" => $company->cmpd_address2 ?? '',
                        "cmpd_address3" => $company->cmpd_address3 ?? '',
                        "cmpd_estd_year" => $company->cmpd_estd_year ?? '',
                        "cmpd_district" => ucwords(strtolower($company->District_Name)) ?? '',
                        "cmpd_state" => ucwords(strtolower($company->State_Name)) ?? "",
                        "cmpd_country" => ucwords(strtolower($company->Country_Name)) ?? "",

                        "cmpd_pincode" => $company->cmpd_pincode ?? '',
                        // "cmpd_category_name" => $categoryName ?? "Unclassified",
                        "avg_rating" => (float) $ratingData->avg_rating,
                        "total_reviews" => (int) $ratingData->total_reviews,

                    ];

                    array_push($resultSet, $data);
                }

                $apiResponse = [
                    'data' => $resultSet
                ];
                $apiResponse['meta'] = [
                    'current_page' => $pageNo,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total' => $totalCompanies,
                ];
            } elseif (!empty($keyword) && $keyword === '100 Years') {

                //$currentYear            = date('Y');
                $hundredYearsCompanies  = [];
                //$offset = ($pageNo - 1) * $perPage;
                $hundredCompanies = CompaniesDetail::with(['district:id,name', 'state:id,name', 'country:id,name', 'companies', 'companies.users.userDetail', 'companies.categories'])
                    //->select('cmpd_id', 'cmpd_cmp_id', 'cmpd_name', 'cmpd_logo', 'cmpd_estd_year')
                    ->where('cmpd_status', '=', 1)
                    ->whereNotNull('cmpd_estd_year')
                    ->whereRaw('YEAR(CURDATE()) - cmpd_estd_year > 100')
                    ->orderBy('cmpd_name', 'ASC')
                    ->offset($offset)
                    ->limit($perPage)
                    ->get();

                // Get the raw SQL query for debugging
                //$sql = $hundredCompanies->toSql();
                //$bindings = $hundredCompanies->getBindings();
                //$rawQuery = vsprintf(str_replace('?', '%s', $sql), array_map('addslashes', $bindings));


                foreach ($hundredCompanies as $company) {

                    $ratingData = getBusinessRating($company->cmpd_cmp_id);

                    $categoryName = null;

                    $owner = $company->companies->users->first()->userDetail;
                    $category = $company->companies->categories->first();
                    if ($category) {
                        $categoryName = [
                            "label" => $category->name,
                            "value" => $category->bcm_id,
                            "sub_category" => [],
                            'logo_img'  => ($category->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $category->logo_file : env('NO_CATEGORY_IMAGE'),
                        ];
                    }

                    $hundredYearsCompanies[] = [
                        "owner_name" => $owner ? $owner->ud_first_name : 'NA',
                        "owner_profile_img" => $owner && $owner->ud_profile_image ? env('UPLOADS_URL') . 'user/' . $owner->ud_profile_image :  env('NO_USER_IMAGE'),
                        "cmpd_id" => $company->cmpd_cmp_id,
                        "cmpd_cmp_id" => $company->cmpd_cmp_id,
                        "cmpd_company_regn_no" => $company->cmpd_company_regn_no,
                        "cmpd_name" => $company->cmpd_name,
                        // "cmpd_description" => $category ? $category->name : "Unclassified",

                        "cmpd_description" => $company->cmpd_description,
                        "category" => $categoryName, //$categoryName ?? "Unclassified",


                        "cmpd_email" => $company->cmpd_email,
                        "cmpd_alternate_email" => $company->cmpd_alternate_email,
                        "cmpd_phone" => $company->cmpd_phone,
                        "cmpd_whatsapp_no" => $company->cmpd_whatsapp_no,
                        "cmpd_logo" => $company->cmpd_logo ? env('UPLOADS_URL') . 'company/' . $company->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE'),
                        "cmpd_address1" => $company->cmpd_address1,
                        "cmpd_address2" => $company->cmpd_address2,
                        "cmpd_address3" => $company->cmpd_address3,
                        "cmpd_estd_year" => $company->cmpd_estd_year,
                        "cmpd_district" => ucwords(strtolower($company->district->name)) ?? '',    // $company->cmpd_district,
                        "cmpd_state" => $company->state->name ?? "",
                        "cmpd_country" => $company->country->name ?? "",
                        "cmpd_pincode" => $company->cmpd_pincode,
                        // "cmpd_category_name" => $category ? $category->name : "Unclassified"
                        "avg_rating" => (float) $ratingData->avg_rating,
                        "total_reviews" => (int) $ratingData->total_reviews,

                    ];
                }

                $apiResponse = [
                    'data' => $hundredYearsCompanies
                ];
            } elseif (!empty($keyword) && $keyword === 'Top Brands') {

                //$offset = ($pageNo - 1) * $perPage;

                # Original logic

                /* $topCompaniesIds       = DB::table('company_click_master')
                    ->select('ccm_cmp_id', DB::raw('COUNT(*) as click_count'))
                    ->groupBy('ccm_cmp_id')
                    ->orderByDesc('click_count')
                    ->pluck('ccm_cmp_id');
                */

                /* just use for development : 6-3-25 */
                $topCompaniesIds =  CompaniesDetail::where('cmpd_status', 1)->pluck('cmpd_cmp_id');


                // Helper::pr($top_companies);
                if ($topCompaniesIds && count($topCompaniesIds) > 0) {


                    $topCompanies = CompaniesDetail::with(['district:id,name', 'state:id,name', 'country:id,name', 'companies', 'companies.users.userDetail', 'companies.categories'])
                        //->select('cmpd_id', 'cmpd_cmp_id', 'cmpd_name', 'cmpd_logo', 'cmpd_estd_year', 'cmpd_status')
                        // ->where('cmpd_status', '=', 1)
                        ->whereIn('cmpd_id', $topCompaniesIds)
                        ->orderBy('cmpd_name', 'ASC')
                        ->offset($offset)
                        ->limit($perPage)
                        ->get();


                    if ($topCompanies->isNotEmpty()) {

                        foreach ($topCompanies as $company) {

                            $owner = $company->companies->users->first()->userDetail ?? null;
                            $category = $company->companies->categories->first() ?? null;

                            if ($category) {
                                $categoryName = [
                                    "label" => $category->name,
                                    "value" => $category->bcm_id,
                                    "sub_category" => [],
                                    'logo_img'  => ($category->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $category->logo_file : env('NO_CATEGORY_IMAGE'),
                                ];
                            }


                            if ($company) {

                                $ratingData = getBusinessRating($company->cmpd_cmp_id);

                                $topCompaniesList[] = [
                                    "owner_name" => $owner ? $owner->ud_first_name : 'NA',
                                    "owner_profile_img" => $owner && $owner->ud_profile_image ? env('UPLOADS_URL') . 'user/' . $owner->ud_profile_image : env('NO_USER_IMAGE'),
                                    "cmpd_id" => $company->cmpd_cmp_id,
                                    "cmpd_cmp_id" => $company->cmpd_cmp_id,
                                    "cmpd_company_regn_no" => $company->cmpd_company_regn_no,
                                    "cmpd_name" => $company->cmpd_name,
                                    // "cmpd_description" => $category ? $category->name : "Unclassified",

                                    "cmpd_description" => $company->cmpd_description,
                                    "category" => $categoryName, //$categoryName ?? "Unclassified",


                                    "cmpd_email" => $company->cmpd_email,
                                    "cmpd_alternate_email" => $company->cmpd_alternate_email,
                                    "cmpd_phone" => $company->cmpd_phone,
                                    "cmpd_whatsapp_no" => $company->cmpd_whatsapp_no,
                                    "cmpd_logo" => $company->cmpd_logo ? env('UPLOADS_URL') . 'company/' . $company->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE'),
                                    "cmpd_address1" => $company->cmpd_address1,
                                    "cmpd_address2" => $company->cmpd_address2,
                                    "cmpd_address3" => $company->cmpd_address3,
                                    "cmpd_estd_year" => $company->cmpd_estd_year,
                                    "cmpd_district" => ucwords(strtolower($company->district->name)) ?? '',  //$company->cmpd_district,
                                    "cmpd_state" => $company->state->name ?? "",
                                    "cmpd_country" => $company->country->name ?? "",
                                    "cmpd_pincode" => $company->cmpd_pincode,
                                    // "cmpd_category_name" => $category ? $category->name : "Unclassified"
                                    "avg_rating" => (float) $ratingData->avg_rating,
                                    "total_reviews" => (int) $ratingData->total_reviews,

                                ];
                            }
                        }
                    } else {
                        $topCompaniesList = [];
                    }
                } else {
                    $topCompaniesList = [];
                }


                $apiResponse = [
                    'data' => $topCompaniesList
                ];
            } else {
                //get the top companies

                /*$topCompaniesClickData       = DB::table('company_click_master')
                                                    ->select('ccm_cmp_id', DB::raw('COUNT(*) as click_count'))
                                                    ->groupBy('ccm_cmp_id')
                                                    ->orderByDesc('click_count')
                                                    ->offset($offset)
                                                    ->limit($perPage)
                                                    ->get();*/
                // Helper::pr($top_companies);
                $topCompanies = [];


                // Build the query
                $companies = DB::table('user_companies_map')
                    ->join('user_details', 'user_companies_map.ucm_um_id', '=', 'user_details.ud_um_id')
                    ->join('companies_details', 'user_companies_map.ucm_cmp_id', '=', 'companies_details.cmpd_cmp_id')
                    ->join('districts', 'districts.id', '=', 'companies_details.cmpd_district')
                    ->join('states as st', 'st.id', '=', 'companies_details.cmpd_state')
                    ->join('countries as cu', 'cu.id', '=', 'companies_details.cmpd_country')
                    ->select(
                        DB::raw("CONCAT(COALESCE(user_details.ud_salutation, ''), ' ', user_details.ud_first_name) AS ud_first_name"),
                        'user_details.ud_profile_image',
                        'companies_details.*',
                        'districts.name as District_Name',
                        'st.name as State_Name',
                        'cu.name as Country_Name'
                    ) // Selecting desired columns
                    ->where('companies_details.cmpd_status', '=', 1) // Condition for active status
                    //->where('companies_details.cmpd_id', '=', $topCompanyData->ccm_cmp_id) // Condition for specific company ID
                    ->orderBy('companies_details.cmpd_name', 'ASC')
                    ->offset($offset)
                    ->limit($perPage)
                    ->get(); // You can also use ->get() for multiple results


                foreach ($companies as $companyDetails) {

                    $category = DB::table('business_category_master')
                        ->join('categories_to_companies', 'business_category_master.bcm_id', '=', 'categories_to_companies.ctc_bcm_id')
                        ->where('categories_to_companies.ctc_cmp_id', $companyDetails->cmpd_cmp_id)->first();
                    // ->value('business_category_master.name'); // Adjust 'name' to the actual column name in your table.

                    if ($category) {
                        $categoryName = [
                            "label" => $category->name,
                            "value" => $category->bcm_id,
                            "sub_category" => [],
                            'logo_img'  => ($category->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $category->logo_file : env('NO_CATEGORY_IMAGE'),
                        ];
                    }


                    $ratingData = getBusinessRating($companyDetails->cmpd_cmp_id);


                    $topCompanies[] = [
                        "owner_name" => $companyDetails->ud_first_name ?? 'NA',
                        "owner_profile_img" => (($companyDetails->ud_profile_image != '') ? env('UPLOADS_URL') . 'user/' . $companyDetails->ud_profile_image : env('NO_USER_IMAGE')),
                        "cmpd_id" => $companyDetails->cmpd_cmp_id,
                        "cmpd_cmp_id" => $companyDetails->cmpd_cmp_id,
                        "cmpd_company_regn_no" => $companyDetails->cmpd_company_regn_no,
                        "cmpd_name" => $companyDetails->cmpd_name,
                        // "cmpd_description" => $categoryName ?? "Unclassified",

                        "cmpd_description" => $companyDetails->cmpd_description,
                        "category" => $categoryName, //$categoryName ?? "Unclassified",


                        "cmpd_email" => $companyDetails->cmpd_email,
                        "cmpd_alternate_email" => $companyDetails->cmpd_alternate_email,
                        "cmpd_phone" => $companyDetails->cmpd_phone,
                        "cmpd_whatsapp_no" => $companyDetails->cmpd_whatsapp_no,
                        "cmpd_logo" => (($companyDetails->cmpd_logo != '') ? env('UPLOADS_URL') . 'company/' . $companyDetails->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE')),
                        "cmpd_address1" => $companyDetails->cmpd_address1 ?? '',
                        "cmpd_address2" => $companyDetails->cmpd_address2 ?? '',
                        "cmpd_address3" => $companyDetails->cmpd_address3 ?? '',
                        "cmpd_estd_year" => $companyDetails->cmpd_estd_year ?? '',
                        "cmpd_district" => ucwords(strtolower($companyDetails->District_Name)) ?? '', //$companyDetails->cmpd_district ?? '',
                        "cmpd_state" => ucwords(strtolower($companyDetails->State_Name)) ?? "",
                        "cmpd_country" => ucwords(strtolower($companyDetails->Country_Name)) ?? "",
                        "cmpd_pincode" => $companyDetails->cmpd_pincode ?? '',
                        // "cmpd_category_name" => $categoryName ?? "Unclassified"
                        "avg_rating" => (float) $ratingData->avg_rating,
                        "total_reviews" => (int) $ratingData->total_reviews,

                    ];
                }

                /*$totalCompanies = DB::table('company_click_master') // Change to your actual table name if necessary
                    ->distinct('ccm_cmp_id') // Specify the column to count unique entries
                    ->count('ccm_cmp_id'); // Count how many unique company_ids there are
                */
                $totalCompanies = CompaniesDetail::count();



                // Calculate total pages
                $totalPages = ceil($totalCompanies / $perPage);

                // Prepare pagination metadata

                // Prepare pagination metadata
                $paginationMeta = [
                    'current_page' => $pageNo,
                    'last_page' => $totalPages,
                    'per_page' => $perPage,
                    'total' => $totalCompanies,
                ];


                // Format and return response with data and pagination info

                $apiResponse['data'] = $topCompanies;
                $apiResponse['meta'] = $paginationMeta;
            }


            http_response_code(200);

            $apiStatus          = true;

            $apiMessage         = 'Data Available !!!';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        } else {

            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function postEnquiryToBusiness(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'authorization', 'business_identifier', 'description_txt', 'enquiry_title',];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, array_merge($requestData, $headerData))) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }

        if ($headerData['key'][0] == env('PROJECT_KEY')) {


            $app_access_token = $headerData['authorization'][0];
            $getTokenValue    = $this->tokenAuth($app_access_token);
            $uId        = $getTokenValue['data'][1] ?? null;
            //check User is available
            $user = UserMaster::find($uId);

            if (!empty($user)) {

                // Manually sanitize inputs
                $sanitizedData = $request->only(['description_txt', 'phone', 'email', 'enquiry_title']);

                $sanitizedData['description_txt'] = strip_tags($sanitizedData['description_txt']); // Strip HTML tags
                //$sanitizedData['email'] = filter_var($sanitizedData['email'], FILTER_SANITIZE_EMAIL) ?? "";

                //$sanitizedData['phone'] = preg_replace('/\D/', '', $sanitizedData['phone']);


                $sanitizedData['enquiry_title'] = strip_tags($sanitizedData['enquiry_title']);
                // Check if user is of type 2 and has a business
                if ($user->um_utm_id == 2) {
                    $userBusiness = $user->companies()->first();
                    if (!$userBusiness) {
                        http_response_code(200);
                        $apiStatus = false;
                        $apiMessage = 'No business found for this type of seller';
                        $apiExtraField = 'response_code';
                        $apiExtraData = http_response_code();
                        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
                        return;
                    }
                }

                // Check if user is of type 1
                if ($user->um_utm_id == 1 || $user->um_utm_id == 2) {
                    // Proceed with the existing logic
                } else {
                    http_response_code(200);
                    $apiStatus = false;
                    $apiMessage = 'This type of user can not send enquiries to business';
                    $apiExtraField = 'response_code';
                    $apiExtraData = http_response_code();
                    $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
                    return;
                }


                //validate business
                $company = CompaniesMaster::find($request->input('business_identifier'));
                if (!$company) {
                    http_response_code(200);
                    $apiStatus = false;
                    $apiMessage = 'No business found to send enquiry';
                    $apiExtraField = 'response_code';
                    $apiExtraData = http_response_code();
                    $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
                    return;
                }

                DB::transaction(function () use ($sanitizedData, $user, $company) {

                    $enquiry = EnquiryMaster::create([
                        'enm_name'  => "",
                        'enm_email' => $sanitizedData['email'] ?? "",
                        'enm_phone' => $sanitizedData['phone'] ?? "",
                        'enm_question_for' => '',
                        'enm_subject'   => $sanitizedData['enquiry_title'],
                        'enm_description' => $sanitizedData['description_txt']
                    ]);

                    // Attaching (sending enquiry)

                    $user->enquiries()->attach($enquiry->enm_id, [
                        'etu_cmp_id' => $company->cmp_id, // Reference to the company
                        'etu_um_id' => $user->um_id, // Reference to the user
                        'etu_created_at' => date('Y-m-d h:i:s')
                    ]);
                });


                http_response_code(200);

                $apiStatus          = true;

                $apiMessage         = 'Enquiries submitted successfully';
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            } else {

                http_response_code(200);

                $apiStatus          = false;

                $apiMessage         = 'User not found, please login';
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }
        } else {

            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function listEnquiriesByUser(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'authorization', 'page_no',];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, array_merge($requestData, $headerData))) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }




        if ($headerData['key'][0] == env('PROJECT_KEY')) {

            $app_access_token = $headerData['authorization'][0];
            $getTokenValue    = $this->tokenAuth($app_access_token);
            $userId        = $getTokenValue['data'][1] ?? null;



            $currentPage = (int) $request->input('page_no', 1); // Get the current page from the request, default to 1

            $perPage = 10; // Set the number of results you want per page


            // Calculate offset
            $offset = ($currentPage - 1) * $perPage;

            //For sellers interpreting as business owners
            $user = UserMaster::where('um_id', $userId)->whereHas('userType', function ($query) {
                $query->where('utm_id', 2);
            })->first();

            if (false && !empty($user)) {

                $companyIds = $user->companies()->pluck('cmp_id');

                $enquiries = DB::table('enquiry_to_user')
                    ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
                    ->join('user_master', 'enquiry_to_user.etu_um_id', '=', 'user_master.um_id')
                    ->join('user_details', 'user_master.um_id', '=', 'user_details.ud_um_id')
                    ->join('companies_details', 'enquiry_to_user.etu_cmp_id', '=', 'companies_details.cmpd_cmp_id')
                    ->select(
                        'enquiry_master.enm_id as enquiry_id',
                        'enquiry_master.enm_subject as enquiry_subject',
                        'enquiry_master.enm_description as enquiry_details',
                        'companies_details.cmpd_cmp_id as company_id',
                        'companies_details.cmpd_name as company_name',
                        'user_master.um_id as user_id',
                        'user_master.um_user_name as user_name',
                        'user_details.ud_first_name',
                        'user_details.ud_last_name',
                        'user_details.ud_profile_image',
                        'companies_details.cmpd_logo',
                        'enquiry_to_user.etu_created_at'
                    )
                    ->whereIn('enquiry_to_user.etu_cmp_id', $companyIds)
                    ->offset($offset)
                    ->limit($perPage)
                    ->get();

                $totalEnquiries = DB::table('enquiry_to_user')
                    ->whereIn('etu_cmp_id', $companyIds)
                    ->count();

                $enquiries_list = [];
                foreach ($enquiries as $enquiry) {
                    $enquiries_list[] = [
                        'user_id' => $enquiry->user_id,
                        'user_name' => $enquiry->ud_first_name . ' ' . $enquiry->ud_last_name,
                        'user_logo' => ($enquiry->ud_profile_image !== '' && !is_null($enquiry->ud_profile_image)) ? env('UPLOADS_URL') . 'user/' . $enquiry->ud_profile_image : env('NO_USER_IMAGE'),
                        'company_logo' => ($enquiry->cmpd_logo !== '' && !is_null($enquiry->cmpd_logo)) ? env('UPLOADS_URL') . 'company/' . $enquiry->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE'),
                        'enquiry_id' => $enquiry->enquiry_id,
                        'company_id' => $enquiry->company_id,
                        'company_name' => $enquiry->company_name,
                        'enquiry_subject' => $enquiry->enquiry_subject,
                        'enquiry_details' => $enquiry->enquiry_details,
                        'create_date_time' => $enquiry->etu_created_at,
                    ];
                }

                $pagination = [
                    'current_page' => $currentPage,
                    'total' => $totalEnquiries,
                    'per_page' => $perPage,
                    'last_page' => ceil($totalEnquiries / $perPage),
                ];

                $apiResponse['enquiries'] = $enquiries_list;
                $apiResponse['meta'] = $pagination;
            } else {

                // $enquiries = DB::table('user_master as u')
                //     ->join('user_details as ud', 'u.um_id', '=', 'ud.ud_um_id')
                //     ->join('enquiry_to_user as p', 'u.um_id', '=', 'p.etu_um_id')
                //     ->join('enquiry_master as e', 'e.enm_id', '=', 'p.etu_enm_id')
                //     ->join('companies_details as c', 'p.etu_cmp_id', '=', 'c.cmpd_cmp_id')
                //     ->select(
                //         'u.um_id as user_id',
                //         'u.um_user_name as user_name',
                //         'e.enm_id as enquiry_id',
                //         'e.enm_subject as enquiry_subject',
                //         'e.enm_description as enquiry_details',
                //         'c.cmpd_cmp_id as company_id',
                //         'c.cmpd_name as company_name',
                //         'ud.ud_first_name',
                //         'ud.ud_last_name',
                //         'ud.ud_profile_image',
                //         'c.cmpd_logo',
                //         'p.etu_created_at'
                //     )
                //     ->where('u.um_id', $userId) // Filter by user ID
                //     ->orderBy('e.enm_id', 'desc')
                //     ->offset($offset)          // Apply offset for pagination
                //     ->limit($perPage)          // Apply limit for pagination
                //     ->get();


                $enquiries =  DB::table('user_master as u')
                    ->join('user_details as ud', 'u.um_id', '=', 'ud.ud_um_id')
                    ->join('enquiry_to_user as p', 'u.um_id', '=', 'p.etu_um_id')
                    ->join('enquiry_master as e', 'e.enm_id', '=', 'p.etu_enm_id')
                    ->leftJoin('companies_details as c', 'p.etu_cmp_id', '=', 'c.cmpd_cmp_id')
                    ->select(
                        'u.um_id as user_id',
                        'u.um_user_name as user_name',
                        'e.enm_id as enquiry_id',
                        'e.enm_type as enquiry_type',
                        'e.enm_subject as enquiry_subject',
                        'e.enm_description as enquiry_details',

                        'e.enm_name as enquirer_name',
                        'e.enm_email as enquirer_email',
                        'e.enm_phone as enquirer_phone',
                        'e.enm_whatsapp as enquirer_whatsapp',
                        'e.enm_address as enm_address',
                        'e.enm_is_myself as isMyself',


                        'c.cmpd_cmp_id as company_id',
                        'c.cmpd_name as company_name',
                        'ud.ud_first_name',
                        'ud.ud_last_name',
                        'ud.ud_profile_image',
                        'c.cmpd_logo',
                        'p.etu_created_at'
                    )
                    ->where('u.um_id', $userId)
                    ->orderBy('e.enm_id', 'desc')
                    ->offset($offset)
                    ->limit($perPage)
                    ->get();

                // return response()->json($enquiries);

                // Get the total count of enquiries for pagination metadata
                $totalEnquiries = DB::table('enquiry_master as e')
                    ->join('enquiry_to_user as p', 'e.enm_id', '=', 'p.etu_enm_id')
                    ->where('p.etu_um_id', $userId)
                    ->count();
                $enquiries_list = [];
                foreach ($enquiries as $enquiry) {
                    array_push($enquiries_list, [
                        'user_id'   => $enquiry->user_id,
                        'user_name' => $enquiry->ud_first_name . ' ' . $enquiry->ud_last_name,
                        'user_logo' => ($enquiry->ud_profile_image !== '' && ! is_null($enquiry->ud_profile_image)) ? env('UPLOADS_URL') . 'user/' . $enquiry->ud_profile_image : env('NO_USER_IMAGE'),
                        'company_logo' => ($enquiry->cmpd_logo !== '' && ! is_null($enquiry->cmpd_logo)) ? env('UPLOADS_URL') . 'company/' . $enquiry->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE'),
                        'enquiry_id' => $enquiry->enquiry_id,
                        'company_id' => $enquiry->company_id ?? null,
                        'company_name' => $enquiry->company_name ?? "",
                        'enquiry_subject' => $enquiry->enquiry_subject,
                        'enquiry_details' => $enquiry->enquiry_details,
                        'create_date_time' => date('M d, Y h:i a', strtotime($enquiry->etu_created_at)),
                        'enquiry_type' => $enquiry->enquiry_type,
                        'enquiry_type_name' => $this->enquiryTypes[$enquiry->enquiry_type],

                        'enquirer_name' => $enquiry->enquirer_name ?? '',
                        'enquirer_email' => $enquiry->enquirer_email ?? '',
                        'enquirer_phone' => $enquiry->enquirer_phone ?? '',
                        'enquirer_whatsapp' => $enquiry->enquirer_whatsapp ?? '',
                        'enquirer_address' => $enquiry->enm_address ?? '',
                        'enquirer_isMySelf ' => $enquiry->isMyself ?? null
                    ]);
                }




                // Prepare pagination metadata
                $pagination = [
                    'current_page' => $currentPage,
                    'total' => $totalEnquiries,
                    'per_page' => $perPage,
                    'last_page' => ceil($totalEnquiries / $perPage),
                ];





                $apiResponse['enquiries'] = $enquiries_list;

                $apiResponse['meta'] = $pagination;


                http_response_code(200);

                $apiStatus          = true;

                $apiMessage         = 'Data Available !!!';
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }
        } else {

            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }


        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function listEnquiriesBySeller(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'authorization', 'page_no',];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, array_merge($requestData, $headerData))) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }

        if ($headerData['key'][0] == env('PROJECT_KEY')) {

            $app_access_token = $headerData['authorization'][0];
            $getTokenValue    = $this->tokenAuth($app_access_token);
            $userId        = $getTokenValue['data'][1] ?? null;


            $currentPage = (int) $request->input('page_no', 1); // Get the current page from the request, default to 1

            $enquiryType = (int) $request->input('enquiry_type', 1);

            $perPage = 10; // Set the number of results you want per page


            // Calculate offset
            $offset = ($currentPage - 1) * $perPage;


            $users = UserMaster::where('um_id', $userId)->whereHas('userType', function ($query) {
                $query->where('utm_id', 2);
            })->get();

            $enquiries_list = [];
            if ($users->isNotEmpty()) {

                foreach ($users as $user) {

                    if ($enquiryType == 2) {
                        $enquiries = DB::table('enquiry_to_user')
                            ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
                            ->join('user_master', 'enquiry_to_user.etu_um_id', '=', 'user_master.um_id')
                            ->join('user_details', 'user_master.um_id', '=', 'user_details.ud_um_id')

                            ->select(
                                'enquiry_master.enm_id as enquiry_id',
                                'enquiry_master.enm_type as enquiry_type',
                                'enquiry_master.enm_subject as enquiry_subject',
                                'enquiry_master.enm_description as enquiry_details',

                                'enquiry_master.enm_name as enquirer_name',
                                'enquiry_master.enm_email as enquirer_email',
                                'enquiry_master.enm_phone as enquirer_phone',
                                'enquiry_master.enm_whatsapp as enquirer_whatsapp',
                                'enquiry_master.enm_address as enm_address',
                                'enquiry_master.enm_is_myself as isMyself',


                                'user_master.um_id as user_id',
                                'user_master.um_user_name as user_name',
                                'user_details.ud_first_name',
                                'user_details.ud_last_name',
                                'user_details.ud_profile_image',

                                'enquiry_to_user.etu_created_at'
                            )
                            ->where('enquiry_to_user.etu_cmp_id', 0)
                            ->whereNot('enquiry_to_user.etu_um_id', $userId)
                            ->orderBy('enquiry_master.enm_id', 'desc')
                            ->paginate($perPage, ['*'], 'page', $currentPage);
                    } else {
                        $companyIds = $user->companies()->pluck('cmp_id');

                        $enquiries = DB::table('enquiry_to_user')
                            ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
                            ->join('user_master', 'enquiry_to_user.etu_um_id', '=', 'user_master.um_id')
                            ->join('user_details', 'user_master.um_id', '=', 'user_details.ud_um_id')
                            ->join('companies_details', 'enquiry_to_user.etu_cmp_id', '=', 'companies_details.cmpd_cmp_id')
                            ->select(
                                'enquiry_master.enm_id as enquiry_id',
                                'enquiry_master.enm_type as enquiry_type',
                                'enquiry_master.enm_subject as enquiry_subject',
                                'enquiry_master.enm_description as enquiry_details',

                                'enquiry_master.enm_name as enquirer_name',
                                'enquiry_master.enm_email as enquirer_email',
                                'enquiry_master.enm_phone as enquirer_phone',
                                'enquiry_master.enm_whatsapp as enquirer_whatsapp',
                                'enquiry_master.enm_address as enm_address',
                                'enquiry_master.enm_is_myself as isMyself',

                                'companies_details.cmpd_cmp_id as company_id',
                                'companies_details.cmpd_name as company_name',
                                'user_master.um_id as user_id',
                                'user_master.um_user_name as user_name',
                                'user_details.ud_first_name',
                                'user_details.ud_last_name',
                                'user_details.ud_profile_image',
                                'companies_details.cmpd_logo',
                                'enquiry_to_user.etu_created_at'
                            )
                            ->whereIn('enquiry_to_user.etu_cmp_id', $companyIds)
                            ->orderBy('enquiry_master.enm_id', 'desc')
                            ->paginate($perPage, ['*'], 'page', $currentPage);
                    }





                    foreach ($enquiries as $enquiry) {

                        $company = $enquiryType == 1 ? CompaniesMaster::find($enquiry->company_id) : null;

                        $enquiries_list[] = [
                            'user_id' => $user->um_id,
                            'user_name' => optional($user->userDetail)->ud_first_name . ' ' . optional($user->userDetail)->ud_last_name,
                            'user_logo' => !empty(optional($user->userDetail)->ud_profile_image)
                                ? env('UPLOADS_URL') . 'user/' . $user->userDetail->ud_profile_image
                                : env('NO_USER_IMAGE'),
                            'company_logo' => ($company && !empty(optional($company->companiesDetail)->cmpd_logo))
                                ? env('UPLOADS_URL') . 'company/' . $company->companiesDetail->cmpd_logo
                                : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE'),
                            'enquiry_id' => $enquiry->enquiry_id ?? null,
                            'company_id' => ($company) ? $company->companiesDetail?->cmpd_cmp_id : null,
                            'company_name' => ($company) ? $company->companiesDetail?->cmpd_name : "",
                            'enquiry_subject' => $enquiry->enquiry_subject ?? '',
                            'enquiry_details' => $enquiry->enquiry_details ?? '',
                            'create_date_time' => format_date($enquiry->etu_created_at) ?? now(),
                            'enquiry_type' => $enquiry->enquiry_type,
                            'enquiry_type_name' => $this->enquiryTypes[$enquiry->enquiry_type],

                            'enquirer_name' => $enquiry->enquirer_name ?? '',
                            'enquirer_email' => $enquiry->enquirer_email ?? '',
                            'enquirer_phone' => $enquiry->enquirer_phone ?? '',
                            'enquirer_whatsapp' => $enquiry->enquirer_whatsapp ?? '',
                            'enquirer_address' => $enquiry->enm_address ?? '',
                            'enquirer_isMySelf ' => $enquiry->isMyself ?? null
                        ];
                    }
                }



                $apiResponse['enquiries'] = $enquiries_list;
                $apiResponse['meta'] = [
                    'current_page' => $enquiries->currentPage(),  //$requestData['page_no'],
                    'total' => $enquiries->total(),
                    'per_page' => $enquiries->perPage(),
                    'last_page' => $enquiries->lastPage(),
                ];
            } else {
                $apiResponse['enquiries'] = $enquiries_list;
                $apiResponse['meta'] = [
                    'current_page' => $requestData['page_no'],
                    'total' => 0,
                    'per_page' => 10,
                    'last_page' => 0,
                ];
            }
            http_response_code(200);
            $apiStatus = true;
            $apiMessage = 'Data Available !!!';
            $apiExtraField = 'response_code';
            $apiExtraData = http_response_code();
        } else {
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function getBusinessDetailsById(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source', 'authorization', 'business_identifier',];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, array_merge($requestData, $headerData))) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }

        if ($headerData['key'][0] == env('PROJECT_KEY')) {

            $app_access_token = $headerData['authorization'][0];
            $getTokenValue    = $this->tokenAuth($app_access_token);
            $userId        = $getTokenValue['data'][1] ?? null;


            $businessId = $request->input('business_identifier');



            // Query to get details related to the master_id

            // $details = DB::table('companies_details')
            //     ->join('user_companies_map', 'companies_details.cmpd_cmp_id', '=', 'user_companies_map.ucm_cmp_id')
            //     ->join('user_details', 'user_companies_map.ucm_um_id', '=', 'user_details.ud_um_id') // Adjust 'id' based on your user_master schema
            //     ->select(
            //         'companies_details.*',
            //         DB::raw("CONCAT(COALESCE(user_details.ud_salutation, ''), ' ', user_details.ud_first_name) AS ud_full_name"),
            //         'user_details.ud_profile_image'
            //     ) // Add any other user details you need
            //     ->where('companies_details.cmpd_cmp_id', $businessId)
            //     ->get();

            $details = DB::table('companies_details as cd')
                ->join('districts as d', 'd.id', '=', 'cd.cmpd_district')
                ->join('states as st', 'st.id', '=', 'cd.cmpd_state')
                ->join('countries as cu', 'cu.id', '=', 'cd.cmpd_country')
                ->join('user_companies_map as ucm', 'cd.cmpd_cmp_id', '=', 'ucm.ucm_cmp_id')
                ->join('user_details as ud', 'ucm.ucm_um_id', '=', 'ud.ud_um_id')
                ->select(
                    'cd.*',
                    'd.name as District_Name',
                    'st.name as State_Name',
                    'cu.name as Country_Name',
                    DB::raw("CONCAT(COALESCE(ud.ud_salutation, ''), ' ', COALESCE(ud.ud_first_name, '')) AS ud_full_name"),
                    'ud.ud_profile_image'
                )
                ->where('cd.cmpd_cmp_id', $businessId)
                ->get();

            $apiResponse = [];

            foreach ($details as  $business) {
                $review_details = null;
                $ratingData = getBusinessRating($business->cmpd_cmp_id);

                // review part start
                $review = ReviewMaster::select('rev_id', 'rev_cmp_id', 'rev_um_id', 'rev_rating', 'rev_comment', 'created_at')->with('user:ud_um_id,ud_first_name,ud_profile_image')->where('rev_um_id', $userId)->where('rev_cmp_id', $businessId)->first();

                if ($review) {
                    $is_given = true;
                    $review_details = [
                        "id" => $review->rev_id,
                        "rating" => (float) $review->rev_rating,
                        "comment" => $review->rev_comment,
                        "comment_on" => format_date($review->created_at),
                        "user_id" => $review->rev_um_id,
                        "user_name" => $review->user->ud_first_name,
                        "user_logo" =>  !empty(optional($review->user)->ud_profile_image)
                            ? env('UPLOADS_URL') . 'user/' . $review->user->ud_profile_image
                            : env('NO_USER_IMAGE'),
                    ];
                } else {
                    $is_given = false;
                }

                // review part end

                $categoryName = null;
                $category = DB::table('business_category_master')
                    ->join('categories_to_companies', 'business_category_master.bcm_id', '=', 'categories_to_companies.ctc_bcm_id')
                    ->where('categories_to_companies.ctc_cmp_id', $business->cmpd_cmp_id)->first();
                // ->value('business_category_master.name'); // Adjust 'name' to the actual column name in your table.
                if ($category) {
                    $categoryName = [
                        "label" => $category->name,
                        "value" => $category->bcm_id,
                        "sub_category" => [],
                        'logo_img'  => ($category->logo_file != '') ? env('UPLOADS_URL') . 'category/' . $category->logo_file : env('NO_CATEGORY_IMAGE'),
                    ];
                }

                $apiResponse = [
                    "owner_name" => $business->ud_full_name ?? 'NA',
                    "owner_profile_img" => (($business->ud_profile_image != '') ? env('UPLOADS_URL') . 'user/' . $business->ud_profile_image : env('NO_USER_IMAGE')),
                    "cmpd_id" => $business->cmpd_cmp_id,
                    "cmpd_cmp_id" => $business->cmpd_cmp_id,
                    "cmpd_company_regn_no" => $business->cmpd_company_regn_no ?? "",
                    "cmpd_name" => $business->cmpd_name ?? "",
                    "cmpd_description" => $business->cmpd_description,
                    "category" => $categoryName, //$categoryName ?? "Unclassified",
                    "cmpd_email" => $business->cmpd_email ?? "",
                    "cmpd_alternate_email" => $business->cmpd_alternate_email ?? "",
                    "cmpd_phone" => $business->cmpd_phone ?? "",
                    "cmpd_whatsapp_no" => $business->cmpd_whatsapp_no ?? "",
                    "cmpd_logo" => (($business->cmpd_logo != '') ? env('UPLOADS_URL') . 'company/' . $business->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE')),
                    "cmpd_address1" => $business->cmpd_address1 ?? "",
                    "cmpd_address2" => $business->cmpd_address2 ?? "",
                    "cmpd_address3" => $business->cmpd_address3 ?? "",
                    "cmpd_estd_year" => $business->cmpd_estd_year ?? "",
                    "cmpd_district" => ucwords(strtolower($business->District_Name)) ?? "",
                    "cmpd_state" => ucwords(strtolower($business->State_Name)) ?? "",
                    "cmpd_country" => ucwords(strtolower($business->Country_Name)) ?? "",
                    "cmpd_pincode" => $business->cmpd_pincode ?? "",
                    "cmpd_status" => $business->cmpd_status,
                    "avg_rating" => (float) $ratingData->avg_rating,
                    "total_reviews" => (int) $ratingData->total_reviews,
                    "is_given" => $is_given,
                    "review_data" => $review_details,

                ];
            }
        } else {

            http_response_code(200);
            $apiStatus          = false;
            $apiMessage         = $this->getResponseCode(http_response_code());
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }


        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    // edit
    public function businessEdit(Request $request)
    {

        $apiStatus     = true;
        $apiMessage    = '';
        $apiResponse   = [];
        $apiExtraField = '';
        $apiExtraData  = '';

        // Get all request data
        $requestData = $request->all();
        $headerData  = $request->header();

        // Validate required fields using Laravel's Validator
        $validator = Validator::make($requestData, [
            'business_id' => 'required|integer',
            'category_id' => 'required',
            'registration_no' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'alternate_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|regex:/^\d{10}$/',
            'whatsapp_no' => 'nullable|string|regex:/^\d{10}$/',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'address3' => 'nullable|string|max:255',
            'estd_year' => 'nullable|integer|digits:4|min:1100|max:' . date('Y'),
            'country'   => 'required|integer',
            'state'     => 'required|integer',
            'district' => 'nullable|integer',
            'pincode' => 'nullable|string|regex:/^\d{6}$/',
            'license_start_datetime' => 'nullable|date|before_or_equal:license_end_datetime',
            'license_end_datetime' => 'nullable|date|after_or_equal:license_start_datetime',
            'license_ref' => 'nullable|string|max:255',
            'pan_image_file' => 'nullable|array',
            'logo' => 'nullable|array',
            'trade_lic_file' => 'nullable|array',
            'gst_certificate_file' => 'nullable|array',
            'pan_no'            => 'nullable|string|max:255',
            'gst_no'            => 'nullable|string|max:255',
            //'renewal_date' => 'required|date',
            // 'logo' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            $apiStatus  = false;
            $apiMessage = $validator->errors()->first(); // Get the first validation error message
            $apiResponse = [
                'errors' => $validator->errors(),
            ];
        } else {
            # Validation Passed #

            if ($headerData['key'][0] == env('PROJECT_KEY')) {

                $app_access_token           = $headerData['authorization'][0];

                $getTokenValue              = $this->tokenAuth($app_access_token);


                if ($getTokenValue['status']) {
                    $uId        = $getTokenValue['data'][1];

                    $cmpId = $requestData['business_id'];
                    // return response()->json($requestData);


                    //Process Profile image
                    $logo_image  = $request->input('logo') ?? null;

                    $panImage   = $request->input('pan_image_file') ?? null;

                    $tradeLicense = $request->input('trade_lic_file') ?? null;

                    $gstCertificate = $request->input('gst_certificate_file') ?? null;

                    $details = CompaniesDetail::where('cmpd_cmp_id', $cmpId)->first();


                    // Define allowed image types and their extensions
                    $allowedTypes = [
                        'image/jpeg' => 'jpeg',
                        'image/jpg'  => 'jpg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                    ];


                    if (!empty($logo_image)) {

                        /*$details = CompaniesDetail::select('cmpd_logo')
                            ->where('cmpd_cmp_id', $cmpId)
                            ->first();*/

                        if ($details && !empty($details->cmpd_logo)) {
                            $filePath = public_path('uploads/company/' . $details->cmpd_logo);

                            if (file_exists($filePath)) {
                                unlink($filePath);
                                Log::info('Deleted logo file: ' . $details->cmpd_logo);
                            } else {
                                Log::warning('Logo file not found for deletion: ' . $details->cmpd_logo);
                            }
                        }

                        $directoryPath = public_path('uploads/company');

                        // Check if the directory does not exist
                        if (!File::exists($directoryPath)) {
                            // Attempt to create the directory
                            if (File::makeDirectory($directoryPath, 0755, true)) {
                            } else {
                                throw new Exception("Directory not exists", 1);
                            }
                        }

                        $logo_image      = $logo_image;
                        $upload_type        = $logo_image[0]['type'];
                        if ($upload_type == 'image/jpeg' || $upload_type == 'image/jpg' || $upload_type == 'image/png' || $upload_type == 'image/gif') {
                            $upload_base64      = $logo_image[0]['base64'];
                            $img                = $upload_base64;
                            $proof_type         = $logo_image[0]['type'];
                            if ($proof_type == 'image/png') {
                                $extn = 'png';
                            } elseif ($proof_type == 'image/jpg') {
                                $extn = 'jpg';
                            } elseif ($proof_type == 'image/jpeg') {
                                $extn = 'jpeg';
                            } elseif ($proof_type == 'image/gif') {
                                $extn = 'gif';
                            } else {
                                $extn = 'png';
                            }
                            $data               = base64_decode($img);
                            $fileName           = uniqid() . '.' . $extn;
                            $file               = 'public/uploads/company/' . $fileName;
                            $success            = file_put_contents($file, $data);
                            $logo_image      = $fileName;
                        }
                    } else {
                        $logo_image = $details->cmpd_logo;
                    }


                    // Check if there is a valid image uploaded
                    if ($panImage && !empty($panImage)) {

                        $directoryPath = public_path('uploads/pan_images');

                        // Check if the directory exists, create it if it doesn't
                        if (!File::exists($directoryPath)) {
                            if (!File::makeDirectory($directoryPath, 0755, true)) {
                                throw new Exception("Failed to create directory.", 1);
                            }
                        }

                        // Get the type and base64 data of the uploaded image
                        $upload_type = $panImage[0]['type'];
                        $upload_base64 = $panImage[0]['base64'];

                        // Check if the uploaded type is allowed
                        if (array_key_exists($upload_type, $allowedTypes)) {
                            // Decode base64 image data
                            $data = base64_decode($upload_base64);
                            $extn = $allowedTypes[$upload_type];

                            // Create a unique file name and path
                            $fileName = Str::random(10) . '.' . $extn; // Use Str::random for uniqueness
                            $file = public_path("uploads/pan_images/{$fileName}");

                            // Save the image to the file
                            if (file_put_contents($file, $data) === false) {
                                throw new Exception("Failed to save the uploaded file.", 1);
                            }
                            if (!empty($details->cmpd_doc_pan_img)) {

                                // Unlink the previous file if it exists
                                $previousFilePath = public_path('uploads/pan_images/' . $details->cmpd_doc_pan_img);
                                if (file_exists($previousFilePath)) {
                                    unlink($previousFilePath);
                                    Log::info('Deleted logo file: ' . $details->cmpd_doc_pan_img);
                                } else {
                                    Log::warning('Logo file not found for deletion: ' . $details->cmpd_doc_pan_img);
                                }
                            }


                            // Update model with the new file name
                            $cmpd_doc_pan_img = $fileName;
                        }
                    } else {
                        $cmpd_doc_pan_img = $details->cmpd_doc_pan_img;
                    }


                    if ($tradeLicense && !empty($tradeLicense)) {

                        $directoryPath = public_path('uploads/trade_licenses');


                        // Check if the directory exists, create it if it doesn't
                        if (!File::exists($directoryPath)) {
                            if (!File::makeDirectory($directoryPath, 0755, true)) {
                                throw new Exception("Failed to create directory.", 1);
                            }
                        }

                        // Get the type and base64 data of the uploaded image
                        $upload_type = $tradeLicense[0]['type'];
                        $upload_base64 = $tradeLicense[0]['base64'];

                        // Check if the uploaded type is allowed
                        if (array_key_exists($upload_type, $allowedTypes)) {
                            // Decode base64 image data
                            $data = base64_decode($upload_base64);
                            $extn = $allowedTypes[$upload_type];

                            // Create a unique file name and path
                            $fileName = Str::random(10) . '.' . $extn; // Use Str::random for uniqueness
                            $file = public_path("uploads/trade_licenses/{$fileName}");

                            // Save the image to the file
                            if (file_put_contents($file, $data) === false) {
                                throw new Exception("Failed to save the uploaded file.", 1);
                            }

                            // Unlink the previous file if it exists
                            if (!empty($details->cmpd_doc_trade_license)) {

                                $previousFilePath = public_path('uploads/trade_licenses/' . $details->cmpd_doc_trade_license);
                                if (file_exists($previousFilePath)) {
                                    unlink($previousFilePath);
                                    Log::info('Deleted logo file: ' . $details->cmpd_doc_trade_license);
                                } else {
                                    Log::warning('Logo file not found for deletion: ' . $details->cmpd_doc_trade_license);
                                }
                            }


                            // Update model with the new file name
                            $cmpd_doc_trade_license = $fileName;
                        }
                    } else {
                        $cmpd_doc_trade_license = $details->cmpd_doc_trade_license;
                    }

                    if ($gstCertificate && sizeof($gstCertificate)) {

                        $directoryPath = public_path('uploads/gst_certificates');

                        // Check if the directory exists, create it if it doesn't
                        if (!File::exists($directoryPath)) {
                            if (!File::makeDirectory($directoryPath, 0755, true)) {
                                throw new Exception("Failed to create directory.", 1);
                            }
                        }

                        // Get the type and base64 data of the uploaded image
                        $upload_type = $gstCertificate[0]['type'];
                        $upload_base64 = $gstCertificate[0]['base64'];

                        // Check if the uploaded type is allowed
                        if (array_key_exists($upload_type, $allowedTypes)) {
                            // Decode base64 image data
                            $data = base64_decode($upload_base64);
                            $extn = $allowedTypes[$upload_type];

                            // Create a unique file name and path
                            $fileName = Str::random(10) . '.' . $extn; // Use Str::random for uniqueness
                            $file = public_path("uploads/gst_certificates/{$fileName}");

                            // Save the image to the file
                            if (file_put_contents($file, $data) === false) {
                                throw new Exception("Failed to save the uploaded file.", 1);
                            }

                            // Unlink the previous file if it exists
                            if (!empty($details->cmpd_doc_gst_certificate)) {

                                $previousFilePath = public_path('uploads/gst_certificates/' . $details->cmpd_doc_gst_certificate);
                                if (file_exists($previousFilePath)) {
                                    unlink($previousFilePath);
                                    Log::info('Deleted logo file: ' . $details->cmpd_doc_gst_certificate);
                                } else {
                                    Log::warning('Logo file not found for deletion: ' . $details->cmpd_doc_gst_certificate);
                                }
                            }


                            // Update model with the new file name
                            $cmpd_doc_gst_certificate = $fileName;
                        }
                    } else {
                        $cmpd_doc_gst_certificate = $details->cmpd_doc_gst_certificate;
                    }


                    $fields =  [
                        'cmpd_company_regn_no' => $requestData['registration_no'],
                        'cmpd_name' => $requestData['name'],
                        'cmpd_description' => $requestData['description'],
                        'cmpd_email' => $requestData['email'],
                        'cmpd_alternate_email' => $requestData['alternate_email'],
                        'cmpd_phone' => $requestData['phone'],
                        'cmpd_whatsapp_no' => $requestData['whatsapp_no'],
                        'cmpd_address1' => $requestData['address1'],
                        'cmpd_address2' => $requestData['address2'],
                        'cmpd_address3' => $requestData['address3'],
                        'cmpd_estd_year' => $requestData['estd_year'],
                        'cmpd_country' => $requestData['country'],
                        'cmpd_state' => $requestData['state'],
                        'cmpd_district' => $requestData['district'],
                        'cmpd_pincode' => $requestData['pincode'],
                        'cmpd_license_start_datetime' => $requestData['license_start_datetime'] ?? null,
                        'cmpd_license_end_datetime' => $requestData['license_end_datetime'] ?? null,
                        'cmpd_license_ref' => $requestData['license_ref'] ?? null,
                        'cmpd_last_renewal_date' => $requestData['renewal_date'] ?? null,
                        'cmpd_logo' => $logo_image,
                        'cmpd_doc_trade_license'    => $cmpd_doc_trade_license,
                        'cmpd_doc_pan_img'      => $cmpd_doc_pan_img,
                        'cmpd_doc_gst_certificate' => $cmpd_doc_gst_certificate,
                        'cmpd_updated_at' => date('Y-m-d H:i:s'),
                        'cmpd_pan_no'    => strip_tags($request->pan_no) ?? null,
                        'cmpd_gst_no'   => strip_tags($request->gst_no) ?? null
                    ];

                    try {
                        DB::beginTransaction();
                        $updatedRows = $details->update($fields);

                        if ($updatedRows) {
                            CategoryToCompany::where('ctc_cmp_id', $cmpId)->delete();


                            CategoryToCompany::insert([
                                'ctc_bcm_id' => $requestData['category_id'],
                                'ctc_cmp_id' => $cmpId,
                            ]);
                            DB::commit();
                            // Successful update
                            $apiStatus = true;
                            $apiMessage = "Update Successful";
                            $apiExtraField = 'response_code';
                            $apiExtraData = 200; // HTTP status code
                        } else {
                            // Update didn't affect any rows (e.g., invalid ID or no changes)
                            throw new Exception("Update failed: no rows affected.");
                        }
                    } catch (Exception $e) {
                        DB::rollBack();
                        // Handle exceptions or errors
                        $apiStatus = false;
                        $apiMessage = $e->getMessage(); // Use exception message as apiMessage
                        $apiExtraField = 'response_code';
                        $apiExtraData = 500; // Internal Server Error
                    }
                } else {
                    $apiStatus                      = false;
                    $apiMessage                     = $getTokenValue['data'];
                }
            } else {
                $apiStatus          = false;
                $apiMessage         = 'Unauthenticate Request !!!';
            }
        }




        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }

    public function businessAdd(Request $request)
    {

        $apiStatus     = true;
        $apiMessage    = '';
        $apiResponse   = [];
        $apiExtraField = '';
        $apiExtraData  = '';

        // Get all request data
        $requestData = $request->all();
        $headerData  = $request->header();


        // Validate required fields using Laravel's Validator
        $validator = Validator::make($requestData, [
            'category_id' => 'required',
            'registration_no' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'alternate_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|regex:/^\d{10}$/',
            'whatsapp_no' => 'nullable|string|regex:/^\d{10}$/',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'address3' => 'nullable|string|max:255',
            'estd_year' => 'nullable|integer|digits:4|min:1100|max:' . date('Y'),
            'country'   => 'required|integer',
            'state'     => 'required|integer',
            'district' => 'nullable|integer',
            'pincode' => 'nullable|string|regex:/^\d{6}$/',
            'license_start_datetime' => 'nullable|date|before_or_equal:license_end_datetime',
            'license_end_datetime' => 'nullable|date|after_or_equal:license_start_datetime',
            'license_ref' => 'nullable|string|max:255',
            'pan_image_file' => 'nullable|array',
            'logo' => 'nullable|array',
            'trade_lic_file' => 'nullable|array',
            'gst_certificate_file' => 'nullable|array',
            'pan_no'            => 'nullable|string|max:255',
            'gst_no'            => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {

            $apiStatus  = false;
            $apiMessage = $validator->errors()->first(); // Get the first validation error message
            $apiResponse = [
                'errors' => $validator->errors(),
            ];
        } else {
            //Validation passed

            if ($headerData['key'][0] == env('PROJECT_KEY')) {

                $app_access_token           = $headerData['authorization'][0];

                $getTokenValue              = $this->tokenAuth($app_access_token);


                if ($getTokenValue['status']) {
                    $uId        = $getTokenValue['data'][1];

                    $user = UserMaster::select(['um_id', 'um_utm_id'])
                        ->with('userDetail:ud_um_id,ud_is_bengali')
                        ->find($uId);

                    if ($user && $user->userDetail && $user->um_utm_id == 2 && $user->userDetail->ud_is_bengali) {

                        //Process Profile image
                        $logo_image  = $request->input('logo') ?? null;

                        $panImage   = $request->input('pan_image_file') ?? null;

                        $tradeLicense = $request->input('trade_lic_file') ?? null;

                        $gstCertificate = $request->input('gst_certificate_file') ?? null;


                        // Define allowed image types and their extensions
                        $allowedTypes = [
                            'image/jpeg' => 'jpeg',
                            'image/jpg'  => 'jpg',
                            'image/png'  => 'png',
                            'image/gif'  => 'gif',
                        ];


                        if (!empty($logo_image)) {



                            $directoryPath = public_path('uploads/company');

                            // Check if the directory does not exist
                            if (!File::exists($directoryPath)) {
                                // Attempt to create the directory
                                if (File::makeDirectory($directoryPath, 0755, true)) {
                                } else {
                                    throw new Exception("Directory not exists", 1);
                                }
                            }

                            $logo_image      = $logo_image;
                            $upload_type        = $logo_image[0]['type'];
                            if ($upload_type == 'image/jpeg' || $upload_type == 'image/jpg' || $upload_type == 'image/png' || $upload_type == 'image/gif') {
                                $upload_base64      = $logo_image[0]['base64'];
                                $img                = $upload_base64;
                                $proof_type         = $logo_image[0]['type'];
                                if ($proof_type == 'image/png') {
                                    $extn = 'png';
                                } elseif ($proof_type == 'image/jpg') {
                                    $extn = 'jpg';
                                } elseif ($proof_type == 'image/jpeg') {
                                    $extn = 'jpeg';
                                } elseif ($proof_type == 'image/gif') {
                                    $extn = 'gif';
                                } else {
                                    $extn = 'png';
                                }
                                $data               = base64_decode($img);
                                $fileName           = uniqid() . '.' . $extn;
                                $file               = 'public/uploads/company/' . $fileName;
                                $success            = file_put_contents($file, $data);
                                $logo_image      = $fileName;
                            }
                        } else {
                            $logo_image = null;
                        }


                        // Check if there is a valid image uploaded
                        if ($panImage && !empty($panImage)) {

                            $directoryPath = public_path('uploads/pan_images');

                            // Check if the directory exists, create it if it doesn't
                            if (!File::exists($directoryPath)) {
                                if (!File::makeDirectory($directoryPath, 0755, true)) {
                                    throw new Exception("Failed to create directory.", 1);
                                }
                            }

                            // Get the type and base64 data of the uploaded image
                            $upload_type = $panImage[0]['type'];
                            $upload_base64 = $panImage[0]['base64'];

                            // Check if the uploaded type is allowed
                            if (array_key_exists($upload_type, $allowedTypes)) {
                                // Decode base64 image data
                                $data = base64_decode($upload_base64);
                                $extn = $allowedTypes[$upload_type];

                                // Create a unique file name and path
                                $fileName = Str::random(10) . '.' . $extn; // Use Str::random for uniqueness
                                $file = public_path("uploads/pan_images/{$fileName}");

                                // Save the image to the file
                                if (file_put_contents($file, $data) === false) {
                                    throw new Exception("Failed to save the uploaded file.", 1);
                                }

                                // Update model with the new file name
                                $cmpd_doc_pan_img = $fileName;
                            }
                        } else {
                            $cmpd_doc_pan_img = null;
                        }


                        if ($tradeLicense && !empty($tradeLicense)) {

                            $directoryPath = public_path('uploads/trade_licenses');


                            // Check if the directory exists, create it if it doesn't
                            if (!File::exists($directoryPath)) {
                                if (!File::makeDirectory($directoryPath, 0755, true)) {
                                    throw new Exception("Failed to create directory.", 1);
                                }
                            }

                            // Get the type and base64 data of the uploaded image
                            $upload_type = $tradeLicense[0]['type'];
                            $upload_base64 = $tradeLicense[0]['base64'];

                            // Check if the uploaded type is allowed
                            if (array_key_exists($upload_type, $allowedTypes)) {
                                // Decode base64 image data
                                $data = base64_decode($upload_base64);
                                $extn = $allowedTypes[$upload_type];

                                // Create a unique file name and path
                                $fileName = Str::random(10) . '.' . $extn; // Use Str::random for uniqueness
                                $file = public_path("uploads/trade_licenses/{$fileName}");

                                // Save the image to the file
                                if (file_put_contents($file, $data) === false) {
                                    throw new Exception("Failed to save the uploaded file.", 1);
                                }

                                // Update model with the new file name
                                $cmpd_doc_trade_license = $fileName;
                            }
                        } else {
                            $cmpd_doc_trade_license = null;
                        }

                        if ($gstCertificate && sizeof($gstCertificate)) {

                            $directoryPath = public_path('uploads/gst_certificates');

                            // Check if the directory exists, create it if it doesn't
                            if (!File::exists($directoryPath)) {
                                if (!File::makeDirectory($directoryPath, 0755, true)) {
                                    throw new Exception("Failed to create directory.", 1);
                                }
                            }

                            // Get the type and base64 data of the uploaded image
                            $upload_type = $gstCertificate[0]['type'];
                            $upload_base64 = $gstCertificate[0]['base64'];

                            // Check if the uploaded type is allowed
                            if (array_key_exists($upload_type, $allowedTypes)) {
                                // Decode base64 image data
                                $data = base64_decode($upload_base64);
                                $extn = $allowedTypes[$upload_type];

                                // Create a unique file name and path
                                $fileName = Str::random(10) . '.' . $extn; // Use Str::random for uniqueness
                                $file = public_path("uploads/gst_certificates/{$fileName}");

                                // Save the image to the file
                                if (file_put_contents($file, $data) === false) {
                                    throw new Exception("Failed to save the uploaded file.", 1);
                                }


                                // Update model with the new file name
                                $cmpd_doc_gst_certificate = $fileName;
                            }
                        } else {
                            $cmpd_doc_gst_certificate = null;
                        }


                        $fields =  [
                            'cmpd_cmp_id' => null,
                            'cmpd_company_regn_no' => $requestData['registration_no'],
                            'cmpd_name' => $requestData['name'],
                            'cmpd_description' => $requestData['description'],
                            'cmpd_email' => $requestData['email'],
                            'cmpd_alternate_email' => $requestData['alternate_email'],
                            'cmpd_phone' => $requestData['phone'],
                            'cmpd_whatsapp_no' => $requestData['whatsapp_no'],
                            'cmpd_address1' => $requestData['address1'],
                            'cmpd_address2' => $requestData['address2'],
                            'cmpd_address3' => $requestData['address3'],
                            'cmpd_estd_year' => $requestData['estd_year'],
                            'cmpd_country'   => $requestData['country'],
                            'cmpd_state'     => $requestData['state'],
                            'cmpd_district' => $requestData['district'],
                            'cmpd_pincode' => $requestData['pincode'],
                            'cmpd_license_start_datetime' => $requestData['license_start_datetime'] ?? null,
                            'cmpd_license_end_datetime' => $requestData['license_end_datetime'] ?? null,
                            'cmpd_license_ref' => $requestData['license_ref'] ?? null,
                            'cmpd_last_renewal_date' => $requestData['renewal_date'] ?? null,
                            'cmpd_logo' => $logo_image,
                            'cmpd_doc_trade_license'    => $cmpd_doc_trade_license,
                            'cmpd_doc_pan_img'      => $cmpd_doc_pan_img,
                            'cmpd_doc_gst_certificate' => $cmpd_doc_gst_certificate,
                            'cmpd_updated_at' => date('Y-m-d H:i:s'),
                            'cmpd_pan_no'    => strip_tags($request->pan_no) ?? null,
                            'cmpd_gst_no'   => strip_tags($request->gst_no) ?? null
                        ];

                        try {
                            DB::beginTransaction();
                            $company = CompaniesMaster::create([]);
                            $fields['cmpd_cmp_id'] = $company->cmp_id;

                            $companyDetails = CompaniesDetail::create($fields);

                            $user = UserMaster::find($uId);

                            $user->companies()->attach($company->cmp_id);

                            if ($companyDetails) {
                                CategoryToCompany::insert([
                                    'ctc_bcm_id' => $requestData['category_id'],
                                    'ctc_cmp_id' => $company->cmp_id,
                                ]);
                                DB::commit();
                                // Successful update
                                $apiStatus = true;
                                $apiMessage = "Business added Successfuly";
                                $apiExtraField = 'response_code';
                                $apiExtraData = 200; // HTTP status code
                            } else {
                                // Update didn't affect any rows (e.g., invalid ID or no changes)
                                throw new Exception("Business add failed: no rows affected.");
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            // Handle exceptions or errors
                            $apiStatus = false;
                            $apiMessage = $e->getMessage(); // Use exception message as apiMessage
                            $apiExtraField = 'response_code';
                            $apiExtraData = 500; // Internal Server Error
                        }
                    } else {
                        $apiStatus          = false;
                        $apiMessage         = 'Operation failed. User must be Bengali';
                    }
                } else {
                    $apiStatus                      = false;
                    $apiMessage                     = $getTokenValue['data'];
                }
            } else {
                $apiStatus          = false;
                $apiMessage         = 'Unauthenticate Request !!!';
            }
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }

    /* after login */
    /*
    Get http response code
    Author : Subhomoy
    */
    private function getResponseCode($code = null)
    {
        if ($code !== null) {
            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Unauthenticated Request !!!';
                    break;
                case 401:
                    $text = 'Token Not Found !!!';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Token Has Expired !!!';
                    break;
                case 404:
                    $text = 'User Not Found !!!';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'All Data Are Not Present !!!';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
            $text = '';
        }
        return $text;
    }
    /*
    Generate JWT tokens for authentication
    Author : Subhomoy
    */
    private static function generateToken($userId, $email, $phone)
    {
        $token      = array(
            'id'                => $userId,
            'email'             => $email,
            'phone'             => $phone,
            'exp'               => time() + (30 * 24 * 60 * 60) // 30 days
        );
        // pr($token);
        return JWT::encode($token, TOKEN_SECRET, 'HS256');
    }
    /*
    Check Authentication
    Author : Subhomoy
    */
    private function tokenAuth($appAccessToken)
    {
        $headers = apache_request_headers();
        if (isset($appAccessToken) && !empty($appAccessToken)) :
            $userdata = $this->matchToken($appAccessToken);
            // pr($userdata);
            if ($userdata['status']) :
                $checkToken =  UserDevice::where('user_id', '=', $userdata['data']->id)->where('app_access_token', '=', $appAccessToken)->first();
                // echo $this->db->last_query();
                // pr($userdata);
                if (!empty($checkToken)) :
                    if ($userdata['data']->exp && $userdata['data']->exp > time()) :
                        $tokenStatus = array(true, $userdata['data']->id, $userdata['data']->email, $userdata['data']->phone, $userdata['data']->exp);
                    else :
                        $tokenStatus = array(false, 'Token Has Expired 1 !!!');
                    endif;
                else :
                    $tokenStatus = array(false, 'Token Has Expired 2 !!!');
                endif;
            else :
                $tokenStatus = array(false, 'Token Not Found !!!');
            endif;
        else :
            $tokenStatus = array(false, 'Token Not Found In Request !!!');
        endif;
        if ($tokenStatus[0]) :
            $this->userId           = $tokenStatus[1];
            $this->userEmail        = $tokenStatus[2];
            $this->userMobile       = $tokenStatus[3];
            $this->userExpiry       = $tokenStatus[4];
            // pr($tokenStatus);
            return array('status' => true, 'data' => $tokenStatus);
        else :
            return array('status' => false, 'data' => $tokenStatus[1]);
        // $this->response_to_json(FALSE, $tokenStatus[1]);
        endif;
    }
    /*
    Match JWT token with user token saved in database
    Author : Subhomoy
    */
    private static function matchToken($token)
    {
        // try{
        //     // $decoded    = JWT::decode($token, TOKEN_SECRET, 'HS256');
        //     $decoded    = JWT::decode($token, new Key(TOKEN_SECRET, 'HS256'));
        //     // pr($decoded);
        // } catch (\Exception $e) {
        //     //echo 'Caught exception: ',  $e->getMessage(), "\n";
        //     return array('status' => FALSE, 'data' => '');
        // }

        // return array('status' => TRUE, 'data' => $decoded);


        try {
            $key = "1234567890qwertyuiopmnbvcxzasdfghjkl";
            $decoded = JWT::decode($token, $key, array('HS256'));
            // $decodedData = (array) $decoded;
        } catch (\Exception $e) {
            //echo 'Caught exception: ',  $e->getMessage(), "\n";
            return array('status' => false, 'data' => '');
        }
        return array('status' => true, 'data' => $decoded);
    }

    public function testRelation(Request $request)
    {

        $users = UserMaster::where('um_id', $request->route('id'))->whereHas('userType', function ($query) {
            $query->where('utm_id', 2);
        })->get();

        $enquiries = [];
        foreach ($users as $user) {
            foreach ($user->enquiries as $enquiry) {
                $company = $enquiry->companies()->first();
                $enquiries[] = [
                    'enquiry_id' => $enquiry->enm_id,
                    'enquiry_subject' => $enquiry->enm_subject,
                    'enquiry_details' => $enquiry->enm_description,
                    'company_id' => $company->cmp_id,
                    'company_name' => $company->companiesDetail->cmpd_name,
                    'company_logo' => $company->companiesDetail->cmpd_logo,
                ];
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Enquiries fetched successfully',
            'data' => $enquiries
        ]);
    }




    // _____________________________________________ SHUBHA -27/02/25 ___________________________________________


    public function changePassword(Request $request)
    {
        $apiStatus     = true;
        $apiMessage    = '';
        $apiResponse   = [];
        $apiExtraField = '';
        $apiExtraData  = '';


        // Validate required fields
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|max:20', # regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/
            'confirm_password' => 'required|same:new_password',
        ]);

        // Set custom attribute names
        $validator->setAttributeNames([
            'old_password' => 'Old Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm Password',
        ]);

        if ($validator->fails()) {
            $apiStatus  = false;
            $apiMessage = $validator->errors()->first(); // Get the first validation error message
            $apiResponse = [
                'errors' => $validator->errors(),
            ];
        } else {
            # Validation Passed #

            // Get all request data
            $requestData = $request->all();
            $headerData  = $request->header();


            if ($headerData['key'][0] == env('PROJECT_KEY')) {

                $app_access_token           = $headerData['authorization'][0];

                $getTokenValue              = $this->tokenAuth($app_access_token);


                if ($getTokenValue['status']) {
                    $uId        = $getTokenValue['data'][1];
                    $user =  UserMaster::find($uId);

                    if ($user && $user->um_status == 2) {

                        if (Hash::check($requestData['old_password'], $user->um_password)) {
                            if ($requestData['new_password'] != $requestData['old_password']) {
                                $user->um_password = Hash::make($requestData['new_password']);
                                $user->save();
                                $apiStatus          = TRUE;
                                $apiMessage         = 'Password Updated Successfully !!!';
                            } else {
                                $apiStatus          = FALSE;
                                $apiMessage         = 'Current & New Password Should Not Be Same !!!';
                            }
                        } else {
                            $apiStatus          = FALSE;
                            $apiMessage         = 'Current Password Doesn\'t Matched !!!';
                        }
                    } else {
                        $apiStatus          = false;
                        $apiMessage         = 'User Not Available !!!';
                    }
                } else {
                    $apiStatus                      = false;
                    $apiMessage                     = $getTokenValue['data'];
                }
            } else {
                $apiStatus          = false;
                $apiMessage         = 'Unauthenticate Request !!!';
            }
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }



    public function profileEdit(Request $request)
    {
        $apiStatus     = true;
        $apiMessage    = '';
        $apiResponse   = [];
        $apiExtraField = '';
        $apiExtraData  = '';


        // Validate required fields
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50',
            'full_name' => 'required|string|max:100',
            'mobile_no' => 'required|digits:10',
            'email_id' => 'required|email|max:100',
            'whatsapp_no' => 'nullable|digits:10',
            'user_addr_1' => 'required|string|max:255',
            'user_addr_2' => 'nullable|string|max:255',
            'pin_code' => 'required|digits:6',
            'is_bengali' => 'required|in:0,1', // Ensures only '0' or '1' is allowed
            'country_id' => 'required|integer',
            'state_id' => 'required|integer',
            'district_id' => 'required|integer',
            'profile_image' => 'nullable'
        ]);

        // Set custom attribute names
        $validator->setAttributeNames([
            'username' => 'Username',
            'full_name' => 'Full Name',
            'mobile_no' => 'Mobile Number',
            'email_id' => 'Email Address',
            'whatsapp_no' => 'WhatsApp Number',
            'user_addr_1' => 'Address Line 1',
            'user_addr_2' => 'Address Line 2',
            'pin_code' => 'PIN Code',
            'is_bengali' => 'Bengali Status',
            'country_id' => 'Country',
            'state_id' => 'State',
            'district_id' => 'District',
            'profile_image' => 'Profile Image'
        ]);

        if ($validator->fails()) {
            $apiStatus  = false;
            $apiMessage = $validator->errors()->first(); // Get the first validation error message
            $apiResponse = [
                'errors' => $validator->errors(),
            ];
        } else {
            # Validation Passed #

            // Get all request data
            $requestData = $request->all();
            $headerData  = $request->header();


            if ($headerData['key'][0] == env('PROJECT_KEY')) {

                $app_access_token           = $headerData['authorization'][0];

                $getTokenValue              = $this->tokenAuth($app_access_token);


                if ($getTokenValue['status']) {
                    $uId  = $getTokenValue['data'][1];

                    try {
                        DB::beginTransaction();

                        $user = UserMaster::with('userDetail')->find($uId);

                        if ($user && $user->userDetail && $user->um_status == 2) {
                            $oldImg = $user->userDetail->ud_profile_image ?? null;
                            //___________________ Process Profile image Start ___________________
                            $profile_image = $request->input('profile_image') ?? null;

                            if (!empty($profile_image)) {

                                $directoryPath = public_path('uploads/user');

                                // Check if the directory does not exist
                                if (!File::exists($directoryPath)) {
                                    // Attempt to create the directory
                                    if (!File::makeDirectory($directoryPath, 0755, true)) {
                                        throw new Exception("Failed to create directory: " . $directoryPath);
                                    }
                                }

                                $upload_type = $profile_image[0]['type'];
                                if (in_array($upload_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
                                    $upload_base64 = $profile_image[0]['base64'];
                                    $img = $upload_base64;
                                    $proof_type = $profile_image[0]['type'];

                                    // Determine extension based on image type
                                    if ($proof_type == 'image/png') {
                                        $extn = 'png';
                                    } elseif ($proof_type == 'image/jpg') {
                                        $extn = 'jpg';
                                    } elseif ($proof_type == 'image/jpeg') {
                                        $extn = 'jpeg';
                                    } elseif ($proof_type == 'image/gif') {
                                        $extn = 'gif';
                                    } else {
                                        $extn = 'png';
                                    }

                                    $data = base64_decode($img);
                                    $fileName = uniqid() . '.' . $extn;
                                    $file = $directoryPath . '/' . $fileName;
                                    $success = file_put_contents($file, $data);

                                    $profile_image = $fileName;

                                    // Remove old image if exists
                                    if ($oldImg) {
                                        $oldImagePath = $directoryPath . '/' . $oldImg;
                                        if (File::exists($oldImagePath)) {
                                            File::delete($oldImagePath);
                                        }
                                    }
                                } else if ($oldImg != null) {
                                    $profile_image = $oldImg;
                                }
                            } else if ($oldImg != null) {
                                $profile_image = $oldImg;
                            }
                            //___________________ Process Profile image End ___________________

                            $user->userDetail->update([
                                'ud_first_name'      => $requestData['full_name'],
                                'ud_whatsapp_no'     => $requestData['whatsapp_no'],
                                'ud_addr_1'          => $requestData['user_addr_1'],
                                'ud_addr_2'          => $requestData['user_addr_2'],
                                'ud_pincode'           => $requestData['pin_code'],
                                'ud_is_bengali'      => $requestData['is_bengali'],
                                'ud_district_id'     => $requestData['district_id'],
                                'ud_state_id'        => $requestData['state_id'],
                                'ud_country_id'      => $requestData['country_id'],
                                'ud_profile_image'   => $profile_image,
                                'ud_updated_at'      => now()
                            ]);
                        }

                        DB::commit();
                        $apiStatus = true;
                        $apiMessage = 'Update successful !!!';
                    } catch (Exception $e) {
                        DB::rollback();
                        $apiStatus = false;
                        $apiMessage = 'Update failed: ' . $e->getMessage();
                    }
                } else {
                    $apiStatus                      = false;
                    $apiMessage                     = $getTokenValue['data'];
                }
            } else {
                $apiStatus          = false;
                $apiMessage         = 'Unauthenticate Request !!!';
            }
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }


    public function referralEnquiryToBusiness(Request $request)
    {
        $apiStatus     = true;
        $apiMessage    = '';
        $apiResponse   = [];
        $apiExtraField = '';
        $apiExtraData  = '';


        $rules = [
            'business_identifier' => 'required|string|max:50',
            'description_text'    => 'required|string|max:1000',
            'enquiry_title'       => 'required|string|max:255',
            'enquiry_self'        => 'required|integer|in:0,1',
        ];

        // If enquiry_self is NOT 1, validate the rest of the fields
        if ($request->enquiry_self != 1) {
            $rules = array_merge($rules, [
                'enquirer_name'     => 'required|string|max:100',
                'enquirer_email'    => 'nullable|email|max:255',
                'enquirer_phone'    => 'required|digits:10',
                'enquirer_whatsapp' => 'nullable|digits:10',
                'enquirer_address'  => 'nullable|string|max:500',
            ]);
        }


        // Apply validation
        $validator = Validator::make($request->all(), $rules);

        // Set custom attribute names
        $validator->setAttributeNames([
            'business_identifier' => 'Business Identifier',
            'description_text'    => 'Description',
            'enquiry_title'       => 'Enquiry Title',
            'enquiry_self'        => 'Enquiry Self',
            'enquirer_name'       => 'Enquirer Name',
            'enquirer_email'      => 'Enquirer Email',
            'enquirer_phone'      => 'Enquirer Phone',
            'enquirer_whatsapp'   => 'Enquirer WhatsApp',
            'enquirer_address'    => 'Enquirer Address',
        ]);

        if ($validator->fails()) {
            $apiStatus  = false;
            $apiMessage = $validator->errors()->first(); // Get the first validation error message
            $apiResponse = [
                'errors' => $validator->errors(),
            ];
        } else {
            # Validation Passed #

            // Get all request data
            $requestData = $request->all();
            $headerData  = $request->header();


            if ($headerData['key'][0] == env('PROJECT_KEY')) {

                $app_access_token           = $headerData['authorization'][0];

                $getTokenValue              = $this->tokenAuth($app_access_token);


                if ($getTokenValue['status']) {
                    $uId  = $getTokenValue['data'][1];

                    try {
                        DB::beginTransaction();
                        $postParam = [];
                        $user = UserMaster::find($uId);
                        $company = CompaniesMaster::find($requestData['business_identifier']);

                        if (!$company) {
                            http_response_code(200);
                            $apiStatus = false;
                            $apiMessage = 'No business found to send enquiry';
                            $apiExtraField = 'response_code';
                            $apiExtraData = http_response_code();
                            $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
                            return;
                        }
                        // return response()->json($user->userDetail);
                        $postParam['enm_subject'] =  $requestData['enquiry_title'];
                        $postParam['enm_description'] = $requestData['description_text'];
                        $postParam['enm_type'] = 1; // private
                        $postParam['enm_is_myself'] = $requestData['enquiry_self'];


                        if ($requestData['enquiry_self'] == 1 && $user && $user->userDetail) {
                            $postParam['enm_name'] =  $user->userDetail->ud_first_name;
                            $postParam['enm_email'] = $user->um_email_id;
                            $postParam['enm_phone'] = $user->um_mobile_no;
                            $postParam['enm_whatsapp'] = $user->userDetail->ud_whatsapp_no;
                            $postParam['enm_address'] =  $user->userDetail->ud_addr_1  . ', Pin: ' . $user->userDetail->ud_pincode;
                        } else {
                            $postParam['enm_name'] =  $requestData['enquirer_name'];
                            $postParam['enm_email'] = $requestData['enquirer_email'];
                            $postParam['enm_phone'] = $requestData['enquirer_phone'];
                            $postParam['enm_whatsapp'] = $requestData['enquirer_whatsapp'];
                            $postParam['enm_address'] = $requestData['enquirer_address'];
                        }


                        // return response()->json($postParam);


                        $enquiry = EnquiryMaster::create($postParam);

                        // Attaching (sending enquiry)

                        $user->enquiries()->attach($enquiry->enm_id, [
                            'etu_cmp_id' => $company->cmp_id, // Reference to the company
                            'etu_um_id' => $user->um_id, // Reference to the user
                            'etu_created_at' => date('Y-m-d h:i:s')
                        ]);


                        DB::commit();
                        $apiStatus = true;
                        $apiMessage = 'Send successful !!!';
                        // $apiResponse['data'] = $user;
                    } catch (Exception $e) {
                        DB::rollback();
                        $apiStatus = false;
                        $apiMessage = 'Send failed: ' . $e->getMessage();
                    }
                } else {
                    $apiStatus                      = false;
                    $apiMessage                     = $getTokenValue['data'];
                }
            } else {
                $apiStatus          = false;
                $apiMessage         = 'Unauthenticate Request !!!';
            }
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }


    public function referralEnquiryToPublic(Request $request)
    {
        $apiStatus     = true;
        $apiMessage    = '';
        $apiResponse   = [];
        $apiExtraField = '';
        $apiExtraData  = '';


        $rules = [
            'description_text'    => 'required|string|max:1000',
            'enquiry_title'       => 'required|string|max:255',
            'enquiry_self'        => 'required|integer|in:0,1',
        ];

        // If enquiry_self is NOT 1, validate the rest of the fields
        if ($request->enquiry_self != 1) {
            $rules = array_merge($rules, [
                'enquirer_name'     => 'required|string|max:100',
                'enquirer_email'    => 'nullable|email|max:255',
                'enquirer_phone'    => 'required|digits:10',
                'enquirer_whatsapp' => 'nullable|digits:10',
                'enquirer_address'  => 'nullable|string|max:500',
            ]);
        }


        // Apply validation
        $validator = Validator::make($request->all(), $rules);

        // Set custom attribute names
        $validator->setAttributeNames([
            'description_text'    => 'Description',
            'enquiry_title'       => 'Enquiry Title',
            'enquiry_self'        => 'Enquiry Self',
            'enquirer_name'       => 'Enquirer Name',
            'enquirer_email'      => 'Enquirer Email',
            'enquirer_phone'      => 'Enquirer Phone',
            'enquirer_whatsapp'   => 'Enquirer WhatsApp',
            'enquirer_address'    => 'Enquirer Address',
        ]);

        if ($validator->fails()) {
            $apiStatus  = false;
            $apiMessage = $validator->errors()->first(); // Get the first validation error message
            $apiResponse = [
                'errors' => $validator->errors(),
            ];
        } else {
            # Validation Passed #

            // Get all request data
            $requestData = $request->all();
            $headerData  = $request->header();


            if ($headerData['key'][0] == env('PROJECT_KEY')) {

                $app_access_token           = $headerData['authorization'][0];

                $getTokenValue              = $this->tokenAuth($app_access_token);


                if ($getTokenValue['status']) {
                    $uId  = $getTokenValue['data'][1];

                    try {
                        DB::beginTransaction();
                        $postParam = [];
                        $user = UserMaster::find($uId);

                        $postParam['enm_subject'] =  $requestData['enquiry_title'];
                        $postParam['enm_description'] = $requestData['description_text'];
                        $postParam['enm_type'] = 2; // public
                        $postParam['enm_is_myself'] = $requestData['enquiry_self'];


                        if ($requestData['enquiry_self'] == 1 && $user && $user->userDetail) {
                            $postParam['enm_name'] =  $user->userDetail->ud_first_name;
                            $postParam['enm_email'] = $user->um_email_id;
                            $postParam['enm_phone'] = $user->um_mobile_no;
                            $postParam['enm_whatsapp'] = $user->userDetail->ud_whatsapp_no;
                            $postParam['enm_address'] =  $user->userDetail->ud_addr_1  . ', Pin: ' . $user->userDetail->ud_pincode;
                        } else {
                            $postParam['enm_name'] =  $requestData['enquirer_name'];
                            $postParam['enm_email'] = $requestData['enquirer_email'];
                            $postParam['enm_phone'] = $requestData['enquirer_phone'];
                            $postParam['enm_whatsapp'] = $requestData['enquirer_whatsapp'];
                            $postParam['enm_address'] = $requestData['enquirer_address'];
                        }


                        // return response()->json($postParam);


                        $enquiry = EnquiryMaster::create($postParam);

                        // Attaching (sending enquiry)

                        $user->enquiries()->attach($enquiry->enm_id, [
                            'etu_cmp_id' => 0, // Reference to the company
                            'etu_um_id' => $user->um_id, // Reference to the user
                            'etu_created_at' => date('Y-m-d h:i:s')
                        ]);


                        DB::commit();
                        $apiStatus = true;
                        $apiMessage = 'Send successful !!!';
                        // $apiResponse['data'] = $user;
                    } catch (Exception $e) {
                        DB::rollback();
                        $apiStatus = false;
                        $apiMessage = 'Send failed: ' . $e->getMessage();
                    }
                } else {
                    $apiStatus                      = false;
                    $apiMessage                     = $getTokenValue['data'];
                }
            } else {
                $apiStatus          = false;
                $apiMessage         = 'Unauthenticate Request !!!';
            }
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }

    public function enquiriesDetails(Request $request)
    {
        $apiStatus     = true;
        $apiMessage    = '';
        $apiResponse   = [];
        $apiExtraField = '';
        $apiExtraData  = '';


        // Validate required fields
        $validator = Validator::make($request->all(), [
            'enquiry_id' => 'required',
        ]);

        // Set custom attribute names
        $validator->setAttributeNames([
            'enquiry_id' => 'Enquiry Id',
        ]);

        if ($validator->fails()) {
            $apiStatus  = false;
            $apiMessage = $validator->errors()->first(); // Get the first validation error message
            $apiResponse = [
                'errors' => $validator->errors(),
            ];
        } else {
            # Validation Passed #

            // Get all request data
            $requestData = $request->all();
            $headerData  = $request->header();


            if ($headerData['key'][0] == env('PROJECT_KEY')) {

                $app_access_token           = $headerData['authorization'][0];

                $getTokenValue              = $this->tokenAuth($app_access_token);

                if ($getTokenValue['status']) {
                    $uId        = $getTokenValue['data'][1];
                    // $user =  UserMaster::find($uId);
                    $enquiryId = $request->input('enquiry_id');
                    // $enquiry = EnquiryMaster::find($enquiryId);
                    $enquiry =  DB::table('user_master as u')
                        ->join('user_details as ud', 'u.um_id', '=', 'ud.ud_um_id')
                        ->join('enquiry_to_user as p', 'u.um_id', '=', 'p.etu_um_id')
                        ->join('enquiry_master as e', 'e.enm_id', '=', 'p.etu_enm_id')
                        ->leftJoin('companies_details as c', 'p.etu_cmp_id', '=', 'c.cmpd_cmp_id')
                        ->select(
                            'u.um_id as user_id',
                            'u.um_user_name as user_name',
                            'e.enm_id as enquiry_id',
                            'e.enm_type as enquiry_type',
                            'e.enm_subject as enquiry_subject',
                            'e.enm_description as enquiry_details',

                            'e.enm_name as enquirer_name',
                            'e.enm_email as enquirer_email',
                            'e.enm_phone as enquirer_phone',
                            'e.enm_whatsapp as enquirer_whatsapp',
                            'e.enm_address as enm_address',
                            'e.enm_is_myself as isMyself',


                            'c.cmpd_cmp_id as company_id',
                            'c.cmpd_name as company_name',
                            'ud.ud_first_name',
                            'ud.ud_last_name',
                            'ud.ud_profile_image',
                            'c.cmpd_logo',
                            'p.etu_created_at'
                        )->where('e.enm_id', $enquiryId)->first();




                    if ($enquiry) {

                        $apiResponse = [
                            // 'id' => $enquiry->enm_id,
                            // 'subject' => $enquiry->enm_subject ?? '',
                            // 'description' => $enquiry->enm_description ?? '',
                            // 'enquirer_name' => $enquiry->enm_name ?? '',
                            // 'enquirer_email' => $enquiry->enm_email ?? '',
                            // 'enquirer_phone' => $enquiry->enm_phone ?? '',
                            // 'enquirer_whatsapp' => $enquiry->enm_whatsapp ?? '',
                            // 'enquirer_address' => $enquiry->enm_address ?? '',
                            // 'type_id' => $enquiry->enm_type ?? '',
                            // 'type_name' => $this->enquiryTypes[$enquiry->enm_type],
                            // 'type_id' => $enquiry->enm_type ?? '',
                            // 'is_myself' => $enquiry->enm_is_myself ?? '',
                            // 'send_on' => $enquiry->enm_created_at ? format_date($enquiry->enm_created_at) : null,






                            'user_id'   => $enquiry->user_id,
                            'user_name' => $enquiry->ud_first_name . ' ' . $enquiry->ud_last_name,
                            'user_logo' => ($enquiry->ud_profile_image !== '' && ! is_null($enquiry->ud_profile_image)) ? env('UPLOADS_URL') . 'user/' . $enquiry->ud_profile_image : env('NO_USER_IMAGE'),
                            'company_logo' => ($enquiry->cmpd_logo !== '' && ! is_null($enquiry->cmpd_logo)) ? env('UPLOADS_URL') . 'company/' . $enquiry->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE'),
                            'enquiry_id' => $enquiry->enquiry_id,
                            'company_id' => $enquiry->company_id ?? null,
                            'company_name' => $enquiry->company_name ?? "",
                            'enquiry_subject' => $enquiry->enquiry_subject,
                            'enquiry_details' => $enquiry->enquiry_details,
                            'create_date_time' => date('M d, Y h:i a', strtotime($enquiry->etu_created_at)),
                            'enquiry_type' => $enquiry->enquiry_type,
                            'enquiry_type_name' => $this->enquiryTypes[$enquiry->enquiry_type],

                            'enquirer_name' => $enquiry->enquirer_name ?? '',
                            'enquirer_email' => $enquiry->enquirer_email ?? '',
                            'enquirer_phone' => $enquiry->enquirer_phone ?? '',
                            'enquirer_whatsapp' => $enquiry->enquirer_whatsapp ?? '',
                            'enquirer_address' => $enquiry->enm_address ?? '',
                            'enquirer_isMySelf ' => $enquiry->isMyself ?? null




                        ];
                    } else {
                        $apiStatus          = false;
                        $apiMessage         = 'Enquiry Not Available !!!';
                    }
                } else {
                    $apiStatus                      = false;
                    $apiMessage                     = $getTokenValue['data'];
                }
            } else {
                $apiStatus          = false;
                $apiMessage         = 'Unauthenticate Request !!!';
            }
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }





    public function saveBusinessReview(Request $request)
    {
        // Initialize response variables
        $apiStatus   = false;
        $apiMessage  = '';
        $apiResponse = [];
        $canProceed  = true;

        // Retrieve header data
        $headerData = $request->header();

        // Validate project key
        if (empty($headerData['key'][0]) || $headerData['key'][0] !== env('PROJECT_KEY')) {
            $apiMessage = 'Unauthenticated Request !!!';
            $canProceed = false;
        }

        // Validate authorization token header
        if ($canProceed && empty($headerData['authorization'][0])) {
            $apiMessage = 'Missing Authorization Token';
            $canProceed = false;
        }

        // Token authentication and get user id
        if ($canProceed) {
            $appAccessToken = $headerData['authorization'][0];
            $getTokenValue  = $this->tokenAuth($appAccessToken);
            if (!$getTokenValue['status']) {
                $apiMessage = $getTokenValue['data'];
                $canProceed = false;
            }
        }

        if ($canProceed) {
            $user = UserMaster::find($getTokenValue['data'][1]);
            $userType = $user ? $user->um_utm_id : 0;
            if (!in_array($userType, [1, 2])) {
                $apiMessage = "User is not authorized to proceed with this request.";
                $canProceed = false;
            }
        }

        // If token is valid, get the dynamic user id and validate the request
        if ($canProceed) {
            $uId = $getTokenValue['data'][1];

            $validator = Validator::make($request->all(), [
                'business_identifier' => [
                    'required',
                    Rule::unique('reviews', 'rev_cmp_id')->where(function ($query) use ($uId) {
                        return $query->where('rev_um_id', $uId);
                    }),
                ],
                'business_rating'  => 'required|numeric|between:1,5',
                'business_reviews' => 'nullable|string|min:3|max:1000'
            ]);

            // Set custom attribute names for clarity
            $validator->setAttributeNames([
                'business_identifier' => 'Identifier id',
                'business_rating'     => 'Business Rating',
                'business_reviews'    => 'Business Reviews'
            ]);

            if ($validator->fails()) {
                $apiMessage  = $validator->errors()->first();
                $apiResponse = ['errors' => $validator->errors()];
                $canProceed  = false;
            }
        }

        // Process the review creation if all validations pass
        if ($canProceed) {
            $requestData = $request->all();
            try {
                DB::beginTransaction();

                $review = ReviewMaster::create([
                    'rev_um_id'   => $getTokenValue['data'][1],
                    'rev_cmp_id'  => $requestData['business_identifier'],
                    'rev_rating'  => $requestData['business_rating'],
                    'rev_comment' => $requestData['business_reviews'],
                ]);

                $insertedId = ReviewMaster::latest()->first()->rev_id;

                DB::commit();
                $apiStatus  = true;
                $apiMessage = 'Review created successfully';

                // user  revew obj
                $review = ReviewMaster::with('user')->where('rev_id', $insertedId)->first();
                $ratingData = getBusinessRating($review->rev_cmp_id);
                $apiResponse = [
                    'review' => [
                        "id" => $review->rev_id,
                        "rating" => $review->rev_rating,
                        "comment" => $review->rev_comment,
                        "comment_on" => format_date($review->created_at),
                        "user_id" => $review->rev_um_id,
                        "user_name" => $review->user->ud_first_name,
                        "user_logo" =>  !empty($review->user->ud_profile_image)
                            ? env('UPLOADS_URL') . 'user/' .  $review->user->ud_profile_image
                            : env('NO_USER_IMAGE'),
                    ],
                    'ratting' => [
                        "avg_rating"            => (float)$ratingData->avg_rating,
                        "total_reviews"         => (int) $ratingData->total_reviews,
                    ]
                ];
                // user  revew obj
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Review creation failed: ' . $e->getMessage());
                $apiMessage = 'Review creation failed. Please try again later';
            }
        }

        // response call
        return $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }


    public function updateBusinessReview(Request $request)
    {
        // Initialize response variables
        $apiStatus   = false;
        $apiMessage  = '';
        $apiResponse = [];
        $canProceed  = true;

        // Retrieve header data
        $headerData = $request->header();

        // Validate project key
        if (empty($headerData['key'][0]) || $headerData['key'][0] !== env('PROJECT_KEY')) {
            $apiMessage = 'Unauthenticated Request !!!';
            $canProceed = false;
        }

        // Validate authorization token header
        if ($canProceed && empty($headerData['authorization'][0])) {
            $apiMessage = 'Missing Authorization Token';
            $canProceed = false;
        }

        // Token authentication and get user id
        if ($canProceed) {
            $appAccessToken = $headerData['authorization'][0];
            $getTokenValue  = $this->tokenAuth($appAccessToken);
            if (!$getTokenValue['status']) {
                $apiMessage = $getTokenValue['data'];
                $canProceed = false;
            }
        }

        // If token is valid, get the dynamic user id and validate the request
        if ($canProceed) {
            $uId = $getTokenValue['data'][1];

            $validator = Validator::make($request->all(), [
                'review_id'        => 'required|integer',
                'business_rating'  => 'required|numeric|between:1,5',
                'business_reviews' => 'nullable|string|min:3|max:1000'
            ]);

            // Set custom attribute names for clarity
            $validator->setAttributeNames([
                'review_id'           => 'Review id',
                'business_rating'     => 'Business Rating',
                'business_reviews'    => 'Business Reviews'
            ]);

            if ($validator->fails()) {
                $apiMessage  = $validator->errors()->first();
                $apiResponse = ['errors' => $validator->errors()];
                $canProceed  = false;
            }
        }

        // Process the review creation if all validations pass
        if ($canProceed) {
            $requestData = $request->all();
            try {
                DB::beginTransaction();

                ReviewMaster::where('rev_id', $requestData['review_id'])->update([
                    'rev_rating'  => $requestData['business_rating'],
                    'rev_comment' => $requestData['business_reviews'],
                    'updated_at'  => now(),
                ]);

                DB::commit();
                $apiStatus  = true;
                $apiMessage = 'Review update successfully';

                // user  revew obj
                $review = ReviewMaster::with('user')->where('rev_id', $requestData['review_id'])->first();
                $ratingData = getBusinessRating($review->rev_cmp_id);
                $apiResponse = [
                    'review' => [
                        "id" => $review->rev_id,
                        "rating" => $review->rev_rating,
                        "comment" => $review->rev_comment,
                        "comment_on" => format_date($review->created_at),
                        "user_id" => $review->rev_um_id,
                        "user_name" => $review->user->ud_first_name,
                        "user_logo" =>  !empty($review->user->ud_profile_image)
                            ? env('UPLOADS_URL') . 'user/' .  $review->user->ud_profile_image
                            : env('NO_USER_IMAGE'),
                    ],
                    'ratting' => [
                        "avg_rating"            => (float) $ratingData->avg_rating,
                        "total_reviews"         => (int) $ratingData->total_reviews,
                    ]
                ];
                // user  revew obj

            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Review update failed: ' . $e->getMessage());
                $apiMessage = 'Review update failed. Please try again later';
            }
        }

        // response call
        return $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }



    public function businessReviewList(Request $request)
    {
        // Initialize response variables
        $apiStatus   = false;
        $apiMessage  = '';
        $apiResponse = [];
        $canProceed  = true;

        // Retrieve header data
        $headerData = $request->header();

        // Validate project key
        if (empty($headerData['key'][0]) || $headerData['key'][0] !== env('PROJECT_KEY')) {
            $apiMessage = 'Unauthenticated Request !!!';
            $canProceed = false;
        }

        // Validate authorization token header
        if ($canProceed && empty($headerData['authorization'][0])) {
            $apiMessage = 'Missing Authorization Token';
            $canProceed = false;
        }

        // Token authentication and get user id
        if ($canProceed) {
            $appAccessToken = $headerData['authorization'][0];
            $getTokenValue  = $this->tokenAuth($appAccessToken);
            if (!$getTokenValue['status']) {
                $apiMessage = $getTokenValue['data'];
                $canProceed = false;
            }
        }

        // If token is valid, get the dynamic user id and validate the request
        if ($canProceed) {

            $validator = Validator::make($request->all(), [
                'business_identifier'  => 'required|integer',
                'page_no' => 'required|integer',
                'per_page' => 'required|integer',
            ]);

            // Set custom attribute names for clarity
            $validator->setAttributeNames([
                'business_identifier' => 'Identifier id',
                'page_no'     => 'Page index',
                'per_page'     => 'Per page',
            ]);

            if ($validator->fails()) {
                $apiMessage  = $validator->errors()->first();
                $apiResponse = ['errors' => $validator->errors()];
                $canProceed  = false;
            }
        }

        // Process the review creation if all validations pass
        if ($canProceed) {
            $requestData = $request->all();
            $uId = $getTokenValue['data'][1];

            $bid = (int) $request->input('business_identifier');
            $currentPage = (int) $request->input('page_no', 1);
            $perPage = (int) $request->input('per_page', 10);
            // $perPage=10;
            // Calculate offset
            $offset = ($currentPage - 1) * $perPage;


            try {
                // $reviews = ReviewMaster::with('user:ud_um_id,ud_first_name,ud_profile_image')
                //     ->where('rev_cmp_id', $bid)
                //     ->orderBy('rev_id', 'DESC')
                //     ->paginate($perPage, ['*'], 'page', $currentPage);

                $reviews = ReviewMaster::with('user:ud_um_id,ud_first_name,ud_profile_image')
                    ->where('rev_cmp_id', $bid)
                    ->orderByRaw("CASE WHEN rev_um_id = ? THEN 0 ELSE 1 END, rev_id DESC", [$uId])
                    ->paginate($perPage, ['*'], 'page', $currentPage);


                if ($reviews) {
                    $reviews->getCollection()->transform(function ($review) {
                        return [
                            'id' => $review->rev_id,
                            'rating' => $review->rev_rating,
                            'comment' => $review->rev_comment,
                            'comment_on' => format_date($review->created_at),
                            'user_id' => $review->rev_um_id,
                            'user_name' => optional($review->user)->ud_first_name ?? '',
                            'user_logo' => ($review->user && !empty($review->user->ud_profile_image))
                                ? env('UPLOADS_URL') . 'user/' . $review->user->ud_profile_image
                                : env('NO_USER_IMAGE'),
                            // 'updated_on' => format_date($review->updated_at),
                        ];
                    });

                    $apiResponse['reviews'] = $reviews;
                    $apiResponse['meta'] = [
                        'current_page' => $reviews->currentPage(),
                        'total' => $reviews->total(),
                        'per_page' => $reviews->perPage(),
                        'last_page' => $reviews->lastPage(),
                    ];
                } else {
                    $apiResponse['reviews'] = [];
                    $apiResponse['meta'] = [
                        'current_page' => $currentPage,
                        'total' => 0,
                        'per_page' => 10,
                        'last_page' => 0,
                    ];
                }

                $apiStatus  = true;
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('revew list failed: ' . $e->getMessage());
                $apiMessage = 'Please try again later';
            }
        }

        // response call
        return $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }


    public function updateCompaniesStatus(Request $request)
    {
        // Initialize response variables
        $apiStatus   = false;
        $apiMessage  = '';
        $apiResponse = [];
        $canProceed  = true;

        // Retrieve header data
        $headerData = $request->header();

        // Validate project key
        if (empty($headerData['key'][0]) || $headerData['key'][0] !== env('PROJECT_KEY')) {
            $apiMessage = 'Unauthenticated Request !!!';
            $canProceed = false;
        }

        // Validate authorization token header
        if ($canProceed && empty($headerData['authorization'][0])) {
            $apiMessage = 'Missing Authorization Token';
            $canProceed = false;
        }

        // Token authentication and get user id
        if ($canProceed) {
            $appAccessToken = $headerData['authorization'][0];
            $getTokenValue  = $this->tokenAuth($appAccessToken);
            if (!$getTokenValue['status']) {
                $apiMessage = $getTokenValue['data'];
                $canProceed = false;
            }
        }

        // If token is valid, get the dynamic user id and validate the request
        if ($canProceed) {
            $uId = $getTokenValue['data'][1];

            $validator = Validator::make($request->all(), [
                'identifier_id' => 'required|integer',
                'status' => 'required|numeric|in:1,0'
            ]);

            // Set custom attribute names for clarity
            $validator->setAttributeNames([
                'identifier_id' => 'Identifier Id',
                'status'        => 'Status',
            ]);

            if ($validator->fails()) {
                $apiMessage  = $validator->errors()->first();
                $apiResponse = ['errors' => $validator->errors()];
                $canProceed  = false;
            }
        }

        // Process the review creation if all validations pass
        if ($canProceed) {
            // $requestData = $request->all();
            $cmp_id = $request->input('identifier_id');
            $status = $request->input('status');
            try {
                DB::beginTransaction();

                DB::table('companies_details AS CD')
                    ->where('CD.cmpd_cmp_id', function ($query) use ($uId, $cmp_id) {
                        $query->select('UCM.ucm_cmp_id')
                            ->from('user_companies_map AS UCM')
                            ->where('UCM.ucm_um_id', $uId)
                            ->where('UCM.ucm_cmp_id', $cmp_id)
                            ->limit(1);
                    })
                    ->update(['CD.cmpd_status' => $status]);


                DB::commit();
                $apiStatus  = true;
                $apiMessage = $status ? "You've successfully activated your business!"
                    : "You've successfully deactivated your business status.";
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('status update failed: ' . $e->getMessage());
                $apiMessage = 'status update failed. Please try again later';
            }
        }

        // response call
        return $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }


    public function socialMediaLogin(Request $request)
    {
        // Initialize response variables
        $apiStatus   = false;
        $apiMessage  = '';
        $apiResponse = [];
        $canProceed  = true;

        // Retrieve header data
        $headerData = $request->header();

        // Validate project key
        if (empty($headerData['key'][0]) || $headerData['key'][0] !== env('PROJECT_KEY')) {
            $apiMessage = 'Unauthenticated Request !!!';
            $canProceed = false;
        }


        // If key is valid, get the dynamic user validate the request
        if ($canProceed) {

            $validator = Validator::make($request->all(), [
                'user_full_name'    => 'nullable|string|regex:/^[^<>]*$/',
                'user_mail'         => 'nullable|email|regex:/^[^<>]*$/',
                'user_img_url'      => 'nullable|string',
                'user_login_type'   => 'required|in:O,G,F,A',
                'device_token'      => 'nullable',
                'fcm_token'         => 'nullable',
                'id_token'          => 'nullable|string|max:255',
            ]);

            $validator->after(function ($validator) use ($request) {
                if (!$request->filled('id_token') && empty($request->input('user_mail'))) {
                    $validator->errors()->add('user_mail', 'The Email field is required.');
                }
            });

            // Set custom attribute names for clarity
            $validator->setAttributeNames([
                'user_full_name'  => 'User Name',
                'user_mail'       => 'User Email',
                'user_img_url'    => 'User Image',
                'user_login_type' => 'Account Type',
                'id_token'        => 'Id Token',
            ]);

            if ($validator->fails()) {
                $apiMessage  = $validator->errors()->first();
                $apiResponse = ['errors' => $validator->errors()];
                $canProceed  = false;
            }
        }

        // Process the review creation if all validations pass
        if ($canProceed) {
            $requestData = $request->all();
            if ($request->filled('id_token')) {
                // id_token is present and not blank
                $idToken = $request->input('id_token');
                $user = UserMaster::select(['um_id', 'um_mobile_no', 'um_email_id', 'um_utm_id', 'um_status'])
                    ->where('um_social_token', $idToken)
                    ->where('um_status', 2)
                    ->first();
            } else {
                $user = UserMaster::select(['um_id', 'um_mobile_no', 'um_email_id', 'um_utm_id', 'um_status'])
                    ->where('um_email_id', $requestData['user_mail'])
                    ->where('um_status', 2)
                    ->first();
            }






            if ($user) {

                try {
                    DB::beginTransaction();

                    // Update user details

                    // $user->update([
                    //     'um_email_id' => $requestData['user_mail'],
                    //     'um_profile_type' => $requestData['user_login_type']
                    // ]);

                    // $user->userDetail->update([
                    //     'ud_first_name'   => $requestData['user_full_name'],
                    //     'ud_profile_image' => $requestData['user_img_url']
                    // ]);

                    // Generate JWT token
                    $jwt = new CreatorJwt();
                    $appAccessToken = $jwt->GenerateToken($user->um_id, $user->um_email_id, $user->um_mobile_no);

                    $deviceType = $headerData['source'][0];

                    // Prepare user device data
                    $fields = [
                        'user_id'          => $user->um_id,
                        'device_type'      => $deviceType,
                        'device_token'     => $requestData['device_token'],
                        'fcm_token'        => $requestData['fcm_token'],
                        'app_access_token' => $appAccessToken,
                    ];

                    // Update or create user device data
                    UserDevice::updateOrCreate(
                        [
                            'user_id' => $user->um_id,
                            'device_type' => $deviceType,
                            'device_token' => $requestData['device_token'],
                            'published' => 1
                        ],
                        $fields
                    );

                    // Get user type
                    $apiResponse = [
                        'isNewUser'    => false,
                        'profile_data' => [
                            'user_id'          => $user->um_id,
                            'name'             => $user->userDetail->ud_first_name,
                            'email'            => $user->um_email_id,
                            'phone'            => $user->um_mobile_no,
                            'user_type_name'   => $user->userType->utm_name,
                            'user_type_id'     => $user->userType->utm_id,
                            'device_type'      => $deviceType,
                            'device_token'     => $requestData['device_token'],
                            'fcm_token'        => $requestData['fcm_token'],
                            'app_access_token' => $appAccessToken,
                        ]
                    ];

                    $apiStatus = true;
                    $apiMessage = 'SignIn Successfully !!!';

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('SignIn Error: ' . $e->getMessage());
                    $apiStatus = false;
                    $apiMessage = 'Something went wrong. Please try again later.';
                }
            } else {
                $apiResponse = [
                    'isNewUser'    => true,
                    'profile_data' => null
                ];
                $apiStatus = true;
                $apiMessage = 'Go to next step';
            }
        }


        // response call
        return $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }

    public function saveUserInfo(Request $request)
    {
        // Initialize response variables
        $apiStatus   = false;
        $apiMessage  = '';
        $apiResponse = [];
        $canProceed  = true;

        // Retrieve header data
        $headerData = $request->header();

        // Validate project key
        if (empty($headerData['key'][0]) || $headerData['key'][0] !== env('PROJECT_KEY')) {
            $apiMessage = 'Unauthenticated Request !!!';
            $canProceed = false;
        }


        // If key is valid, get the dynamic user validate the request
        if ($canProceed) {

            $validator = Validator::make($request->all(), [
                'user_full_name'    => 'required|string|regex:/^[^<>]*$/',
                'user_mail'         => 'required|email|regex:/^[^<>]*$/|unique:user_master,um_email_id',
                'user_img_url'      => 'nullable|string',
                'user_login_type'   => 'required|in:O,G,F,A',
                'device_token'      => 'nullable',
                'fcm_token'         => 'nullable',
                'id_token'          => 'nullable|string|max:255',
                'user_number'            => 'nullable|regex:/^[6-9][0-9]{9}$/|unique:user_master,um_mobile_no',
                'user_whatsapp_number'   => 'nullable|regex:/^[6-9][0-9]{9}$/',
                'user_addr_1'            => 'nullable|string|regex:/^[^<>]*$/',
                'user_addr_2'            => 'nullable|string|regex:/^[^<>]*$/',
                'user_country'           => 'required|numeric',
                'user_state'             => 'nullable|numeric',
                'user_district'          => 'nullable|numeric',
                'user_pincode'           => 'nullable|digits:6',
                'user_is_bengali'        => 'nullable|in:0,1',
            ]);


            // Set custom attribute names for clarity
            $validator->setAttributeNames([
                'user_full_name'         => 'User Name',
                'user_mail'              => 'User Email',
                'user_img_url'           => 'User Image',
                'user_login_type'        => 'Account Type',
                'device_token'           => 'Device Token',
                'fcm_token'              => 'FCM Token',
                'user_number'            => 'Mobile Number',
                'user_whatsapp_number'   => 'WhatsApp Number',
                'user_addr_1'            => 'Address Line 1',
                'user_addr_2'            => 'Address Line 2',
                'user_country'           => 'Country',
                'user_state'             => 'State',
                'user_district'          => 'District',
                'user_pincode'           => 'Pincode',
                'user_is_bengali'        => 'Bengali User Status',
                'id_token'               => 'Id Token',
            ]);

            if ($validator->fails()) {
                $apiMessage  = $validator->errors()->first();
                $apiResponse = ['errors' => $validator->errors()];
                $canProceed  = false;
            }
        }

        // Process the review creation if all validations pass
        if ($canProceed) {
            $requestData = $request->all();

            try {
                DB::beginTransaction();

                if ($request->filled('id_token')) {
                    // Create or update user
                    $user = UserMaster::updateOrCreate(
                        ['um_social_token' => $request->id_token], // find by id_token
                        [
                            'um_utm_id'     => 2,
                            'um_email_id'   => $request->user_mail,
                            'um_mobile_no'  => $request->user_number,
                            'um_social_token' => $request->id_token
                        ]
                    );
                } else {
                    // Create or update user
                    $user = UserMaster::firstOrCreate(
                        [
                            'um_email_id'  => $request->user_mail,
                            // 'um_mobile_no' => $request->user_number
                        ],
                        [
                            'um_email_id'  => $request->user_mail,
                            'um_utm_id'    => 2, // Assuming 2 is the default user type
                            'um_mobile_no' => $request->user_number,
                        ]
                    );
                }


                // Generate user ID and hashed password
                $userId = "EN" . str_pad($user->um_id, 6, '0', STR_PAD_LEFT);
                $userPassword = Hash::make($userId);

                // Create or update user details
                $userDetails = UserDetails::updateOrCreate(
                    ['ud_um_id' => $user->um_id],
                    [
                        'ud_whatsapp_no'   => $request->user_whatsapp_number,
                        'ud_first_name'    => $request->user_full_name,
                        'ud_addr_1'        => $request->user_addr_1,
                        'ud_addr_2'        => $request->user_addr_2,
                        'ud_profile_image' => $request->user_img_url,
                        'ud_country_id'    => $request->user_country,
                        'ud_state_id'      => $request->user_state,
                        'ud_district_id'   => $request->user_district,
                        'ud_pincode'       => $request->user_pincode,
                        'ud_is_bengali'    => $request->user_is_bengali,
                    ]
                );

                // Update user data
                $user->update([
                    'um_user_name' => $userId,
                    'um_password'  => $userPassword,
                    'um_status' => 2,
                    'um_profile_type' => $requestData['user_login_type']
                ]);



                // Generate JWT token
                $jwt = new CreatorJwt();
                $appAccessToken = $jwt->GenerateToken($user->um_id, $user->um_email_id, $user->um_mobile_no);

                $deviceType = $headerData['source'][0];

                // Prepare user device data
                $fields = [
                    'user_id'          => $user->um_id,
                    'device_type'      => $deviceType,
                    'device_token'     => $requestData['device_token'],
                    'fcm_token'        => $requestData['fcm_token'],
                    'app_access_token' => $appAccessToken,
                ];

                // Update or create user device data
                UserDevice::updateOrCreate(
                    [
                        'user_id' => $user->um_id,
                        'device_type' => $deviceType,
                        'device_token' => $requestData['device_token'],
                        'published' => 1
                    ],
                    $fields
                );

                // Get user type
                $apiResponse = [
                    'isNewUser'    => true,
                    'profile_data' => [
                        'user_id'          => $user->um_id,
                        'name'             => $user->userDetail->ud_first_name,
                        'email'            => $user->um_email_id,
                        'phone'            => $user->um_mobile_no,
                        'user_type_name'   => $user->userType->utm_name,
                        'user_type_id'     => $user->userType->utm_id,
                        'device_type'      => $deviceType,
                        'device_token'     => $requestData['device_token'],
                        'fcm_token'        => $requestData['fcm_token'],
                        'app_access_token' => $appAccessToken,
                    ]
                ];

                $apiStatus = true;
                $apiMessage = 'SignIn Successfully !!!';

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('User Creation Error: ' . $e->getMessage());
                $apiStatus  = false;
                $apiMessage = 'Failed to create/update user. Please try again.';
            }
        }


        // response call
        return $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
}
