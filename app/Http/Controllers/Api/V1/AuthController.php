<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Admin\UserMaster\UserMasterController;
use App\Http\Controllers\Controller;
use App\Libraries\CreatorJwt;
use App\Models\EmailLog;
use App\Models\GeneralSetting;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use App\Models\User\UserTypeMaster;
use App\Models\UserDevice;
use App\Models\UserRegistrationOtps;
use App\Notifications\SMSNotification;
use App\Notifications\UserOtpEmailNotify;
use App\Services\LoginOTPService;
use App\Services\RegistrationOTPService;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Libraries\JWT;
use App\Models\Companies\CategoryToCompany;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\Companies\UserToCompanies;
use App\Models\Country;
use App\Models\DeleteAccountRequest;
use App\Models\District;
use App\Models\Page;
use App\Models\State;

class AuthController extends Controller
{
    //
    protected $otp; // Store OTP for verification
    protected $otpHash;
    protected $userService;
    protected $loginOtpService;

    public function __construct(UserService $userService, LoginOTPService $lOtpService)
    {
        $this->userService = $userService;
        $this->loginOtpService = $lOtpService;
    }


    public function smsTest()
    {
        Log::info("Function Executed: " . __METHOD__, ['request_time' => now()]);

        $otp = rand(111111, 999999);
        $message = "Dear user, {$otp} is you verification OTP for registration at KEYLINE";

        $url = 'https://sms.digitalsms.net/api/v3/sendsms';
        // Bearer token
        $apiToken = "198|td0aaBizzgjMwRgKcQfn8VTYguWUXCs2fo6hSsYIabc9f13f";
        // payload
        $payload = [
            'recipient' => '9614311058',
            "entity_id" => "1201159375531154788",
            "sender_id" => "KEYLNS",
            "type" => "transactional",
            'message' => $message,
            "dlt_template_id" => "1307162333099680070"
        ];

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true); // This is a POST request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiToken,
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        //Ignore SSL certificate verification
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // Execute cURL request
        Log::info("Sending cURL request to: {$url}", ['payload' => $payload]);

        $response = curl_exec($ch);

        Log::info("Received cURL response: ", ['response' => $response]);

        if ($response === false) {
            return "cURL Error: " . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);
        // Decode and return response
        $result =  json_decode($response, true);

        dd($result);

