<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Admin;
use App\Models\ClientOrder;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\Product;
use App\Models\Unit;
use App\Models\UserDevice;

use Auth;
use Session;
use Helper;
use Hash;
use Illuminate\Support\Facades\DB;
use PHPUnit\TextUI\Help;

class OrdersController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Orders',
            'controller'        => 'OrdersController',
            'controller_route'  => 'orders',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list($slug){
            $getOrderStatus                 = $this->get_order_status($slug);
            $data['getOrderStatus']         = $getOrderStatus;
            $data['module']                 = $this->data;
            $data['order_status']           = $slug;
            $title                          = ucwords($slug) . ' ' . $this->data['title'].' List';
            $page_name                      = 'orders.list';
            $data['rows']                   = DB::table('client_orders')
                                                ->join('employee_types', 'employee_types.id', '=', 'client_orders.employee_type_id')
                                                ->join('employees', 'employees.id', '=', 'client_orders.employee_id')
                                                ->join('client_types', 'client_types.id', '=', 'client_orders.client_type_id')
                                                ->join('clients', 'clients.id', '=', 'client_orders.client_id')
                                                ->select(
                                                    'client_orders.*',
                                                    'employee_types.prefix as employee_type_prefix',
                                                    'employees.name as employee_name',
                                                    'client_types.name as client_type_name',
                                                    'clients.name as client_name'
                                                )
                                                ->where('client_orders.status', $getOrderStatus)
                                                ->orderBy('client_orders.id', 'DESC')
                                                ->get();
            $sessionData                    = Auth::guard('admin')->user();
            $data['admin']                  = Admin::where('id', '=', $sessionData->id)->orderBy('id', 'DESC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* delete */
        public function delete(Request $request, $id){
            $id                             = Helper::decoded($id);
            $fields = [
                'status'             => 3
            ];
            Product::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request){
            $id                             = Helper::decoded($request->order_id);
            $getClientOrder                 = ClientOrder::where($this->data['primary_key'], '=', $id)->first();
            $employee_id                    = $getClientOrder->employee_id;
            $order_status                   = $request->order_status;
            if($order_status == 1){
                $order_status_name = 'submitted';
            } elseif($order_status == 2){
                $order_status_name = 'approved';
            } elseif($order_status == 3){
                $order_status_name = 'dispatch';
            } elseif($order_status == 4){
                $order_status_name = 'billing';
            } elseif($order_status == 5){
                $order_status_name = 'completed';
            }
            $fields = [
                'status'             => $order_status
            ];
            ClientOrder::where($this->data['primary_key'], '=', $id)->update($fields);
            /* throw notification */
                $getTemplate = $this->getNotificationTemplates('ORDER STATUS');
                if($getTemplate){
                    $users[]            = $employee_id;
                    $notificationFields = [
                        'title'             => $getTemplate['title'],
                        'description'       => $getTemplate['description'],
                        'to_users'          => $employee_id,
                        'users'             => json_encode($users),
                        'is_send'           => 1,
                        'send_timestamp'    => date('Y-m-d H:i:s'),
                    ];
                    Notification::insert($notificationFields);
                    $getUserFCMTokens   = UserDevice::select('fcm_token')->where('fcm_token', '!=', '')->where('user_id', '=', $employee_id)->groupBy('fcm_token')->get();
                    $tokens             = [];
                    $type               = 'order-status';
                    if($getUserFCMTokens){
                        foreach($getUserFCMTokens as $getUserFCMToken){
                            $response           = $this->sendCommonPushNotification($getUserFCMToken->fcm_token, $getTemplate['title'], $getTemplate['description'], $type);
                        }
                    }
                }
            /* throw notification */
            return redirect("admin/" . $this->data['controller_route'] . "/" . $order_status_name)->with('success_message', $this->data['title'].' Marked As '.ucwords($order_status_name).' Successfully !!!');
        }
    /* change status */
    public function viewOrderDetails($slug, $id)
    {
        $id                             = Helper::decoded($id);       
        //  Helper::pr($id);
        $getOrderStatus                 = $this->get_order_status($slug);
        // Helper::pr($getOrderStatus);
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;        
        // Helper::pr($data['slug']);
        // $page_name                      = 'employee-details.view_order_details';
        $page_name                      = 'orders.view_order_details';        
        $rows                           =   DB::table('client_order_details')
                                            ->join('products', 'products.id', '=', 'client_order_details.product_id')
                                            ->join('units', 'units.id', '=', 'client_order_details.case_unit')
                                            ->select(
                                                'client_order_details.*',
                                                'products.name as product_name',
                                                'products.short_desc as product_desc',
                                                'units.name as unit_name'
                                            )
                                            ->where('client_order_details.order_id', '=', $id)
                                            ->get();

        $order_details  = [];
        $items          = DB::table('client_order_details')
                                    ->join('units', 'units.id', '=', 'client_order_details.case_unit')
                                    ->join('products', 'client_order_details.product_id', '=', 'products.id')
                                    ->select('client_order_details.*', 'units.name as case_unit_name', 'products.name as product_name', 'products.short_desc as product_short_desc')
                                    ->where('client_order_details.order_id', '=', $id)
                                    ->orderBy('client_order_details.id', 'DESC')
                                    ->get();
        if($items){
            foreach($items as $item){
                $getProduct         = Product::where('id', '=', $item->product_id)->first();
                if($getProduct){
                    $getPackageSizeUnit = Unit::select('name as package_unit_name')->where('id', '=', $getProduct->package_size_unit)->first();
                    $getPerQtyUnit      = Unit::select('name as per_qty_unit_name')->where('id', '=', $getProduct->per_case_qty_unit)->first();
                    $order_details[]       = [
                        'product_id'            => $item->product_id,
                        'product_name'          => $item->product_name,
                        'product_short_desc'    => $item->product_short_desc,
                        'rate'                  => number_format($item->rate,2),
                        'qty'                   => $item->qty,
                        'subtotal'              => number_format($item->subtotal,2),
                        'package_size'          => $getProduct->package_size . (($getPackageSizeUnit)?$getPackageSizeUnit->package_unit_name:''),
                        'case_size'             => $getProduct->case_size . (($item->case_unit_name)),
                        'qty_per_case'          => $getProduct->per_case_qty . (($getPerQtyUnit)?$getPerQtyUnit->per_qty_unit_name:''),
                    ];
                }
            }
        }
        //    Helper::pr($order_details);

        $data['row']                    = $order_details;  
        //  Helper::pr($data['row']); 
        $data['order_details']          = ClientOrder::where('status', '=', $getOrderStatus)->where('id', '=', $id)->first(); 
        //  Helper::pr($data['order_details']);                        
        $data['client_details']         = Client::where('status', '=', 1)->where('id', '=', $data['order_details']->client_id)->first();
        $data['order_client_types']     = ClientType::where('status', '=', 1)->where('id', '=', $data['order_details']->client_type_id)->first();
        $data['employee_details']       = Employees::where('status', '=', 1)->where('id', '=', $data['order_details']->employee_id)->first();
        $data['employee_types']         = EmployeeType::where('status', '=', 1)->where('id', '=', $data['order_details']->employee_type_id)->first();
        $title                          = $this->data['title'] . ' View Order Details : ' . (($data['order_details'])?$data['order_details']->order_no:'');
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    public function get_order_status($slug){
        $order_status = 0;
        if($slug == 'submitted'){
            $order_status = 1;
        } elseif($slug == 'approved'){
            $order_status = 2;
        } elseif($slug == 'dispatch'){
            $order_status = 3;
        } elseif($slug == 'billing'){
            $order_status = 4;
        } elseif($slug == 'completed'){
            $order_status = 5;
        }
        return $order_status;
    }
    public function getNotificationTemplates($notificationType){
        $returnArray                    = [];
        $getRandomNotificationTemplate  = NotificationTemplate::select('title', 'description')->where('status', '=', 1)->where('type', '=', $notificationType)->inRandomOrder()->first();
        if($getRandomNotificationTemplate){
            $returnArray                = [
                'title'         => $getRandomNotificationTemplate->title,
                'description'   => $getRandomNotificationTemplate->description,
            ];
        }
        return $returnArray;
    }
}
