<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeleteAccountRequest;
use App\Models\GeneralSetting;
use App\Models\User\UserMaster;
use App\Models\User\UserTypeMaster;
use App\Services\LoginOTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DeleteProfileController extends Controller
{

    protected $otp; // Store OTP for verification

    protected $loginOtpService;

    public function __construct(LoginOTPService $lOtpService)
    {

        $this->loginOtpService = $lOtpService;
    }

    public function getOtp(Request $request)
    {

        $requestData        = $request->all();

        $requiredFields     = ['phone'];
        $headerData         = $request->header();
        $apiResponse = [];

        if (!$this->validateArray($requiredFields, $requestData)) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }


        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $phone                      = $requestData['phone'];
            $checkUser                  = UserMaster::where('um_mobile_no', '=', $phone)->where('um_status', '=', 2)->first();


            if ($checkUser) {

                $otp = $this->loginOtpService->generateOTP($checkUser->um_id);

                $mailData                   = [
                    'id'    => $checkUser->um_id,
                    'email' => $checkUser->um_email_id,
                    'phone' => $checkUser->um_mobile_no,
                    'otp'   => $otp,
                ];


                $this->loginOtpService->sendOTP($checkUser->um_id, $otp);

                /* email log save */
                /* send sms */
                /* send sms */
                $apiResponse                        = $mailData;
                $apiStatus                          = true;
                $apiMessage                         = 'OTP Sent To Phone Validation !!!';
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


    public function ValidateMobile(Request $request)
    {
        try {

            $apiStatus          = true;
            $apiMessage         = '';
            $apiResponse        = [];
            $apiExtraField      = '';
            $apiExtraData       = '';
            $requestData        = $request->all();
            $requiredFields     = ['phone', 'otp'];
            $headerData         = $request->header();

            if (!$this->validateArray($requiredFields, $requestData)) {
                $apiStatus          = false;
                $apiMessage         = 'All Data Are Not Present !!!';
            }

            if ($headerData['key'][0] == env('PROJECT_KEY')) {
                $phone                      = $requestData['phone'];
                $otp                        = $requestData['otp'];

                $checkUser                  = UserMaster::where('um_mobile_no', '=', $phone)->where('um_status', '=', 2)->first();
                if ($checkUser) {
                    if ($this->loginOtpService->verifyOTP($checkUser->um_id, $otp)) {
                        $user_id                = $checkUser->um_id;
                        $apiResponse            = [
                            'is_phone_verify'       => Crypt::encryptString($user_id),
                            'user_type_id'          => $checkUser->um_utm_id,

                        ];
                        $apiStatus                          = true;
                        $apiMessage                         = 'validate Successfully !!!';
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


    public function deleteRequest(Request $request)
    {
        $apiStatus          = true;
        $apiMessage         = '';
        $apiResponse        = [];
        $apiExtraField      = '';
        $apiExtraData       = '';
        $requestData        = $request->all();
        $requiredFields     = ['reason', 'verify'];
        $headerData         = $request->header();
        if (!$this->validateArray($requiredFields, array_merge($requestData, $headerData))) {
            $apiStatus          = false;
            $apiMessage         = 'All Data Are Not Present !!!';
        }
        if ($headerData['key'][0] == env('PROJECT_KEY')) {
            $uId        = Crypt::decryptString($requestData['verify']);
            $reason        = $requestData['reason'];
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
                    'comments'                  => $reason
                ];
                DeleteAccountRequest::insert($fields);

                $apiStatus          = true;
                $apiMessage         = 'Account Delete Requests Submitted Successfully !!!';
            } else {
                $apiStatus          = false;
                $apiMessage         = 'User Not Found !!!';
            }
        } else {
            $apiStatus          = false;
            $apiMessage         = 'Unauthenticate Request !!!';
        }
        $this->response_to_json($apiStatus, $apiMessage, $apiResponse);
    }
}
