<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Role;
use App\Models\Modules;
use App\Models\Admin;
use Auth;
use Session;
use Helper;
use Hash;

class RoleController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Role',
            'controller'        => 'RoleController',
            'controller_route'  => 'role',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'role.list';
            $data['rows']                   = Role::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            $sessionData = Auth::guard('admin')->user();
            $data['admin'] = Admin::where('id', '=', $sessionData->id)->orderBy('id', 'DESC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'name'           => 'required',
                    'modules'           => 'required',                    
                ];
                if($this->validate($request, $rules)){
                    $checkValue = Role::where('name', '=', $postData['name'])->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        $fields = [
                            'name'         => strtoupper($postData['name']),
                            'modules_id'   => json_encode($postData['modules'] ?? []),
                            'company_id'   => $sessionData->company_id,
                            'created_by'            => $sessionData->id,  
                        ];
                        Role::insert($fields);
                        return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                    } else {
                        return redirect()->back()->with('error_message', $this->data['title'].' Already Exists !!!');
                    }
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $data['modules']                = Modules::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'role.add-edit';
            $data['row']                    = [];
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $data['modules']                = Modules::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'role.add-edit';
            $data['row']                    = Role::where($this->data['primary_key'], '=', $id)->first();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'name'           => 'required',
                    'modules'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = Role::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        $fields = [
                            'name'         => strtoupper($postData['name']),
                            'modules_id'   => json_encode($postData['modules'] ?? []), 
                            'updated_by'            => $sessionData->id,
                            'company_id'   => $sessionData->company_id,
                            'updated_at'   => date('Y-m-d H:i:s')
                        ];
                        Role::where($this->data['primary_key'], '=', $id)->update($fields);
                        return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Updated Successfully !!!');
                    } else {
                        return redirect()->back()->with('error_message', $this->data['title'].' Already Exists !!!');
                    }
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
            Role::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Role::find($id);
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