        return ($result['status'] === 'success');
    }




    public function userRegistration(Request $request)
    {
        try {
            $apiResponse = [];
            /**
             * Rule::unique('user_registration_otps', 'uro_email')->where(function ($query) use ($request) {
                        return $query->where('uro_user_type', $request->user_type)
                                    ->where('uro_is_deleted', '1');
                    })
                                    Rule::unique('user_registration_otps', 'uro_mobileno')->where(function ($query) use ($request) {
                        return $query->where('uro_user_type', $request->user_type)
                                        ->where('uro_is_deleted', '1');
                    })
             */

            $headerData         = $request->header();
            if ($headerData['key'][0] === env('PROJECT_KEY')) {
                // Validate the incoming request data
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email',
                    'mobile_no' => 'required|digits:10',
                    'whatsapp_no' => 'nullable|digits:10',
                    'user_type'     => 'required|string'
                ], $this->customMessages());

                if ($validator->fails()) {
                    //return response()->json($validator->errors(), 422);

                    // Concatenate error messages into a single string
                    $errors = $validator->errors()->all();
                    $errorMessage = implode(', ', $errors);

                    $apiStatus          = false;
                    http_response_code(200);
                    $apiMessage         = $errorMessage;
                    $apiExtraField      = 'response_code';
                    $apiExtraData       = http_response_code();
                } else {
                    // Check if user already exists (although not strictly necessary due to validation)
                    //$existingUser = UserUserMaster::where('ud_email_id', $request->email)->first();


                    $registeredUser = $this->userService->RegisteredUserExistByEmailOrMobileNo($request->email, $request->mobile_no);



                    if ($registeredUser) {
                        //return response()->json(['error' => 'User with that email/mobile no already exists.'], 409);

                        http_response_code(200);
                        $apiStatus          = false;
                        $apiMessage         = "User with this email/mobile no already exists, please login";
                        $apiExtraField      = 'response_code';
                        $apiExtraData       = http_response_code();
                    } else {
                        try {
                            // Begin a transaction
                            DB::beginTransaction();

                            // Determine user type values
                            if ($request->input('user_type') == 'seller') {
                                $prefix = "EN";
                                $um_utm_id = 2;
                            } else {
                                $prefix = "SE";
                                $um_utm_id = 1;
                            }

                            // Acquire a lock and try to fetch the user
                            $user = UserMaster::where('um_email_id', $request->email)
                                ->where('um_mobile_no', $request->mobile_no)
                                ->lockForUpdate()
                                ->first();

                            // Create the user if not found
                            if (!$user) {
                                $user = UserMaster::create([
                                    'um_email_id'  => $request->email,
                                    'um_utm_id'    => $um_utm_id,
                                    'um_mobile_no' => $request->mobile_no,
                                ]);
                            }

                            // Update user details
                            $userDetails = UserDetails::firstOrCreate(
                                ['ud_um_id' => $user->um_id],  // Check for existing details
                                ['ud_um_id' => $user->um_id]   // Create if not found
                            );

                            $userDetails->ud_whatsapp_no = $request->whatsapp_no;
                            $userDetails->ud_first_name  = $request->name;
                            $userDetails->save();

                            // Generate a unique user ID and password
                            $userId = $prefix . str_pad($user->um_id, 6, '0', STR_PAD_LEFT);
                            $userPassword = Hash::make($userId);

                            $user->um_user_name = $userId;
                            $user->um_password  = $userPassword;
                            $user->save();

                            // Commit the transaction once all DB operations are successful
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            // Log the error or handle it accordingly
                            throw $e; // or return an error response
                        }

                        // Outside the transaction: generate and send OTP and emails
                        $otp = $this->loginOtpService->generateOTP($user->um_id);

                        $mailData = [
                            'id'    => $user->um_id,
                            'email' => $user->um_email_id,
                            'phone' => $user->um_mobile_no,
                            'otp'   => $otp,
                        ];

                        $generalSetting = GeneralSetting::find('1');
                        $subject = $generalSetting->site_name . ' :: Signup Validate OTP';
                        $message = view('email-templates.otp', $mailData);

                        // Send the OTP email
                        $this->sendMail($user->um_email_id, $subject, $message);

                        // Send the OTP SMS or any other notification
                        $this->loginOtpService->sendOTP($user->um_id);

                        $apiResponse    = ['user_id' => $user->um_id];
                        $apiStatus      = true;
                        http_response_code(200);
                        $apiMessage     = 'OTP sent successfully';
                        $apiExtraField  = 'response_code';
                        $apiExtraData   = http_response_code();
                    }
                }
            } else {

                $apiStatus          = false;
                http_response_code(200);
                $apiMessage         = 'Unrestricted access';
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }
        } catch (\Exception $ex) {
            // Handle other exceptions
            // return response()->json(['error' => 'An error occurred: ' . $ex->getMessage()], 500);
            $apiStatus          = false;
            http_response_code(200);
            $apiMessage         = $ex->getMessage(); //'Registration failed. Please check your details and try again.';  // 'Unknown error occured during registration';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        } catch (QueryException $ex) {

            // Handle database query exceptions
            //return response()->json(['error' => 'Database error: ' . $ex->getMessage()], 500);

            $apiStatus          = false;
            http_response_code(200);
            $apiMessage         = 'Database error';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function validateOtp(Request $request)
    {
        try {

            $request->validate([
                'identifier' => 'required|string',
                'otp' => 'required|string',
            ]);

            $apiResponse = [];
            $identifier = $request->input('identifier');
            $otp = $request->input('otp');


            $user = UserMaster::where('um_id', $identifier)->first(); //->orWhere('phone_number', $identifier)


            if (!$user) {
                //return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);

                http_response_code(200);
                $apiStatus          = false;
                $apiMessage         = "User Not found";
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }


            if ($this->loginOtpService->verifyOTP($user->um_id, $otp)) {
                //return response()->json(['message' => 'OTP verified'], Response::HTTP_OK);
                array_push($apiResponse, ['identifier' => $user->um_id]);
                http_response_code(200);
                $apiStatus          = true;
                $apiMessage         = "OTP verified";
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            } else {
                //return response()->json(['error' => 'Invalid OTP'], Response::HTTP_UNAUTHORIZED);

                $apiStatus          = false;
                $apiMessage         = "Invalid OTP";
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            }
        } catch (ValidationException $ex) {

            Log::error("Validation error in verifyOTP: " . $ex->getMessage());
            //return response()->json(['errors' => $ex->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);

            $apiStatus          = false;
            $apiMessage         = "Validation error in verifying OTP";
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        } catch (Exception $ex) {

            Log::error("Error in verifyOTP: " . $ex->getMessage());
            //return response()->json(['error' => 'An unexpected error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);

            $apiStatus          = false;
            $apiMessage         = "Unknown error in verifying OTP";
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }


        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function resendOtp(Request $request)
    {
        try {
            $apiResponse = [];
            $headerData         = $request->header();
            if ($headerData['key'][0] === env('PROJECT_KEY')) {

                $request->validate([
                    'identifier' => 'required|string',
                ]);

                $identifier = (int) $request->input('identifier');
                //$user = UserRegistrationOtps::where('email', $identifier)->orWhere('phone_number', $identifier)->first();

                $user = UserMaster::where('um_id', $identifier)->first();

                $apiResponse = [];


                if (!$user) {
                    //return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);

                    $apiStatus                          = false;
                    http_response_code(200);
                    $apiMessage                         = 'User not found !!!';
                    $apiExtraField                      = 'response_code';
                    $apiExtraData                       = http_response_code();
                } else {

                    if ($this->loginOtpService->isOtpExpired($user->um_id)) {
                        //return response()->json(['error' => 'OTP not expired yet'], Response::HTTP_BAD_REQUEST);


                        $this->loginOtpService->sendOTP($user->um_id, $user->um_otp);

                        $otp = $user->um_otp;

                        $apiStatus                          = true;
                        http_response_code(200);
                        $apiMessage                         = 'Previous OTP not expired, resend successfull !!!';
                        $apiExtraField                      = 'response_code';
                        $apiExtraData                       = http_response_code();
                    } else {


                        $otp = $this->loginOtpService->generateOTP($user->um_id);

                        $this->loginOtpService->sendOTP($user->um_id, $otp);
                    }
                    //return response()->json(['message' => 'OTP resent successfull'], Response::HTTP_OK);

                    $mailData                   = [
                        'id'    => $user->um_id,
                        'email' => $user->um_email_id,
                        'phone' => $user->um_mobile_no,
                        'otp'   => $otp,
                    ];
                    $generalSetting             = GeneralSetting::find('1');
                    $subject                    = $generalSetting->site_name . ' :: Resend Validate OTP';
                    $message                    = view('email-templates.otp', $mailData);
                    $this->sendMail($user->um_email_id, $subject, $message);


                    $apiStatus                          = true;
                    http_response_code(200);
                    $apiMessage                         = 'OTP resent Successfully !!!';
                    $apiExtraField                      = 'response_code';
                    $apiExtraData                       = http_response_code();
                }
            } else {

                $apiStatus                          = false;
                http_response_code(200);
                $apiMessage                         = 'Unrestricted access';
                $apiExtraField                      = 'response_code';
                $apiExtraData                       = http_response_code();
            }
        } catch (ValidationException $ex) {

            Log::error("Validation error in verifyOTP: " . $ex->getMessage());
            //return response()->json(['errors' => $ex->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);

            $apiStatus          = false;
            http_response_code(200);
            $apiMessage         = 'Validation error in verifying OTP';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        } catch (Exception $ex) {

            Log::error("Error in verifyOTP: " . $ex->getMessage());
            //return response()->json(['error' => 'An unexpected error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);

            $apiStatus          = false;
            http_response_code(200);
            $apiMessage         = 'Unexpected error in verifying OTP';
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function test()
    {
        /*return response()->json([
            'success'   => true,
            'data'      => [],
            'message'   => "",
            'meta'      => [],
            'errors'    => ['field_name' => 'here is the error messaage']
        ]);*/

        http_response_code(200);
        $apiStatus          = true;
        $apiMessage         = 'Data Available !!!';
        $apiExtraField      = 'response_code';
        $apiExtraData       = http_response_code();
        $apiResponse        = [
            'success'   => true,
            'data'      => [],
            'message'   => "",
            'meta'      => [],
            'errors'    => ['field_name' => 'here is the error messaage']
        ];


        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function completeRegsitration(Request $request)
    {
        try {

            $apiResponse = [];

            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
                'country'    => 'required|string',
                'state'         => 'required|string',
                'district'      => 'required|string',
                'address1'      => 'required|string',
                //'address2'      => 'required|string',
                'pincode'       => 'required|string|max:6',
                'is_bengali'    => 'required',
                // 'profile_image' => 'required|string|regex:/^data:image\/(\w+);base64,/', // Modify this regex for other formats
                // 'file_size'     => 'required|integer|max:2048000', // Maximum size in kilobytes (2MB)
            ]);
            if ($validator->fails()) {


                $apiStatus          = false;
                http_response_code(200);
                $apiMessage         = $validator->errors();
                $apiExtraField      = 'response_code';
                $apiExtraData       = http_response_code();
            } else {


                $identifier = $request->input('identifier');

                //find resource in temporary user table
                $user = UserMaster::where('um_id', $identifier)->first(); //->orWhere('phone_number', $identifier)

                if (!$user) {

                    $apiStatus          = false;
                    http_response_code(200);
                    $apiMessage         = "User not found";
                    $apiExtraField      = 'response_code';
                    $apiExtraData       = http_response_code();
                } elseif ($user->um_status == 2) {

                    $apiStatus          = false;
                    http_response_code(200);
                    $apiMessage         = "User already registerd with us";
                    $apiExtraField      = 'response_code';
                    $apiExtraData       = http_response_code();
                } else {
                    //Process Profile image
                    $profile_image  = $request->input('profile_image') ?? null;

                    if (!empty($profile_image)) {

                        $directoryPath = public_path('uploads/user');

                        // Check if the directory does not exist
                        if (!File::exists($directoryPath)) {
                            // Attempt to create the directory
                            if (File::makeDirectory($directoryPath, 0755, true)) {
                            } else {
                                throw new Exception("Directory not exists", 1);
                            }
                        } else {
                        }

                        $profile_image      = $profile_image;
                        $upload_type        = $profile_image[0]['type'];
                        if ($upload_type == 'image/jpeg' || $upload_type == 'image/jpg' || $upload_type == 'image/png' || $upload_type == 'image/gif') {
                            $upload_base64      = $profile_image[0]['base64'];
                            $img                = $upload_base64;
                            $proof_type         = $profile_image[0]['type'];
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
                            $file               = 'public/uploads/user/' . $fileName;
                            $success            = file_put_contents($file, $data);
                            $profile_image      = $fileName;
                        } else {
                            $apiStatus          = false;
                            http_response_code(404);
                            $apiMessage         = 'Please Upload Image file !!!';
                            $apiExtraField      = 'response_code';
                            $apiExtraData       = http_response_code();
                        }
                    }

                    $isRegistrationSuccess = DB::transaction(function () use ($request, $user, $profile_image) {

                        $updateData = [
                            'ud_business_addr_1'    => $request->address1,
                            'ud_business_addr_2'    => $request->address2,
                            'ud_profile_image'      => $profile_image,
                            'ud_pincode'            => $request->pincode,
                            'ud_country_id'         => $request->country,
                            'ud_state_id'           => $request->state,
                            'ud_district_id'        => $request->district,
                            'ud_is_bengali'         => $request->is_bengali,
                        ];

                        $result = UserDetails::where('ud_um_id', $user->um_id)
                            ->update($updateData);

                        if ($result) {

                            $user->um_status = 2;
                            $user->save();
                        }

                        return $result;
                    });

                    $apiStatus          = true;
                    http_response_code(200);
                    $apiMessage         = "Registration Successfull";
                    $apiExtraField      = 'response_code';
                    $apiExtraData       = http_response_code();
                }
            }
        } catch (\Exception $ex) {
            //throw $th;

            $apiStatus          = false;
            http_response_code(200);
            $apiMessage         = $ex->getMessage();
            $apiExtraField      = 'response_code';
            $apiExtraData       = http_response_code();
        }


        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    protected function customMessages()
    {
        return [
            // 'username.required' => 'The username field is mandatory.',
            // 'username.unique' => 'This username has already been taken.',
            'email.required' => 'A valid email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'mobile_no.unique' > 'This mobile no is already registerd'
        ];
    }

    public function sendEmail(Request $request)
    {
        $identifier = $request->input('resourceid');

        $user = UserMaster::where('um_id', $identifier)->first();

        $user->notify(new UserOtpEmailNotify($user));
    }

    public function signinWithMobile(Request $request)
    {
        $requestData        = $request->all();
        $requiredFields     = ['phone'];
        $apiResponse = [];

        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }


        if (hash_equals((string) env('PROJECT_KEY'), (string) $request->header('key'))) {
            $phone = preg_replace('/\D+/', '', (string) $requestData['phone']);
            if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
                $phone = substr($phone, 2);
            }
            $checkUser                  = UserMaster::where('um_mobile_no', '=', $phone)->where('um_status', '=', 2)->first();
            if ($checkUser) {
                $otp = $this->loginOtpService->generateOTP($checkUser->um_id);
                $generalSetting = GeneralSetting::find('1');


                //Employees::where('id', '=', $checkUser->id)->update(['otp' => $remember_token]);


                $mailData                   = [
                    'id'    => $checkUser->um_id,
                    'email' => $checkUser->um_email_id,
                    'phone' => $checkUser->um_mobile_no,
                    'otp'   => $otp,
                ];


                //$generalSetting             = GeneralSetting::find('1');
                $subject                    = $generalSetting->site_name . ' :: SignIn Validate OTP';
                $message                    = view('email-templates.otp', $mailData);
                $this->sendMail($checkUser->um_email_id, $subject, $message);

                /* email log save */
                $postData2 = [
                    'name'                  => $checkUser->um_user_name,
                    'email'                 => $checkUser->um_email_id,
                    'subject'               => $subject,
                    'message'               => $message
                ];
                EmailLog::insert($postData2);

                // $this->loginOtpService->sendOTP($checkUser->um_id, $otp);



                /* email log save */
                /* send sms */

                // $transactionId = uniqid();
                // Log::info("Transaction ID: {$transactionId} | Sending SMS to {$phone} with OTP: {$otp}");
                $this->sendSmsNew($phone, $otp);
                // Log::info("Transaction ID: {$transactionId} | SMS sent");


                /* send sms */
                // Never expose the OTP in an API response. The client only needs
                // the member identifier and masked delivery destinations.
                $apiResponse = [
                    'id' => $checkUser->um_id,
                    'email' => $this->maskEmail($checkUser->um_email_id),
                    // Kept unmasked for backward compatibility: the current
                    // mobile client sends this value back during verification.
                    'phone' => $checkUser->um_mobile_no,
                ];
                $apiStatus                          = true;
                $apiMessage                         = 'OTP Sent To Email & Phone Validation !!!';
            } else {
                $apiStatus                              = false;
                $apiMessage                             = 'Your mobile number not registered with us !!!';
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }

    private function maskEmail(?string $email): ?string
    {
        if (!$email || !str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);
        return substr($name, 0, 1) . str_repeat('*', max(strlen($name) - 1, 2)) . '@' . $domain;
    }

    public function signinValidateMobile(Request $request)
    {
        try {

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

            if (hash_equals((string) env('PROJECT_KEY'), (string) $request->header('key'))) {
                $phone = preg_replace('/\D+/', '', (string) $requestData['phone']);
                if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
                    $phone = substr($phone, 2);
                }
                $otp                        = $requestData['otp'];
                $device_type                = $headerData['source'][0];
                $device_token               = $requestData['device_token'];
                $fcm_token                  = $requestData['fcm_token'];
                $checkUser                  = UserMaster::where('um_mobile_no', '=', $phone)->where('um_status', '=', 2)->first();
                if ($checkUser) {
                    if ($this->loginOtpService->verifyOTP($checkUser->um_id, $otp)) {
                        $objOfJwt               = new CreatorJwt();
                        $app_access_token       = $objOfJwt->GenerateToken($checkUser->um_id, $checkUser->um_email_id, $checkUser->um_mobile_no);
                        $user_id                = $checkUser->um_id;
                        //UserMaster::where('um_id', '=', $user_id)->update(['otp' => 0]);
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
                        $userType        = UserTypeMaster::select('utm_name', 'utm_id')->where('utm_id', '=', $checkUser->um_utm_id)->first();
                        $apiResponse            = [
                            'user_id'               => $user_id,
                            'name'                  => $checkUser->um_name,
                            'email'                 => $checkUser->um_email_id,
                            'phone'                 => $checkUser->um_mobile_no,
                            'user_type_name'        => $userType->utm_name,
                            'user_type_id'          => $checkUser->um_utm_id,
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
        } catch (\Exception $ex) {

            $apiStatus          = false;
            $apiMessage         = $ex->getMessage();
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }

    public function forgotPassword(Request $request)
    {
        try {

            $apiStatus          = true;
            $apiMessage         = '';
            $apiResponse        = [];
            $apiExtraField      = '';
            $apiExtraData       = '';
            $requestData        = $request->all();
            $requiredFields     = ['email'];
            $headerData         = $request->header();
            if (!$this->validateArray($requiredFields, $requestData)) {
                $apiStatus          = false;
                $apiMessage         = 'All Data Are Not Present !!!';
            }
            if ($headerData['key'][0] == env('PROJECT_KEY')) {
                //$checkEmail = UserMaster::where('um_email_id', '=', $requestData['email'])->orWhere('um_user_name')->first();
                $checkEmail = $this->userService->getUserByEmailOrUserName($requestData['email']);

                if ($checkEmail) {
                    $otp = $this->loginOtpService->generateOTP($checkEmail->um_id);
                    //Employees::where('id', '=', $checkEmail->id)->update(['otp' => $remember_token]);
                    $mailData                   = [
                        'id'    => $checkEmail->um_id,
                        'email' => $checkEmail->um_email_id,
                        'otp'   => $otp,
                    ];
                    $generalSetting             = GeneralSetting::find('1');
                    $subject                    = $generalSetting->site_name . ' :: Forgot Password OTP';
                    $message                    = view('email-templates.otp', $mailData);
                    $this->sendMail($requestData['email'], $subject, $message);

                    /* email log save */
                    $postData2 = [
                        'name'                  => $checkEmail->ud_first_name,
                        'email'                 => $checkEmail->um_email_id,
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
        } catch (\Exception $ex) {
            //throw $th;

            http_response_code(200);
            $apiStatus          = false;
            $apiMessage = $ex->getMessage();
        }


        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function resetPassword(Request $request)
    {
        try {

            $apiStatus          = true;
            $apiMessage         = '';
            $apiResponse        = [];
            $apiExtraField      = '';
            $apiExtraData       = '';
            $requestData        = $request->all();
            $requiredFields     = ['id', 'password', 'confirm_password'];
            $headerData         = $request->header();
            if (!$this->validateArray($requiredFields, $requestData)) {
                $apiStatus          = false;
                $apiMessage         = 'All Data Are Not Present !!!';
            }
            if ($headerData['key'][0] == env('PROJECT_KEY')) {
                $getUser = UserMaster::with('userDetail')->where('um_id', '=', $requestData['id'])->first();
                if ($getUser) {
                    if ($requestData['password'] == $requestData['confirm_password']) {
                        UserMaster::where('um_id', '=', $requestData['id'])->update(['um_password' => Hash::make($requestData['password'])]);
                        $mailData        = [
                            'id'        => $getUser->um_id,
                            'name'      => $getUser->um_user_name,
                            'email'     => $getUser->um_email_id
                        ];

                        $generalSetting             = GeneralSetting::find('1');
                        $subject                    = $generalSetting->site_name . ' :: Reset Password';
                        $message                    = view('email-templates.change-password', $mailData);
                        $this->sendMail($getUser->um_email_id, $subject, $message);

                        /* email log save */
                        $postData2 = [
                            'name'                  => $getUser->ud_first_name,
                            'email'                 => $getUser->um_email_id,
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
        } catch (\Exception $ex) {
            //throw $th;

            http_response_code(200);
            $apiStatus          = false;
            $apiMessage = $ex->getMessage();
        }

        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function loginValidateOtp(Request $request)
    {
        try {

            $apiStatus          = true;
            $apiMessage         = '';
            $apiResponse        = [];
            $apiExtraField      = '';
            $apiExtraData       = '';
            $requestData        = $request->all();
            $requiredFields     = ['id', 'otp'];
            $headerData         = $request->header();
            if (!$this->validateArray($requiredFields, $requestData)) {
                $apiStatus          = false;
                $apiMessage         = 'All Data Are Not Present !!!';
            }
            if ($headerData['key'][0] == env('PROJECT_KEY')) {
                $getUser = UserMaster::where('um_id', '=', $requestData['id'])->first();
                if ($getUser) {

                    if ($this->loginOtpService->verifyOTP($getUser->um_id, $requestData['otp'])) {
                        //UserMaster::where('id', '=', $requestData['id'])->update(['otp' => 0]);
                        // $this->sendMail('subhomoysamanta1989@gmail.com', $requestData['subject'], $requestData['message']);
                        $apiResponse        = [
                            'id'    => $getUser->um_id,
                            'email' => $getUser->um_email_id
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
        } catch (\Exception $ex) {
            //throw $th;
            http_response_code(200);
            $apiStatus          = false;
            $apiMessage = $ex->getMessage();
        }


        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function loginResendOtp(Request $request)
    {
        try {

            $apiStatus          = true;
            $apiMessage         = '';
            $apiResponse        = [];
            $apiExtraField      = '';
            $apiExtraData       = '';
            $requestData        = $request->all();
            $requiredFields     = ['id'];
            $headerData         = $request->header();
            if (!$this->validateArray($requiredFields, $requestData)) {
                $apiStatus          = false;
                $apiMessage         = 'All Data Are Not Present !!!';
            }
            if ($headerData['key'][0] == env('PROJECT_KEY')) {
                $id         = $requestData['id'];
                $getUser    = UserMaster::where('um_id', '=', $id)->first();
                if ($getUser) {
                    $remember_token = $this->loginOtpService->generateOTP($getUser->um_id);
                    $postData = [
                        'otp'        => $remember_token
                    ];
                    //Employees::where('id', '=', $id)->update($postData);

                    $mailData                   = [
                        'id'    => $getUser->um_id,
                        'email' => $getUser->um_email_id,
                        'otp'   => $remember_token,
                    ];
                    $generalSetting             = GeneralSetting::find('1');
                    $subject                    = $generalSetting->site_name . ' :: Resend OTP';
                    $message                    = view('email-templates.otp', $mailData);
                    $this->sendMail($getUser->um_email_id, $subject, $message);

                    /* email log save */
                    $this->userService->getUserByEmailOrUserName($getUser->um_user_name);
                    $postData2 = [
                        'name'                  => $getUser->ud_first_name,
                        'email'                 => $getUser->um_email_id,
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
        } catch (\Exception $ex) {
            //throw $th;

            http_response_code(200);
            $apiStatus          = false;

            $apiMessage = $ex->getMessage();
        }


        $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
    }

    public function signIn(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['email_or_userid', 'password', 'device_token', 'fcm_token'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $email                      = $requestData['email_or_userid'];
            $password                   = $requestData['password'];
            $device_type                = $headerData['source'][0];
            $device_token               = $requestData['device_token'];
            $fcm_token                  = $requestData['fcm_token'];
            $checkUser                  = UserMaster::where('um_email_id', '=', $email)->orWhere('um_user_name', $email)->where('um_status', '=', 2)->first();
            if ($checkUser) {
                if (Hash::check($password, $checkUser->um_password)) {
                    $objOfJwt           = new CreatorJwt();
                    $app_access_token   = $objOfJwt->GenerateToken($checkUser->um_id, $checkUser->um_email_id, $checkUser->um_mobile_no);
                    $user_id                        = $checkUser->um_id;
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
                    //$getEmployeeType        = ::select('name')->where('id', '=', $checkUser->employee_type_id)->first();

                    $userType        = UserTypeMaster::select('utm_name', 'utm_id')->where('utm_id', '=', $checkUser->um_utm_id)->first();

                    $apiResponse            = [
                        'user_id'               => $user_id,
                        'name'                  => $checkUser->um_user_name,
                        'email'                 => $checkUser->um_email_id,
                        'phone'                 => $checkUser->um_mobile_no,
                        'user_type_name'        => $userType->utm_name,
                        'user_type_id'          => $userType->utm_id,
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


    // ============================  AFTER USER LOGIN ============================

    /*
    Match JWT token with user token saved in database
    */
    private static function matchToken($token)
    {
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

    /*
    Check Authentication
    */
    private function tokenAuth($appAccessToken)
    {
        $headers = apache_request_headers();
        if (isset($appAccessToken) && !empty($appAccessToken)) :
            $userdata = $this->matchToken($appAccessToken);

            if ($userdata['status']) :
                $checkToken =  UserDevice::where('user_id', '=', $userdata['data']->id)->where('app_access_token', '=', $appAccessToken)->first();
                // echo $this->db->last_query();
                // pr($userdata);
                if (!empty($checkToken)) :
                    /*
                    if ($userdata['data']->exp && $userdata['data']->exp > time()) :
                        $tokenStatus = array(true, $userdata['data']->id, $userdata['data']->email, $userdata['data']->phone, $userdata['data']->exp);
                    else :
                        $tokenStatus = array(false, 'Token Has Expired 1 !!!');
                    endif;
                    */
                    $tokenStatus = array(true, $userdata['data']->id, $userdata['data']->email, $userdata['data']->phone, $userdata['data']->exp);
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
            // $this->userId           = $tokenStatus[1];
            // $this->userEmail        = $tokenStatus[2];
            // $this->userMobile       = $tokenStatus[3];
            // $this->userExpiry       = $tokenStatus[4];
            // pr($tokenStatus);
            return array('status' => true, 'data' => $tokenStatus);
        else :
            return array('status' => false, 'data' => $tokenStatus[1]);
        // $this->response_to_json(FALSE, $tokenStatus[1]);
        endif;
    }



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

        // return response()->json($headerData);

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

                $getUser    = UserMaster::select('um_id', 'um_utm_id', 'um_user_name', 'um_mobile_no', 'um_email_id', 'um_created_at')
                    ->with(['userType:utm_id,utm_name', 'userDetail'])
                    ->where('um_id', '=', $uId)->first();


                $userType = $getUser->userType;
                $userDtl  = $getUser->userDetail;

                if ($getUser) {
                    $countryDetails = !empty($userDtl->ud_country_id) ? Country::find($userDtl->ud_country_id) : null;
                    $districtDetails = !empty($userDtl->ud_district_id) ? District::find($userDtl->ud_district_id) : null;
                    $stateDetails = !empty($userDtl->ud_state_id) ? State::find($userDtl->ud_state_id) : null;

                    $my_business_count = DB::table('user_companies_map as UCM')
                        ->join('companies_details as CD', 'CD.cmpd_cmp_id', '=', 'UCM.ucm_cmp_id')
                        ->where('UCM.ucm_um_id', $uId)
                        ->where('CD.cmpd_is_document_valid', 0)
                        ->count('UCM.ucm_cmp_id');


                    $countryInfo = Country::find($userDtl->ud_country_id);
                    $stateInfo = State::find($userDtl->ud_state_id);
                    $districtInfo = District::find($userDtl->ud_district_id);

                    $country = $countryInfo->name ?? "";
                    $state = $stateInfo->name ?? "";
                    $district = $districtInfo->name ?? "";

                    $profileData    = [
                        'user_id'               => $getUser->um_id,
                        'user_type_id'          => $getUser->um_utm_id,
                        'user_type_name'        => $userType->utm_name,
                        'user_name'             => $getUser->um_user_name,
                        'name'                  => $userDtl->ud_first_name . ' ' . $userDtl->ud_last_name ?? '',
                        'mobile_no'             => $getUser->um_mobile_no,
                        'email_id'              => $getUser->um_email_id,
                        'doj'                   => (($getUser->um_created_at != '') ? date_format(date_create($getUser->um_created_at), "M d, Y") : ''),
                        'aadhar_no'             => $userDtl->ud_aadhar_no ?? '',
                        'td_lic_file'           => $userDtl->ud_td_lic_file ?? '',
                        'gst_cert'              => $userDtl->ud_gst_cert ?? '',
                        'whatsapp_no'           => $userDtl->ud_whatsapp_no ?? '',
                        'user_addr_1'           => $userDtl->ud_addr_1 ?? '',
                        'user_addr_2'           => $userDtl->ud_addr_2 ?? '',
                        'is_bengali'            => $userDtl->ud_is_bengali ?? '',
                        'business_name'         => $userDtl->ud_business_name ?? '',
                        'business_addr_1'       => $userDtl->ud_business_addr_1 ?? '',
                        'business_addr_2'       => $userDtl->ud_business_addr_2 ?? '',
                        'business_category'     => $userDtl->ud_business_category ?? '',
                        'keywords'              => $userDtl->ud_keywords ?? '',
                        'social_links'          => $userDtl->ud_social_links ?? [],
                        'country_id'            => $userDtl->ud_country_id,
                        'country_name'          => $country,
                        'state_id'              => $userDtl->ud_state_id,
                        'state_name'            => $state,
                        'district_id'           => $userDtl->ud_district_id,
                        'district_name'         => $district,
                        'pin_code'              => $userDtl->ud_pincode ?? '',
                        'my_business_count'     => $my_business_count,
                        'profile_image'         => (($userDtl->ud_profile_image != '') ? env('UPLOADS_URL') . 'user/' . $userDtl->ud_profile_image : env('NO_USER_IMAGE')),
                        // 'created_at'            => date_format(date_create($getUser->created_at), "M d, Y h:i A"),

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



    public function deleteAccount(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['key', 'source'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, array_merge($requestData, $headerData))) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $app_access_token           = $headerData['authorization'][0];
            $checkUserTokenExist        = UserDevice::where('app_access_token', '=', $app_access_token)->where('published', '=', 1)->first();
            if ($checkUserTokenExist) {
                $getTokenValue              = $this->tokenAuth($app_access_token);
                if ($getTokenValue['status']) {
                    $uId        = $getTokenValue['data'][1];
                    $expiry     = date('d/m/Y H:i:s', $getTokenValue['data'][4]);
                    $getUser    = UserMaster::where('um_id', '=', $uId)->where('um_status', 2)->first();
                    if ($getUser) {
                        $details = $getUser->userDetail;
                        $getEmployeeType     = UserTypeMaster::select('utm_name')->where('utm_id', '=', $getUser->um_utm_id)->first();
                        $fields = [
                            'user_type'                 => (($getEmployeeType) ? $getEmployeeType->utm_name : ''),
                            'entity_name'               => $details->ud_first_name,
                            'email'                     => $getUser->um_email_id,
                            'is_email_verify'           => 1,
                            'phone'                     => $getUser->um_mobile_no,
                            'is_phone_verify'           => 1,
                        ];
                        DeleteAccountRequest::insert($fields);

                        $apiStatus          = true;
                        $apiMessage         = 'Account Delete Requests Submitted Successfully !!!';
                    } else {
                        $apiStatus          = false;
                        $apiMessage         = 'User Not Found !!!';
                    }
                } else {
                    $apiStatus                      = false;
                    $apiMessage                     = $getTokenValue['data'];
                }
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




    public function policyAndTerms(Request $request)
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
            'slug' => 'required|string',
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
                    $slug       = $requestData['slug'];
                    // $expiry     = date('d/m/Y H:i:s', $getTokenValue['data'][4]);
                    $page    = Page::select('page_name', 'page_content')
                        ->where('page_slug', $slug)
                        ->where('status', 1)->first();

                    if ($page) {

                        $pageData    = [
                            'name'             => $page->page_name,
                            'content'          => $page->page_content,
                        ];
                        $apiStatus          = true;
                        $apiMessage         = 'Data Available !!!';
                        $apiResponse        = $pageData;
                    } else {
                        $apiStatus          = false;
                        $apiMessage         = 'Data Not Available !!!';
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

    // ======================== business ========================

    public function businessList(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = [];
        $headerData         = $request->header();





        if ($headerData['key'][0] == env('PROJECT_KEY')) {

            $app_access_token           = $headerData['authorization'][0];

            $getTokenValue              = $this->tokenAuth($app_access_token);


            if ($getTokenValue['status']) {
                $list = [];
                $uId        = $getTokenValue['data'][1];
                $userWithBusiness = UserMaster::with(['companies.details', 'companies.categories'])->where('um_id', $uId)->get();

                //$details = $businessList->details;
                //$companies = CompaniesMaster::with('categories')->where('cmp_id', $details->cmpd_cmp_id)->get();

                //dd($businessList);
                // return response()->json($businessList);

                if (!empty($userWithBusiness)) {
                    foreach ($userWithBusiness as $user) {
                        foreach ($user->companies as $company) {
                            $categories = $company->categories;
                            $category = $categories->first();
                            if ($company->details) {

                                $ratingData = getBusinessRating($company->cmp_id);

                                $list[] = [
                                    'id' => $company->cmp_id,
                                    'regn_no' => $company->details->cmpd_company_regn_no ?? '',
                                    'name' => $company->details->cmpd_name ?? '',
                                    'description' => $company->details->cmpd_description ?? '',
                                    'email' => $company->details->cmpd_email ?? '',
                                    'alternate_email' => $company->details->cmpd_alternate_email ?? '',
                                    'phone' => $company->details->cmpd_phone ?? '',
                                    'whatsapp_no' => $company->details->cmpd_whatsapp_no ?? '',
                                    'address1' => $company->details->cmpd_address1 ?? '',
                                    'address2' => $company->details->cmpd_address2 ?? '',
                                    'address3' => $company->details->cmpd_address3 ?? '',
                                    'estd_year' => $company->details->cmpd_estd_year ?? '',
                                    'district' => District::where('id', $company->details->cmpd_district)->value('name')  ?? '',
                                    'pincode' => $company->details->cmpd_pincode ?? '',
                                    'license_start_datetime' => $company->details->cmpd_license_start_datetime ?? '',
                                    'license_end_datetime' => $company->details->cmpd_license_end_datetime ?? '',
                                    'license_ref' => $company->details->cmpd_license_ref ?? '',
                                    'renewal_date' => $company->details->cmpd_last_renewal_date ?? '',
                                    'status' => $company->details->cmpd_status ?? '',
                                    'logo' => (($company->details->cmpd_logo != '') ? env('UPLOADS_URL') . 'company/' . $company->details->cmpd_logo : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE')),
                                    'cmpd_doc_trade_license' => ($company->details->cmpd_doc_trade_license != '') ? env('UPLOADS_URL') . 'trade_licenses/' . $company->details->cmpd_doc_trade_license : null,
                                    'cmpd_doc_pan_img'      => ($company->details->cmpd_doc_pan_img != '') ? env('UPLOADS_URL') . 'pan_images/' . $company->details->cmpd_doc_pan_img : null,
                                    'cmpd_doc_gst_certificate'  => ($company->details->cmpd_doc_gst_certificate != '') ? env('UPLOADS_URL') . 'gst_certificates/' . $company->details->cmpd_doc_gst_certificate : null,
                                    'cmpd_country'              => Country::where('id', $company->details->cmpd_country)->value('name') ?? '',
                                    'cmpd_state'              => State::where('id', $company->details->cmpd_state)->value('name') ?? '',
                                    'cmpd_is_document_valid'    => $company->details->cmpd_is_document_valid ?? null,
                                    'state_id'                  => $company->details->cmpd_state,
                                    'district_id'               => $company->details->cmpd_district,
                                    'country_id'                => $company->details->cmpd_country,
                                    'category_id'               => $category->bcm_id ?? 23,
                                    'category_name'             => $category->name ?? 'Unclassified',
                                    'cmpd_pan_no'               => $company->details->cmpd_pan_no ?? '',
                                    'cmpd_gst_no'               => $company->details->cmpd_gst_no ?? '',
                                    "avg_rating"                => (float) $ratingData->avg_rating,
                                    "total_reviews"             => (int) $ratingData->total_reviews,

                                ];
                            }
                        }
                    }


                    $apiStatus          = true;
                    $apiMessage         = 'Data Available !!!';
                    $apiResponse        = $list;
                } else {
                    $apiStatus          = false;
                    $apiMessage         = 'business Not Found !!!';
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






    // ======================== business ========================




}
