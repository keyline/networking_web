<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\ClientCheckIn;
use App\Models\ClientOrder;
use App\Models\ClientOrderDetail;
use App\Models\ClientType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Companies;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Models\Hotel;
use App\Models\Odometer;
use App\Models\Role;
use App\Models\District;
use Auth;
use Session;
use Helper;
use Hash;
use Illuminate\Support\Facades\DB;
use PHPUnit\TextUI\Help;

class EmployeeDetailsController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Employee: ',
            'controller'        => 'EmployeeDetailsController',
            'controller_route'  => 'employee-details',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list($slug){
            $data['slug']                   = $slug;
            $data['module']                 = $this->data;
            $title                          = ucfirst($data['slug']).' List';
            $page_name                      = 'employee-details.list';
            $sessionType                    = Session::get('type');
            $data['employee_department']    = EmployeeType::where('status', '!=', 3)->where('slug', '=', $data['slug'])->orderBy('id', 'ASC')->first();
            if($slug != 'all'){
                $data['rows']                   = Employees::where('status', '!=', 3)->where('employee_type_id', '=', $data['employee_department']->id)->orderBy('id', 'DESC')->get();
            } else {
                $data['rows']                   = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.*', 'employee_types.name as employee_type_name')
                                                        ->where('employees.status', '=', 1)
                                                        ->orderBy('employees.id', 'DESC')
                                                        ->get();
            }
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* add */
        public function add(Request $request, $slug){
            $data['module']           = $this->data;    
            $data['slug']             = $slug;
            $data['employee_department']    = EmployeeType::where('status', '!=', 3)->where('slug', '=', $data['slug'])->orderBy('id', 'ASC')->first();
            $data['districts']              = District::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['empTypes']               = EmployeeType::select('id', 'name')->where('status', '=', 1)->get();
                                  
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'assign_district'       => 'required',
                    'name'                  => 'required',
                    'employee_type_id'      => 'required',
                    'email'                 => 'required',
                    'whatsapp_no'           => 'required',                    
                    'phone'                 => 'required',                    
                    'password'              => 'required',                    
                ];
                if($this->validate($request, $rules)){
                    $checkValue = Employees::where('name', '=', $postData['name'])->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        /* profile image */
                            $imageFile      = $request->file('image');
                            if($imageFile != ''){
                                $imageName      = $imageFile->getClientOriginalName();
                                $uploadedFile   = $this->upload_single_file('image', $imageName, '', 'image');
                                if($uploadedFile['status']){
                                    $image = $uploadedFile['newFilename'];
                                } else {
                                    return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                                }
                            } else {
                                $image = '';
                            }
                        /* profile image */
                        /* generate employee no */
                            // $prefix         = (($data['employee_department'])?$data['employee_department']->prefix:'');                        
                            $prefix         = 'EC';
                            $getLastEnquiry = Employees::where('employee_type_id', '=', $data['employee_department']->id)->orderBy('id', 'DESC')->first();
                            if($getLastEnquiry){
                                $sl_no                  = $getLastEnquiry->sl_no;
                                $next_sl_no             = $sl_no + 1;
                                $next_sl_no_string      = str_pad($next_sl_no, 5, 0, STR_PAD_LEFT);
                                $employee_no            = $prefix.$next_sl_no_string;
                            } else {
                                $next_sl_no             = 1;
                                $next_sl_no_string      = str_pad($next_sl_no, 5, 0, STR_PAD_LEFT);
                                $employee_no            = $prefix.$next_sl_no_string;
                            }
                        /* generate employee no */
                        /* parent empoyees fetch */
                            $assign_district    = $postData['assign_district'];
                            $employee_type_id   = $postData['employee_type_id'];
                            $empIds             = [];
                            if(!empty($assign_district)){
                                for($d=0;$d<count($assign_district);$d++){
                                    $getUpperLevelEmpTypes = EmployeeType::select('id')->where('status', '=', 1)->where('id', '<', $employee_type_id)->get();
                                    if($getUpperLevelEmpTypes){
                                        foreach($getUpperLevelEmpTypes as $getUpperLevelEmpType){
                                            $getEmps = Employees::select('id', 'name')->where('employee_type_id', '=', $getUpperLevelEmpType->id)->where('status', '=', 1)->whereJsonContains('assign_district', $assign_district[$d])->get();
                                            if($getEmps){
                                                foreach($getEmps as $getEmp){
                                                    if(!in_array($getEmp->id, $empIds)){
                                                        if($getEmp->name != 'VACANT'){
                                                            $empIds[]             = $getEmp->id;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            // Helper::pr($empIds);
                        /* parent empoyees fetch */
                        $fields = [
                            'assign_district'       => json_encode($postData['assign_district']),
                            'name'                  => $postData['name'],
                            'phone'                 => $postData['phone'],
                            'email'                 => $postData['email'],
                            'employee_type_id'      => $postData['employee_type_id'],
                            'sl_no'                 => $next_sl_no,
                            'parent_id'             => json_encode($empIds),
                            'alt_email'             => $postData['alt_email'],
                            'whatsapp_no'           => $postData['whatsapp_no'],
                            'short_bio'             => $postData['short_bio'],
                            'dob'                   => $postData['dob'],
                            'doj'                   => $postData['doj'],
                            'qualification'         => $postData['qualification'],
                            'address'               => $postData['address'],
                            'password'              => Hash::make($postData['password']),
                            'profile_image'         => $image,
                            'employee_no'           => $employee_no,
                            'created_by'            => $sessionData->id,
                        ];
                        Employees::insert($fields);
                        return redirect("admin/" . $this->data['controller_route'] ."/".$data['slug']. "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                    } else {
                        return redirect()->back()->with('error_message', $this->data['title'].' Already Exists !!!');
                    }
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;                          
            $title                          = $this->data['title'].' '.$data['slug'].' Add';
            $page_name                      = 'employee-details.add-edit';
            $data['row']                    = [];                         
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $slug, $id){
            $data['module']                 = $this->data;
            $data['slug']                   = $slug;            
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'employee-details.add-edit';
            $data['row']                    = Employees::where($this->data['primary_key'], '=', $id)->first();
            $data['employee_department']    = EmployeeType::where('id', '=', $data['row']->employee_type_id)->orderBy('id', 'ASC')->first();
            $data['districts']              = District::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['empTypes']               = EmployeeType::select('id', 'name')->where('status', '=', 1)->get();
            
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'assign_district'       => 'required',
                    'name'                  => 'required',
                    'employee_type_id'      => 'required',
                    // 'email'                 => 'required',
                    // 'whatsapp_no'           => 'required',                    
                    // 'phone'                => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = Employees::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                    // if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        /* profile image */
                            $imageFile      = $request->file('image');
                            if($imageFile != ''){
                                $imageName      = $imageFile->getClientOriginalName();
                                $uploadedFile   = $this->upload_single_file('image', $imageName, '', 'image');
                                if($uploadedFile['status']){
                                    $image = $uploadedFile['newFilename'];
                                } else {
                                    return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                                }
                            } else {
                                $image = $data['row']->image;
                            }
                        /* profile image */
                        /* parent empoyees fetch */
                            $assign_district    = $postData['assign_district'];
                            $employee_type_id   = $postData['employee_type_id'];
                            $empIds             = [];
                            if(!empty($assign_district)){
                                for($d=0;$d<count($assign_district);$d++){
                                    $getUpperLevelEmpTypes = EmployeeType::select('id')->where('status', '=', 1)->where('id', '<', $employee_type_id)->get();
                                    if($getUpperLevelEmpTypes){
                                        foreach($getUpperLevelEmpTypes as $getUpperLevelEmpType){
                                            $getEmps = Employees::select('id', 'name')->where('employee_type_id', '=', $getUpperLevelEmpType->id)->where('status', '=', 1)->whereJsonContains('assign_district', $assign_district[$d])->get();
                                            if($getEmps){
                                                foreach($getEmps as $getEmp){
                                                    if(!in_array($getEmp->id, $empIds)){
                                                        if($getEmp->name != 'VACANT'){
                                                            $empIds[]             = $getEmp->id;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            // Helper::pr($empIds);
                        /* parent empoyees fetch */
                        if($postData['password'] != ''){
                            $fields = [
                                'assign_district'       => json_encode($postData['assign_district']),
                                'name'                  => $postData['name'],
                                'phone'                 => $postData['phone'],
                                'email'                 => $postData['email'],
                                'employee_type_id'      => $postData['employee_type_id'],
                                'parent_id'             => json_encode($empIds),
                                'alt_email'             => $postData['alt_email'],
                                'whatsapp_no'           => $postData['whatsapp_no'],
                                'short_bio'             => $postData['short_bio'],
                                'dob'                   => $postData['dob'],
                                'doj'                   => $postData['doj'],
                                'qualification'         => $postData['qualification'],
                                'password'              => Hash::make($postData['password']),
                                'profile_image'         => $image,
                                'address'               => $postData['address'],
                                'created_by'            => $sessionData->id,
                            ];
                        } else {
                            $fields = [
                                'assign_district'       => json_encode($postData['assign_district']),
                                'name'                  => $postData['name'],
                                'phone'                 => $postData['phone'],
                                'email'                 => $postData['email'],
                                'employee_type_id'      => $postData['employee_type_id'],
                                'parent_id'             => json_encode($empIds),
                                'alt_email'             => $postData['alt_email'],
                                'whatsapp_no'           => $postData['whatsapp_no'],
                                'short_bio'             => $postData['short_bio'],
                                'dob'                   => $postData['dob'],
                                'doj'                   => $postData['doj'],
                                'qualification'         => $postData['qualification'],
                                'profile_image'         => $image,
                                'updated_by'            => $sessionData->id,
                                'updated_at'            => date('Y-m-d H:i:s')
                            ];
                        }
                        Employees::where($this->data['primary_key'], '=', $id)->update($fields);                        
                        return redirect("admin/" . $this->data['controller_route'] ."/".$data['slug']. "/list")->with('success_message', $this->data['title']."/".$data['slug'].' Updated Successfully !!!');
                    // } else {
                    //     return redirect()->back()->with('error_message', $this->data['title'].' Already Exists !!!');
                    // }
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* edit */
    /* delete */
        public function delete(Request $request , $slug, $id){
            $id                             = Helper::decoded($id);
            $data['slug']             = $slug;
            $fields = [
                'status'             => 3
            ];
            Employees::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] ."/".$data['slug']. "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $slug, $id){
            $id                             = Helper::decoded($id);
            $data['slug']             = $slug;
            $model                          = Employees::find($id);
            if ($model->status == 1)
            {
                $model->status  = 0;
                $msg            = 'Deactivated';
            } else {
                $model->status  = 1;
                $msg            = 'Activated';
            }            
            $model->save();
            return redirect("admin/" . $this->data['controller_route'] ."/".$data['slug']. "/list")->with('success_message', $this->data['title'].' '.$msg.' Successfully !!!');
        }
    /* change status */
    // view details
    public function viewDetails($slug, $id)
    {
        $id                             = Helper::decoded($id);       
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;        
        $page_name                      = 'employee-details.view_details';
        $data['row']                    = Employees::where('status', '!=', 3)->where('id', '=', $id)->orderBy('id', 'DESC')->first();        
        $data['employee_department']    = EmployeeType::where('status', '=', 1)->where('id', '=', $data['row']->employee_type_id)->orderBy('name', 'ASC')->first();                
        $data['order']                  = ClientOrder::where('status', '!=', 3)->where('employee_id', '=', $id)->orderBy('id', 'DESC')->get(); 
        $data['checkin']                = ClientCheckIn::where('status', '!=', 3)->where('employee_id', '=', $id)->orderBy('id', 'DESC')->get();
        $data['attandence']             = Attendance::where('status', '!=', 3)->where('employee_id', '=', $id)->orderBy('id', 'DESC')->get();
        $data['odometers']             = Odometer::where('status', '!=', 3)->where('employee_id', '=', $id)->orderBy('id', 'DESC')->get();
        $data['travel_distance']        = $data['odometers']->sum('travel_distance');                  
        $title                          = $this->data['title'] . ' View Details : ' . (($data['row'])?$data['row']->name:'');
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    public function viewOrderDetails($slug, $id)
    {
        $id                             = Helper::decoded($id);       
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;        
        $page_name                      = 'employee-details.view_order_details';
        // $page_name                      = 'orders.view_order_details';
        $rows                           = DB::table('client_order_details')
                                            ->join('client_orders', 'client_orders.id', '=', 'client_order_details.order_id')
                                            ->join('products', 'products.id', '=', 'client_order_details.product_id')
                                            ->join('units', 'units.id', '=', 'client_order_details.case_unit')
                                            ->join('admins as created_by_admins', 'created_by_admins.id', '=', 'client_order_details.created_by')
                                            ->join('admins as updated_by_admins', 'updated_by_admins.id', '=', 'client_order_details.updated_by')
                                            ->select(
                                                'client_order_details.*',
                                                'client_orders.order_no',
                                                'products.name as product_name',
                                                'products.short_desc as product_short_desc',
                                                'units.name as unit_name',
                                                'created_by_admins.name as created_by',
                                                'updated_by_admins.name as updated_by'
                                            )
                                            ->where('client_order_details.order_id', $id)
                                            ->get();
        // Helper::pr($rows);

        $data['row']                    = $rows;   
        $data['order_details']          = ClientOrder::where('status', '=', 1)->where('id', '=', $id)->first();                         
        $data['client_details']         = Client::where('status', '=', 1)->where('id', '=', $data['order_details']->client_id)->first();
        $data['order_client_types']     = ClientType::where('status', '=', 1)->where('id', '=', $data['order_details']->client_type_id)->first();
        $data['employee_details']       = Employees::where('status', '=', 1)->where('id', '=', $data['order_details']->employee_id)->first();
        $data['employee_types']         = EmployeeType::where('status', '=', 1)->where('id', '=', $data['order_details']->employee_type_id)->first();
        $title                          = $this->data['title'] . ' View Order Details : ' . (($data['order_details'])?$data['order_details']->order_no:'');
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    // view details
    public function employeewiseorderListRecords(Request $request)
    {
        // Retrieve query parameters
        $orderId = $request->query('orderId');
        $name = $request->query('name');

        // Ensure $orderId is present
        if (empty($orderId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order ID is required.'
            ], 400);
        }

        // Use Laravel's query builder for safe and efficient SQL generation
        $rows = DB::table('client_order_details')
            ->join('client_orders', 'client_orders.id', '=', 'client_order_details.order_id')
            ->join('products', 'products.id', '=', 'client_order_details.product_id')
            ->join('sizes', 'sizes.id', '=', 'client_order_details.size_id')
            ->join('units', 'units.id', '=', 'client_order_details.unit_id')
            ->join('admins as created_by_admins', 'created_by_admins.id', '=', 'client_order_details.created_by')
            ->join('admins as updated_by_admins', 'updated_by_admins.id', '=', 'client_order_details.updated_by')
            ->select(
                'client_order_details.*',
                'client_orders.order_no',
                'products.name as product_name',
                'sizes.name as size_name',
                'units.name as unit_name',
                'created_by_admins.name as created_by',
                'updated_by_admins.name as updated_by'
            )
            ->where('client_order_details.order_id', $orderId)
            ->get();

        // Start building the HTML response
        $html = '<div class="modal-header" style="justify-content: center;">
                    <h6 class="modal-title">Orders Details for <b><u>' . htmlspecialchars($name) . '</u></b></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="table-responsive table-card">
                            <table class="table general_table_style">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Order No</th>
                                        <th>Product Name</th>
                                        <th>Order Unit</th>
                                        <th>Order Qty</th>
                                        <th>Rate</th>
                                        <th>Subtotal</th>
                                        <th>Created Info</th>
                                        <th>Updated Info</th>
                                    </tr>
                                </thead>
                                <tbody>';

        // Add rows to the table
        if ($rows->isNotEmpty()) {
            $sl = 1;
            foreach ($rows as $record) {
                $html .= '<tr>
                            <td>' . $sl++ . '</td>
                            <td>' . htmlspecialchars($record->order_no) . '</td>
                            <td>' . htmlspecialchars($record->product_name) . '</td>
                            <td>' . htmlspecialchars($record->size_name) . ' ' . htmlspecialchars($record->unit_name) . '</td>
                            <td>' . htmlspecialchars($record->qty) . '</td>
                            <td>' . htmlspecialchars($record->rate) . '</td>
                            <td>' . htmlspecialchars($record->subtotal) . '</td>
                            <td>' . htmlspecialchars($record->created_by) . '<br>' . date('M d Y h:i A', strtotime($record->created_at)) . '</td>
                            <td>' . htmlspecialchars($record->updated_by) . '<br>' . date('M d Y h:i A', strtotime($record->updated_at)) . '</td>
                        </tr>';
            }
        } else {
            $html .= '<tr>
                        <td colspan="9" class="text-center">No records found for this order.</td>
                    </tr>';
        }

        $html .= '</tbody>
                        </table>
                    </div>
                </div>
            </div>';

        // Return the HTML response
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }
}
