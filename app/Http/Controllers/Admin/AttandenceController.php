<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\ClientOrder;
use App\Models\Client;
use App\Models\Employees;
use App\Models\EmployeeType;
use Auth;
use Session;
use Helper;
use Hash;
use Illuminate\Support\Facades\DB;

class AttandenceController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Attandence',
            'controller'        => 'AttandenceController',
            'controller_route'  => 'attandence',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'attandence.list';           
            $currentDate = date('Y-m-d');
            // Split the date into an array
            $dateParts = explode('-', $currentDate);

            // Extract the year and month
            $year = $dateParts[0]; // 2024
            $month = $dateParts[1]; // 12            
            $data['rows']               = Employees::where('status', '=', 1)->orderBy('id', 'ASC')->get();
            
            //  Helper::pr($data['rows']);
            
            $data['employee_types']         = EmployeeType::where('status', '=', 1)->orderBy('id', 'ASC')->get();
            // $sessionData = Auth::guard('admin')->user();
            // $data['admin'] = Admin::where('id', '=', $sessionData->id)->orderBy('id', 'DESC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */    
    public function viewDetails($id)
    {
        //  dd($id);
        DB::enableQueryLog();
        $id                             = Helper::decoded($id);       
        $data['module']                 = $this->data;               
        $page_name                      = 'attandence.view_details';
        $data['row']                    = Employees::where('status', '!=', 3)->where('id', '=', $id)->orderBy('id', 'DESC')->first();   
        $data['attandence']             = Attendance::where('status', '!=', 3)->where('employee_id', '=', $id)->orderBy('id', 'DESC')->get();   
        // $query = DB::table('employees')
        // ->leftJoin('attendances', function($join) use ($month, $year) {
        //     $join->on('employees.id', '=', 'attendances.employee_id')
        //         ->whereMonth('attendances.attendance_date', $month)
        //         ->whereYear('attendances.attendance_date', $year);
        // })
        // ->join('employee_types', 'employee_types.id', '=', 'employees.employee_type_id')
        // ->select(
        //     'employees.id as employee_id',
        //     'employees.name as employee_name',
        //     'employees.phone',
        //     'employees.profile_image',
        //     'employees.employee_no',
        //     'employees.status as employee_status',
        //     'employee_types.name as employee_dept',
        //     DB::raw('COUNT(attendances.attendance_date) as attendance_count')
        // )
        // ->groupBy('employees.id', 'employees.name', 'employees.phone', 'employees.profile_image', 'employees.employee_no', 'employees.status', 'employee_types.name')
        // ->where('employees.id', '=', $id);
        // $employees = $query->get();
        //   dd(DB::getQueryLog());
        // Helper::pr($data['attandence']);
        // Prepare the data for JavaScript, you can use json_encode to pass data
        $data['rowJson'] = json_encode($data['attandence']); // Passing as JSON
        // Helper::pr($data['row'])     ;
        // $data['employee_department']    = EmployeeType::where('status', '=', 1)->where('id', '=', $data['row']->employee_type_id)->orderBy('name', 'ASC')->first();                
        // $data['order']                  = ClientOrder::where('status', '!=', 3)->where('employee_id', '=', $id)->orderBy('id', 'DESC')->get(); 
        // $data['checkin']                = ClientCheckIn::where('status', '!=', 3)->where('employee_id', '=', $id)->orderBy('id', 'DESC')->get();
        // $data['attandence']                = Attendance::where('status', '!=', 3)->where('employee_id', '=', $id)->orderBy('id', 'DESC')->get();
        $title                          = $this->data['title'] . ' View Details : ' . (($data['row'])?$data['row']->name:'');
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }

    public function filter(Request $request)
{
    $month = $request->input('month') ?? date('m'); // Default to current month
    $year = $request->input('year') ?? date('Y'); // Default to current year
    $department = $request->input('department') ?? 'all';
    $inactiveStaff = $request->input('inactiveStaff') ?? 0;
    DB::enableQueryLog();
    $query = DB::table('employees')
        ->leftJoin('attendances', function($join) use ($month, $year) {
            $join->on('employees.id', '=', 'attendances.employee_id')
                ->whereMonth('attendances.attendance_date', $month)
                ->whereYear('attendances.attendance_date', $year);
        })
        ->join('employee_types', 'employee_types.id', '=', 'employees.employee_type_id')
        ->select(
            'employees.id as employee_id',
            'employees.name as employee_name',
            'employees.phone',
            'employees.profile_image',
            'employees.employee_no',
            'employees.status as employee_status',
            'employee_types.name as employee_dept',
            DB::raw('COUNT(attendances.attendance_date) as attendance_count')
        )
        ->groupBy('employees.id', 'employees.name', 'employees.phone', 'employees.profile_image', 'employees.employee_no', 'employees.status', 'employee_types.name');

    if ($department !== 'all') {
        $query->where('employees.employee_type_id', $department);
    }

    if ($inactiveStaff) {
        $query->where('employees.status', '=', '0'); // Assuming '0' means inactive
    }

    $employees = $query->get();
    // dd(DB::getQueryLog());
    // Encode employee_id using Helper::encoded
    $employees = $employees->map(function ($employee) {
        $employee->encoded_id = Helper::encoded($employee->employee_id);        
        return $employee;
        
    });

    return response()->json($employees);
}
public function generateCalendar($month, $year)
{
     $firstDay = Carbon::createFromDate($year, $month)->startOfMonth()->dayOfWeek; 
     $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;
     $currentDate = Carbon::now();

    $calendarHtml = '';
    $day = 1;

    for ($week = 0; $week < 6; $week++) {
        $calendarHtml .= '<tr>';

        for ($weekday = 0; $weekday < 7; $weekday++) {
            if ($week == 0 && $weekday < $firstDay) {
                $calendarHtml .= '<td></td>';
            } elseif ($day <= $daysInMonth) {
                $date = Carbon::createFromDate($year, $month, $day);
                $isBeforeToday = $date->isBefore($currentDate);
                $isSunday = $date->isSunday(); // Check if it's Sunday

                // Apply green color for dates up to today, and grey for others
                $dateClass = $isBeforeToday ? 'green' : 'grey';
                if ($isSunday) {                    
                    $dateClass = 'green';  // Highlight Sundays in green
                }

                $calendarHtml .= "<td><div class='cal_date {$dateClass}' data-bs-toggle='modal' data-bs-target='#attendance_info_popup'><p>{$day}</p></div></td>";
                $day++;
            } else {
                $calendarHtml .= '<td></td>';
            }
        }

        $calendarHtml .= '</tr>';

        if ($day > $daysInMonth) break;
    }

    return $calendarHtml;
}



}
