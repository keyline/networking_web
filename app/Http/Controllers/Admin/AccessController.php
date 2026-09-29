<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Module;
use App\Models\Admin;
use App\Models\UserAccess;
use Auth;
use Session;
use Helper;
use Hash;

class AccessController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Sub User Access',
            'controller'        => 'AccessController',
            'controller_route'  => 'access',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'access.list';
            $data['rows']                   = UserAccess::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'user_id'             => 'required',
                    'module_id'           => 'required'
                ];
                if($this->validate($request, $rules)){
                    $fields = [
                        'user_id'             => $postData['user_id'],
                        'module_id'           => json_encode($postData['module_id']),
                    ];
                    UserAccess::insert($fields);
                    return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'access.add-edit';
            $data['row']                    = [];
            $data['modules']                = Module::where('status', '=', 1)->get();
            $data['subUsers']               = Admin::where('status', '=', 1)->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'access.add-edit';
            $data['row']                    = UserAccess::where($this->data['primary_key'], '=', $id)->first();
            $data['modules']                = Module::where('status', '=', 1)->get();
            $data['subUsers']               = Admin::where('status', '=', 1)->get();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'user_id'             => 'required',
                    'module_id'           => 'required'
                ];
                if($this->validate($request, $rules)){
                    $fields = [
                        'user_id'             => $postData['user_id'],
                        'module_id'           => json_encode($postData['module_id']),
                        'updated_at'          => date('Y-m-d H:i:s')
                    ];
                    UserAccess::where($this->data['primary_key'], '=', $id)->update($fields);
                    return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Updated Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* edit */
    /* delete */
        public function delete(Request $request, $id){
            $id                             = Helper::decoded($id);
            $fields = [
                'status'             => 3
            ];
            UserAccess::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = UserAccess::find($id);
            if ($model->status == 1)
            {
                $model->status  = 0;
                $msg            = 'Deactivated';
            } else {
                $model->status  = 1;
                $msg            = 'Activated';
            }            
            $model->save();
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' '.$msg.' Successfully !!!');
        }
    /* change status */
}
