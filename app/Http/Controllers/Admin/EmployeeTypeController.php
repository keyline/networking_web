<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Admin;
use App\Models\EmployeeType;
use Auth;
use Session;
use Helper;
use Hash;

class EmployeeTypeController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Employee Type',
            'controller'        => 'EmployeeTypeController',
            'controller_route'  => 'employee-type',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'employee-type.list';
            $data['rows']                   = EmployeeType::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
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
                    'name'              => 'required',
                    'prefix'            => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = EmployeeType::where('name', '=', $postData['name'])->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        $fields = [
                            'name'          => strtoupper($postData['name']),
                            'slug'          => Helper::clean($postData['name']),
                            'prefix'        => strtoupper($postData['prefix']),
                            'level'         => $postData['level'],
                            'created_by'    => $sessionData->id,
                            'company_id'    => $sessionData->company_id,
                        ];
                        EmployeeType::insert($fields);
                        return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                    } else {
                        return redirect()->back()->with('error_message', $this->data['title'].' Already Exists !!!');
                    }
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'employee-type.add-edit';
            $data['row']                    = [];
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'employee-type.add-edit';
            $data['row']                    = EmployeeType::where($this->data['primary_key'], '=', $id)->first();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'name'              => 'required',
                    'prefix'            => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = EmployeeType::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        $fields = [
                            'name'          => strtoupper($postData['name']),
                            'slug'          => Helper::clean($postData['name']),
                            'prefix'        => strtoupper($postData['prefix']),
                            'level'         => $postData['level'],
                            'company_id'    => $sessionData->company_id,
                            'updated_by'    => $sessionData->id,
                            'updated_at'    => date('Y-m-d H:i:s')
                        ];
                        EmployeeType::where($this->data['primary_key'], '=', $id)->update($fields);
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
            EmployeeType::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = EmployeeType::find($id);
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
