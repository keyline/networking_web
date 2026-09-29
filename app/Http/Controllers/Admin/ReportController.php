<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Attendance;
use App\Models\Odometer;
use App\Models\Admin;
use App\Models\Employees;
use Auth;
use Session;
use Helper;
use Hash;
use DB;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use PHPUnit\TextUI\Help;

class ReportController extends Controller
{
    /* attendance report */
        public function attendanceReport(){
            $title                          = 'Attendance Report';
            $page_name                      = 'report.attendance-report';
            $data['rows']                   = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.*', 'employee_types.name as employee_type_name')
                                                        ->where('employees.status', '=', 1)
                                                        ->where('employees.name', '!=', 'VACANT')
                                                        ->orderBy('employees.id', 'ASC')
                                                        ->get();
            $data['is_search']              = 0;
            $data['month']                  = date('m');
            $data['year']                   = date('Y');
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
        public function attendanceReportSearch(Request $request){
            $postData                       = $request->all();
            $month_year                     = explode("-", $postData['month_year']);
            $month                          = $month_year[1];
            $year                           = $month_year[0];
            $title                          = 'Attendance Report';
            $page_name                      = 'report.attendance-report';
            $data['rows']                   = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.*', 'employee_types.name as employee_type_name')
                                                        ->where('employees.status', '=', 1)
                                                        ->where('employees.name', '!=', 'VACANT')
                                                        ->orderBy('employees.id', 'ASC')
                                                        ->get();
            $data['is_search']              = 1;
            $data['month']                  = $month;
            $data['year']                   = $year;
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
        public function getAttendanceDetails(Request $request){
            $apiStatus          = TRUE;
            $apiMessage         = 'Data Available !!!';
            $apiResponse        = [];
            $apiExtraField      = '';
            $apiExtraData       = '';
            $postData           = $request->all();
            $attn_date          = $postData['date'];
            $uId                = $postData['userId'];
            $name               = $postData['name'];
            $attnDatas          = [];
            $attnList           = Attendance::where('employee_id', '=', $uId)->where('attendance_date', '=', $attn_date)->orderBy('id', 'ASC')->get();
            $tot_attn_time      = 0;
            $isPresent          = 0;
            if($attnList){
                foreach($attnList as $attnRow){
                    if($attnRow->status == 1){
                        $attnDatas[]          = [
                            'punch_date'            => date_format(date_create($attn_date), "M d, Y"),
                            'label'                 => 'IN',
                            'time'                  => date_format(date_create($attnRow->start_timestamp), "h:i A"),
                            'address'               => (($attnRow->start_address != '')?$attnRow->start_address:''),
                            'image'                 => env('UPLOADS_URL').'user/'.$attnRow->start_image,
                            'type'                  => 1
                        ];
                    }
                    if($attnRow->status == 2){
                        $attnDatas[]          = [
                            'punch_date'            => date_format(date_create($attn_date), "M d, Y"),
                            'label'                 => 'IN',
                            'time'                  => date_format(date_create($attnRow->start_timestamp), "h:i A"),
                            'address'               => (($attnRow->start_address != '')?$attnRow->start_address:''),
                            'image'                 => env('UPLOADS_URL').'user/'.$attnRow->start_image,
                            'type'                  => 1
                        ];
                        $attnDatas[]          = [
                            'punch_date'            => date_format(date_create($attn_date), "M d, Y"),
                            'label'                 => 'OUT',
                            'time'                  => (($attnRow->end_timestamp != '')?date_format(date_create($attnRow->end_timestamp), "h:i A"):''),
                            'address'               => (($attnRow->end_address != '')?$attnRow->end_address:''),
                            'image'                 => (($attnRow->end_image != '')?env('UPLOADS_URL').'user/'.$attnRow->end_image:''),
                            'type'                  => 2
                        ];
                    }
                }
            }

            $odometer_list      = Odometer::select('start_km', 'start_image', 'start_timestamp', 'end_km', 'end_image', 'end_timestamp', 'travel_distance', 'status', 'start_address', 'end_address')
                                    ->where('employee_id', $uId)
                                    ->where('odometer_date', '=', $attn_date)
                                    ->orderBy('odometer_date', 'ASC')
                                    ->get();
            $odometer_data      = [];
            if($odometer_list){
                foreach($odometer_list as $odometerRow){
                    $odometer_data[] = [
                        'start_km'              => $odometerRow->start_km,
                        'start_image'           => (($odometerRow->start_image)?env('UPLOADS_URL').'user/'.$odometerRow->start_image:''),
                        'start_timestamp'       => date_format(date_create($odometerRow->start_timestamp), "h:i A"),
                        'start_address'         => $odometerRow->start_address,
                        'end_km'                => $odometerRow->end_km,
                        'end_image'             => (($odometerRow->end_image != '')?env('UPLOADS_URL').'user/'.$odometerRow->end_image:''),
                        'end_timestamp'         => date_format(date_create($odometerRow->end_timestamp), "h:i A"),
                        'end_address'           => $odometerRow->end_address,
                        'travel_distance'       => (($odometerRow->status == 2)?$odometerRow->travel_distance:'NA'),
                    ];
                }
            }
            $data        = [
                'attnDatas'             => $attnDatas,
                'odometer_data'         => $odometer_data,
                'name'                  => $name,
                'attn_date'             => $attn_date,
            ];
            echo $modalHTML = view('admin.maincontents.report.attendance-modal', $data);die;
            // $apiResponse = array('modalHTML' => $modalHTML);
            // $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
        }
    /* attendance report */
    /* odometer report */
        public function odometerReport(){
            $title                          = 'Odometer Report';
            $page_name                      = 'report.odometer-report';
            $data['rows']                   = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.*', 'employee_types.name as employee_type_name')
                                                        ->where('employees.status', '=', 1)
                                                        ->orderBy('employees.id', 'ASC')
                                                        ->get();
            $data['is_search']              = 0;
            $data['month']                  = date('m');
            $data['year']                   = date('Y');
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
        public function odometerReportSearch(Request $request){
            $postData                       = $request->all();
            $month_year                     = explode("-", $postData['month_year']);
            $month                          = $month_year[1];
            $year                           = $month_year[0];
            $title                          = 'Odometer Report';
            $page_name                      = 'report.odometer-report';
            $data['rows']                   = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.*', 'employee_types.name as employee_type_name')
                                                        ->where('employees.status', '=', 1)
                                                        ->orderBy('employees.id', 'ASC')
                                                        ->get();
            $data['is_search']              = 1;
            $data['month']                  = $month;
            $data['year']                   = $year;
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* odometer report */
    /* odometer details report */
        public function odometerDetailsReport(){
            $title                          = 'Odometer Details Report';
            $page_name                      = 'report.odometer-details-report';
            $data['rows']                   = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.*', 'employee_types.name as employee_type_name')
                                                        ->where('employees.status', '=', 1)
                                                        ->orderBy('employees.id', 'ASC')
                                                        ->get();
            // Helper::pr($data['rows']);
            $data['is_search']              = 0;
            $data['month']                  = date('m');
            $data['year']                   = date('Y');
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
        public function odometerAllDetailsReport(){
            $title                          = 'Odometer Details Report';
            $page_name                      = 'report.odometer-all-details-report';
            $data['rows']                   = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.*', 'employee_types.name as employee_type_name')
                                                        ->where('employees.status', '=', 1)
                                                        ->orderBy('employees.id', 'ASC')
                                                        ->get();
            // Helper::pr($data['rows']);
            $data['is_search']              = 0;
            $data['month']                  = date('m');
            $data['year']                   = date('Y');
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
        public function odometerDetailsReportSearch(Request $request){
            $postData                       = $request->all();
            $month_year                     = explode("-", $postData['month_year']);
            $month                          = $month_year[1];
            $year                           = $month_year[0];
            $title                          = 'Odometer Details Report';
            $page_name                      = 'report.odometer-details-report';
            $data['rows']                   = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.*', 'employee_types.name as employee_type_name')
                                                        ->where('employees.status', '=', 1)
                                                        ->orderBy('employees.id', 'ASC')
                                                        ->get();
            $data['is_search']              = 1;
            $data['month']                  = $month;
            $data['year']                   = $year;
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
        public function getOdometerDetails(Request $request){
            $apiStatus          = TRUE;
            $apiMessage         = 'Data Available !!!';
            $apiResponse        = [];
            $apiExtraField      = '';
            $apiExtraData       = '';
            $postData           = $request->all();
            $attn_date          = $postData['date'];
            $uId                = $postData['userId'];
            $name               = $postData['name'];
            $attnDatas          = [];
            $attnList           = Attendance::where('employee_id', '=', $uId)->where('attendance_date', '=', $attn_date)->orderBy('id', 'ASC')->get();
            $tot_attn_time      = 0;
            $isPresent          = 0;

            if($attnList){
                foreach($attnList as $attnRow){
                    if($attnRow->status == 1){
                        $attnDatas[]          = [
                            'punch_date'            => date_format(date_create($attn_date), "M d, Y"),
                            'label'                 => 'IN',
                            'time'                  => date_format(date_create($attnRow->start_timestamp), "h:i A"),
                            'address'               => (($attnRow->start_address != '')?$attnRow->start_address:''),
                            'image'                 => env('UPLOADS_URL').'user/'.$attnRow->start_image,
                            'type'                  => 1
                        ];
                    }
                    if($attnRow->status == 2){
                        $attnDatas[]          = [
                            'punch_date'            => date_format(date_create($attn_date), "M d, Y"),
                            'label'                 => 'IN',
                            'time'                  => date_format(date_create($attnRow->start_timestamp), "h:i A"),
                            'address'               => (($attnRow->start_address != '')?$attnRow->start_address:''),
                            'image'                 => env('UPLOADS_URL').'user/'.$attnRow->start_image,
                            'type'                  => 1
                        ];
                        $attnDatas[]          = [
                            'punch_date'            => date_format(date_create($attn_date), "M d, Y"),
                            'label'                 => 'OUT',
                            'time'                  => (($attnRow->end_timestamp != '')?date_format(date_create($attnRow->end_timestamp), "h:i A"):''),
                            'address'               => (($attnRow->end_address != '')?$attnRow->end_address:''),
                            'image'                 => (($attnRow->end_image != '')?env('UPLOADS_URL').'user/'.$attnRow->end_image:''),
                            'type'                  => 2
                        ];
                    }
                }
            }

            $odometer_list      = Odometer::select('id', 'start_km', 'start_image', 'start_timestamp', 'end_km', 'end_image', 'end_timestamp', 'travel_distance', 'status', 'start_address', 'end_address')
                                    ->where('employee_id', $uId)
                                    ->where('odometer_date', '=', $attn_date)
                                    ->orderBy('odometer_date', 'ASC')
                                    ->get();
            $odometer_data      = [];
            if($odometer_list){
                foreach($odometer_list as $odometerRow){
                    $odometer_data[] = [
                        'id'                    => $odometerRow->id,
                        'start_km'              => $odometerRow->start_km,
                        'start_image'           => (($odometerRow->start_image)?env('UPLOADS_URL').'user/'.$odometerRow->start_image:''),
                        'start_timestamp'       => date_format(date_create($odometerRow->start_timestamp), "h:i A"),
                        'start_address'         => $odometerRow->start_address,
                        'end_km'                => $odometerRow->end_km,
                        'end_image'             => (($odometerRow->end_image != '')?env('UPLOADS_URL').'user/'.$odometerRow->end_image:''),
                        'end_timestamp'         => date_format(date_create($odometerRow->end_timestamp), "h:i A"),
                        'end_address'           => $odometerRow->end_address,
                        'travel_distance'       => (($odometerRow->status == 2)?$odometerRow->travel_distance:'NA'),
                    ];
                }
            }
            $data        = [
                'attnDatas'             => $attnDatas,
                'odometer_data'         => $odometer_data,
                'name'                  => $name,
                'attn_date'             => $attn_date,
            ];
            echo $modalHTML = view('admin.maincontents.report.odometer-modal', $data);die;
            // $apiResponse = array('modalHTML' => $modalHTML);
            // $this->response_to_json($apiStatus, $apiMessage, $apiResponse, $apiExtraField, $apiExtraData);
        }
        public function updateOdometerDetails(Request $request){
            $apiStatus          = TRUE;
            $apiMessage         = 'Data Available !!!';
            $apiResponse        = [];
            $apiExtraField      = '';
            $apiExtraData       = '';
            $postData           = $request->all();
            $odometerId         = $postData['odometer_id'];     
            $name               = $postData['name'];       
            $odometer           = Odometer::where('id', '=', $odometerId)->first();                
            $odometer_data      = [];    
            if($odometer){
                $odometer_data = [
                    'start_km'              => $odometer->start_km,
                    'start_image'           => (($odometer->start_image)?env('UPLOADS_URL').'user/'.$odometer->start_image:''),
                    'start_timestamp'       => date_format(date_create($odometer->start_timestamp), "h:i A"),
                    'start_address'         => $odometer->start_address,
                    'end_km'                => $odometer->end_km,
                    'end_image'             => (($odometer->end_image != '')?env('UPLOADS_URL').'user/'.$odometer->end_image:''),
                    'end_timestamp'         => date_format(date_create($odometer->end_timestamp), "h:i A"),
                    'end_address'           => $odometer->end_address,
                    'travel_distance'       => (($odometer->status == 2)?$odometer->travel_distance:'NA'),
                ];
            }       
            $data        = [
                'odometer_data'         => $odometer_data,
                'odometerId'            => $odometerId,
                'name'                  => $name,
            ];
            echo $modalHTML = view('admin.maincontents.report.odometer-edit-modal', $data);die;            
        }

        public function storeOdometerDetails(Request $request, $id){
            $id                             = $id;
            // Helper::pr($id);
            $sessionData = Auth::guard('admin')->user();
            $data['row']                    = Odometer::where('id', '=', $id)->first();            
            // Helper::pr($data['row']);
            if($request->isMethod('post')){
                $postData = $request->all();
                //  Helper::pr($postData);
                $strt_time = explode('T',$postData['start_timestamp']);
                $end_time = explode('T',$postData['end_timestamp']);
                $start_timestamp = $strt_time[0].' '.$strt_time[1];
                $end_timestamp = $end_time[0].' '.$end_time[1];
                /* start image */
                $imageFile      = $request->file('start_image');
                if($imageFile != ''){
                    $imageName      = $imageFile->getClientOriginalName();
                    $uploadedFile   = $this->upload_single_file('start_image', $imageName, 'user', 'image');
                    if($uploadedFile['status']){
                        $start_image = $uploadedFile['newFilename'];
                    } else {
                        return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                    }
                } else {
                    $start_image = $data['row']->start_image;
                }

                /* end image */
                $imageFile      = $request->file('end_image');
                if($imageFile != ''){
                    $imageName      = $imageFile->getClientOriginalName();
                    $uploadedFile   = $this->upload_single_file('end_image', $imageName, 'user', 'image');
                    if($uploadedFile['status']){
                        $end_image = $uploadedFile['newFilename'];
                    } else {
                        return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                    }
                } else {
                    $end_image = $data['row']->end_image;
                }
                $fields = [
                    'start_image'   => $start_image,
                    'start_km'      => $postData['start_km'],
                    'start_timestamp'  =>  $start_timestamp,
                    'start_address'         => $postData['start_address'],
                    'start_latitude'        => $postData['start_lat'] ?? $data['row']->start_latitude,
                    'start_longitude'       => $postData['start_lng'] ?? $data['row']->start_longitude,
                    'travel_distance'       => $postData['travel_distance'],
                    'status'            => isset($postData['travel_distance'])? 2:1,
                    'end_image'   => $end_image,
                    'end_km'      => $postData['end_km'],
                    'end_timestamp'  => $end_timestamp,
                    'end_latitude'        => $postData['end_lat'] ?? $data['row']->end_latitude,
                    'end_longitude'       => $postData['end_lng'] ?? $data['row']->end_longitude,
                    'end_address'         => $postData['end_address'],
                    'updated_at'    => date('Y-m-d H:i:s'),
                    'updated_by'            => $sessionData->id,
                ];
                //    Helper::pr($fields);
                Odometer::where('id', '=', $id)->update($fields);                
            }            
            // $apiStatus          = TRUE;
            // $apiMessage         = 'Data Available !!!';
            // $apiResponse        = [];
            // $apiExtraField      = '';
            // $apiExtraData       = '';
            // $postData           = $request->all();
            // $attn_date          = $data['row']->odometer_date;
            // $uId                = $data['row']->employee_id;
            // $name               = Employees::where('id', '=', $uId)->value('name');

            // $odometer_list      = Odometer::select('id', 'start_km', 'start_image', 'start_timestamp', 'end_km', 'end_image', 'end_timestamp', 'travel_distance', 'status', 'start_address', 'end_address')
            //                         ->where('employee_id', $uId)
            //                         ->where('odometer_date', '=', $attn_date)
            //                         ->orderBy('odometer_date', 'ASC')
            //                         ->get();
            // $odometer_data      = [];
            // if($odometer_list){
            //     foreach($odometer_list as $odometerRow){
            //         $odometer_data[] = [
            //             'id'                    => $odometerRow->id,
            //             'start_km'              => $odometerRow->start_km,
            //             'start_image'           => (($odometerRow->start_image)?env('UPLOADS_URL').'user/'.$odometerRow->start_image:''),
            //             'start_timestamp'       => date_format(date_create($odometerRow->start_timestamp), "h:i A"),
            //             'start_address'         => $odometerRow->start_address,
            //             'end_km'                => $odometerRow->end_km,
            //             'end_image'             => (($odometerRow->end_image != '')?env('UPLOADS_URL').'user/'.$odometerRow->end_image:''),
            //             'end_timestamp'         => date_format(date_create($odometerRow->end_timestamp), "h:i A"),
            //             'end_address'           => $odometerRow->end_address,
            //             'travel_distance'       => (($odometerRow->status == 2)?$odometerRow->travel_distance:'NA'),
            //         ];
            //     }
            // }
            // $data        = [
            //     'odometer_data'         => $odometer_data,
            //     'name'                  => $name,
            //     'attn_date'             => $attn_date,
            // ];
            // echo $modalHTML = view('admin.maincontents.report.odometer-modal', $data);die;   
            return redirect()->back()->with(['success_message' => 'Odometer details updated successfully !!!']);                     
        }
    /* odometer details report */
}
