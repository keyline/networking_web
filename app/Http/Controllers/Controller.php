<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\GeneralSetting;
use App\Models\HomePage;
use App\Models\Admin;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\Category;
use App\Models\EmployeeType;
use App\Models\ClientType;
use App\Models\Manufacturer;
use App\Models\User\UserTypeMaster;
use Session;
use Helper;
use Google\Client;
use Google\Service\FirebaseCloudMessaging;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\DigitalSmsOtpService;

date_default_timezone_set("Asia/Kolkata");
class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    protected function sendSmsNew($recipient, $otp)
    {
        try {
            (new DigitalSmsOtpService())->send((string) $recipient, (string) $otp);
            return true;
        } catch (\Throwable $exception) {
            Log::error('DigitalSMS OTP delivery failed', [
                'recipient' => substr((string) $recipient, -4),
                'message' => $exception->getMessage(),
            ]);
            return false;
        }
    }


    protected function sendMail($email, $subject, $message, $file = '')
    {
        $generalSetting             = GeneralSetting::find('1');
        $mailLibrary                = new PHPMailer(true);
        $mailLibrary->CharSet       = 'UTF-8';
        $mailLibrary->SMTPDebug     = 0;
        //$mailLibrary->IsSMTP();
        $mailLibrary->Host          = $generalSetting->smtp_host;
        $mailLibrary->SMTPAuth      = true;
        $mailLibrary->Port          = $generalSetting->smtp_port;
        $mailLibrary->Username      = $generalSetting->smtp_username;
        $mailLibrary->Password      = $generalSetting->smtp_password;
        $mailLibrary->SMTPSecure    = 'ssl';
        $mailLibrary->From          = $generalSetting->from_email;
        $mailLibrary->FromName      = $generalSetting->from_name;
        $mailLibrary->AddReplyTo($generalSetting->from_email, $generalSetting->from_name);
        if (is_array($email)) :
            foreach ($email as $eml):
                $mailLibrary->addAddress($eml);
            endforeach;
        else:
            $mailLibrary->addAddress($email);
        endif;
        //Set CC address
        // $mailLibrary->addBCC("shuvadeep@keylines.net", "Shuvadeep Chakraborty");

        $mailLibrary->WordWrap      = 5000;
        $mailLibrary->Subject       = $subject;
        $mailLibrary->Body          = $message;
        $mailLibrary->isHTML(true);
        if (!empty($file)):
            $mailLibrary->AddAttachment($file);
        endif;
        return (!$mailLibrary->send()) ? false : true;
    }
    public function getAccessToken($credentialsJson)
    {
        $client = new Client();
        $client->setAuthConfig($credentialsJson);
        $client->addScope('https://www.googleapis.com/auth/cloud-platform');
        $client->setAccessType('offline');

        $client->fetchAccessTokenWithAssertion();

        return $client->getAccessToken();
    }
    public function sendFCMMessage($accessToken, $projectId, $message)
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $headers = [
            'Authorization: Bearer ' . $accessToken['access_token'],
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }
    public function sendCommonPushNotification($token, $title, $body, $type = '', $image = '')
    {
        try {
            // $credentialsPath = public_path('uploads/ccfc-83373-firebase-adminsdk-qauj0-66a7cd8a2f.json'); // Replace with the path to your service account JSON file
            // echo $credentialsPath;die;

            // Get the Firebase credentials from environment variable
            $jsonCredentials = getenv('FIREBASE_CREDENTIALS');
            if (!$jsonCredentials) {
                throw new Exception("Firebase credentials not found in environment variables.");
            }

            $credentialsArray = json_decode($jsonCredentials, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON format in environment variable.');
            }

            $projectId = 'energic-abf74'; // Replace with your Firebase project ID

            // Get access token
            $accessToken = $this->getAccessToken($credentialsArray);

            // Define your message payload
            if ($image != '') {
                $message = [
                    'message' => [
                        'token' => $token, // Replace with the recipient device token
                        'data' => [
                            'type' => $type,
                            // 'picture' => $image
                        ],
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                            'image' => $image
                        ]
                    ]
                ];
                $iosPayload = [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'sound' => 'default',
                        'mutable-content' => 1,
                    ],
                    'media-url' => $image
                ];
            } else {
                $message = [
                    'message' => [
                        'token' => $token, // Replace with the recipient device token
                        'data' => [
                            'type' => $type
                        ],
                        'notification' => [
                            'title' => $title,
                            'body'  => $body
                        ]
                    ]
                ];
                $iosPayload = [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'sound' => 'default',
                        'mutable-content' => 1,
                    ]
                ];
            }

            // Send FCM message
            $response = $this->sendFCMMessage($accessToken, $projectId, $message);
            $response = $this->sendFCMMessage($accessToken, $projectId, $iosPayload);
            return true;
            // return "Response: " . $response;
            // return redirect()->to('admin/create/settinglist')->with('status', "Response: " . $response);
        } catch (Exception $e) {
            return false;
            // return "Error: " . $e->getMessage();
            // return redirect()->to('admin/create/settinglist')->with('error_message', "Error: " . $e->getMessage());
        }
    }
    // send sms
    public function sendSMS($mobileNo, $messageBody)
    {
        $generalSetting             = GeneralSetting::find('1');
        $authKey                    = $generalSetting->sms_authentication_key;
        $senderId                   = $generalSetting->sms_sender_id;
        $route                      = "4";
        $postData = array(
            'apikey'        => $authKey,
            'number'        => $mobileNo,
            'message'       => $messageBody,
            'senderid'      => $senderId,
            'format'        => 'json'
        );
        //API URL
        $url            = $generalSetting->sms_base_url;
        // init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => false,
            CURLOPT_POSTFIELDS => $postData
            //,CURLOPT_FOLLOWLOCATION => true
        ));
        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //get response
        $output = curl_exec($ch);
        //Print error if any
        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
            return false;
        } else {
            return true;
        }
        curl_close($ch);
    }
    // send sms
    // single file upload
    public function upload_single_file($fieldName, $fileName, $uploadedpath, $uploadType, $tempFile = '')
    {
        $imge = $fileName;
        if ($imge == '') {
            $slider_image = 'no-user-image.jpg';
        } else {
            $imageFileType1 = pathinfo($imge, PATHINFO_EXTENSION);
            if ($uploadType == 'image') {
                if ($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "WEBP" && $imageFileType1 != "webp" && $imageFileType1 != "ico" && $imageFileType1 != "ICO" && $imageFileType1 != "SVG" && $imageFileType1 != "svg") {
                    $message = 'Sorry, only JPG, JPEG, ICO, SVG, PNG, WEBP files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif ($uploadType == 'pdf') {
                if ($imageFileType1 != "pdf" && $imageFileType1 != "PDF") {
                    $message = 'Sorry, only PDF files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif ($uploadType == 'word') {
                if ($imageFileType1 != "doc" && $imageFileType1 != "DOC" && $imageFileType1 != "docx" && $imageFileType1 != "DOCX") {
                    $message = 'Sorry, only DOC files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif ($uploadType == 'excel') {
                if ($imageFileType1 != "xls" && $imageFileType1 != "XLS" && $imageFileType1 != "xlsx" && $imageFileType1 != "XLSX") {
                    $message = 'Sorry, only EXCEl files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif ($uploadType == 'powerpoint') {
                if ($imageFileType1 != "ppt" && $imageFileType1 != "PPT" && $imageFileType1 != "pptx" && $imageFileType1 != "PPTX") {
                    $message = 'Sorry, only PPT files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif ($uploadType == 'video') {
                if ($imageFileType1 != "mp4" && $imageFileType1 != "3gp" && $imageFileType1 != "webm" && $imageFileType1 != "MP4" && $imageFileType1 != "3GP" && $imageFileType1 != "WEBM") {
                    $message = 'Sorry, only Video files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif ($uploadType == 'custom') {
                if ($imageFileType1 != "pdf" && $imageFileType1 != "PDF" && $imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG") {
                    $message = 'Sorry, only .PDF, JPG, JPEG, PNG files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif ($uploadType == 'customapplication') {
                if ($imageFileType1 != "doc" && $imageFileType1 != "DOC" && $imageFileType1 != "docx" && $imageFileType1 != "DOCX" && $imageFileType1 != "pdf" && $imageFileType1 != "PDF" && $imageFileType1 != "txt" && $imageFileType1 != "TXT") {
                    $message = 'Sorry, only .DOC,.DOCX,.PDF,.TXT files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            }

            $newFilename = time() . $imge;
            if ($tempFile == '') {
                $temp = $_FILES[$fieldName]["tmp_name"];
            } else {
                $temp = $tempFile;
            }
            if ($uploadedpath == '') {
                $upload_path = 'public/uploads/';
            } else {
                $upload_path = 'public/uploads/' . $uploadedpath . '/';
            }
            if ($status) {
                move_uploaded_file($temp, $upload_path . $newFilename);
                $return_array = array('status' => 1, 'message' => $message, 'newFilename' => $newFilename);
            } else {
                $return_array = array('status' => 0, 'message' => $message, 'newFilename' => '');
            }
            return $return_array;
        }
    }
    // multiple files upload
    public function commonFileArrayUpload($path = '', $images = array(), $uploadType = '')
    {
        $apiStatus = false;
        $apiMessage = [];
        $apiResponse = [];
        if (count($images) > 0) {
            for ($p = 0; $p < count($images); $p++) {
                $imge = $images[$p]->getClientOriginalName();
                if ($imge == '') {
                    $slider_image = 'no-user-image.jpg';
                } else {
                    $imageFileType1 = pathinfo($imge, PATHINFO_EXTENSION);
                    if ($uploadType == 'image') {
                        if ($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "gif" && $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "GIF" && $imageFileType1 != "ico" && $imageFileType1 != "ICO") {
                            $message = 'Sorry, only JPG, JPEG, ICO, PNG & GIF files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif ($uploadType == 'pdf') {
                        if ($imageFileType1 != "pdf" && $imageFileType1 != "PDF") {
                            $message = 'Sorry, only PDF files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif ($uploadType == 'word') {
                        if ($imageFileType1 != "doc" && $imageFileType1 != "DOC" && $imageFileType1 != "docx" && $imageFileType1 != "DOCX") {
                            $message = 'Sorry, only DOC files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif ($uploadType == 'excel') {
                        if ($imageFileType1 != "xls" && $imageFileType1 != "XLS" && $imageFileType1 != "xlsx" && $imageFileType1 != "XLSX") {
                            $message = 'Sorry, only EXCEl files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif ($uploadType == 'powerpoint') {
                        if ($imageFileType1 != "ppt" && $imageFileType1 != "PPT" && $imageFileType1 != "pptx" && $imageFileType1 != "PPTX") {
                            $message = 'Sorry, only PPT files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif ($uploadType == 'all') {
                        if ($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "gif" && $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "GIF" && $imageFileType1 != "ico" && $imageFileType1 != "ICO" && $imageFileType1 != "ppt" && $imageFileType1 != "PPT" && $imageFileType1 != "pptx" && $imageFileType1 != "PPTX" && $imageFileType1 != "pdf" && $imageFileType1 != "PDF" && $imageFileType1 != "doc" && $imageFileType1 != "DOC" && $imageFileType1 != "docx" && $imageFileType1 != "DOCX" && $imageFileType1 != "xls" && $imageFileType1 != "XLS" && $imageFileType1 != "xlsx" && $imageFileType1 != "XLSX") {
                            $message = 'Sorry, only image,pdf,docs,xls,ppt files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    }

                    $newFilename = uniqid() . "." . $imageFileType1;
                    // $temp = $images[$p]->getTempName();
                    $temp = $images[$p]->getPathName();
                    if ($path == '') {
                        $upload_path = 'public/uploads/';
                    } else {
                        $upload_path = 'public/uploads/' . $path . '/';
                    }
                    if ($status) {
                        move_uploaded_file($temp, $upload_path . $newFilename);
                        //$apiStatus      = TRUE;
                        //$apiMessage     = $message;
                        $apiResponse[]  = $newFilename;
                    } else {
                        //$apiStatus      = FALSE;
                        //$apiMessage     = $message;
                    }
                }
            }
        }
        //$return_array = array('status'=> $apiStatus, 'message'=> $apiMessage, 'newFilename'=> $apiResponse);
        return $apiResponse;
    }
    // front before login layout
    public function front_before_login_layout($title, $page_name, $data)
    {
        $data['generalSetting']             = GeneralSetting::find('1');
        $data['title']                      = $title . ' :: ' . $data['generalSetting']->site_name;
        $data['page_header']                = $title;

        $data['before_head']                = view('front.elements.beforehead', $data);
        $data['before_header']              = view('front.elements.beforeheader', $data);
        $data['before_footer']              = view('front.elements.beforefooter', $data);
        $data['maincontent']                = view('front.pages.' . $page_name, $data);
        return view('front.layout-before-login', $data);
    }
    // front after login layout
    public function front_after_login_layout($title, $page_name, $data)
    {
        $data['generalSetting']     = GeneralSetting::find('1');
        $data['content']            = HomePage::where('status', '=', 1)->orderBy('id', 'ASC')->first();
        $data['title']              = $title . ' :: ' . $data['generalSetting']->site_name;
        $data['page_header']        = $title;
        $user_id                    = session('user_id');
        $data['user']               = User::find($user_id);

        $data['after_head']         = view('front.elements.afterhead', $data);
        $data['after_header']       = view('front.elements.afterheader', $data);
        $data['after_sidebar']      = view('front.elements.sidebar', $data);
        $data['after_footer']       = view('front.elements.afterfooter', $data);
        $data['maincontent']        = view('front.pages.user.' . $page_name, $data);
        return view('front.layout-after-login', $data);
    }
    // admin authentication layout
    public function admin_before_login_layout($title, $page_name, $data)
    {
        $data['generalSetting']     = GeneralSetting::find('1');
        $data['title']              = $title . ' :: ' . $data['generalSetting']->site_name;
        $data['page_header']        = $title;

        $data['head']               = view('admin.elements.head', $data);
        $data['maincontent']        = view('admin.maincontents.' . $page_name, $data);
        return view('admin.layout-before-login', $data);
    }
    // admin after login layout
    public function admin_after_login_layout($title, $page_name, $data)
    {
        // Helper::pr(session()->all());
        $data['generalSetting']     = GeneralSetting::find('1');
        $data['title']              = $title . ' :: ' . $data['generalSetting']->site_name;
        $data['page_header']        = $title;
        $user_id                    = session('user_id');
        $data['admin']              = Admin::find($user_id);
        $data['employee_type']      = EmployeeType::where('status', '=', 1)->orderBy('level', 'ASC')->get();
        // $data['client_types']       = ClientType::select('id', 'name', 'slug')->where('status', '=', 1)->orderBy('id', 'ASC')->get();
        $data['client_types']       = UserTypeMaster::select('utm_id', 'utm_name')->where('utm_status', '=', 1)->orderBy('utm_id', 'ASC')->get();

        $userAccess                 = UserAccess::where('user_id', '=', $user_id)->where('status', '=', 1)->first();
        if ($userAccess) {
            $data['module_id']      = json_decode($userAccess->module_id);
        } else {
            $data['module_id']      = [];
        }

        $data['head']               = view('admin.elements.head', $data);
        $data['header']             = view('admin.elements.header', $data);
        $data['footer']             = view('admin.elements.footer', $data);
        $data['sidebar']            = view('admin.elements.sidebar', $data);
        $data['maincontent']        = view('admin.maincontents.' . $page_name, $data);
        return view('admin.layout-after-login', $data);
    }
    // currency converter
    public function convertCurrency($amount, $from, $to)
    {
        // Fetching JSON
        $req_url = 'https://api.exchangerate-api.com/v4/latest/' . $from;
        $response_json = file_get_contents($req_url);
        // Continuing if we got a result
        if (false !== $response_json) {
            // Try/catch for json_decode operation
            try {
                // Decoding
                $response_object = json_decode($response_json);
                // YOUR APPLICATION CODE HERE, e.g.
                $base_price = $amount; // Your price in USD
                $EUR_price = round(($base_price * $response_object->rates->$to), 2);
                return $EUR_price;
            } catch (Exception $e) {
                // Handle JSON parse error...
            }
        }
    }
    public function jobseekerNamingFormat($fname, $lname, $dob)
    {
        $formattedName = strtoupper(substr($fname, 0, 1)) . '. ' . $lname . ' ' . date_format(date_create($dob), "m-y");
        return $formattedName;
    }
    public function perform_http_request($method, $url, $data = array())
    {

        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //If SSL Certificate Not Available, for example, I am calling from http://localhost URL
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    public function curl_request_post($method, $functionName, $data = array())
    {
        $url = "https://segen-enterprise.synergydevelopmentgroups.com/api/";

        $dataJson           = [
            'key' => 'facb6e0a6fcbe200dca2fb60dec75be7',
            'source' => 'WEB'
        ];
        $data = array_merge($dataJson, $data);
        // Helper::pr($data);

        $curl = curl_init($url . $functionName);
        curl_setopt($curl, CURLOPT_URL, $url . $functionName);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-type: application/json",
            "Accept: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        //pr($resp);
        return $resp;
    }
    public function getApiResponse($method, $functionName, $data = array())
    {
        $response               = json_decode($this->curl_request_post($method, $functionName));
        return $response->data;
    }
    /* For API Development */
    public function isJSON($string)
    {
        // echo $string;die;
        $valid = is_string($string) && is_array(json_decode($string, true)) ? true : false;
        if (!$valid) {
            $this->response_to_json(false, "Not JSON", 401);
        }
    }
    /* Process json from request */
    public function extract_json($key)
    {
        return json_decode($key, true);
    }
    /* Methods to check all necessary fields inside a requested post body */
    public function validateArray($keys, $arr)
    {
        return !array_diff_key(array_flip($keys), $arr);
    }
    /* Set response message response_to_json = set_response */
    public function response_to_json($success = true, $message = "success", $data = null, $extraField = null, $extraData = null)
    {
        $response = ["status" => $success, "message" => $message, "data" => $data];
        if ($extraField != null && $extraData != null) {
            $response[$extraField] = $extraData;
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-Requested-With, Origin, Authorization");
        print json_encode($response);
        die;
    }
    public function responseJSON($data)
    {
        print json_encode($data);
        die;
    }
    /* For API Development */
}
