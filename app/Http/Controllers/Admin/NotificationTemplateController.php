<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\NotificationTemplate;
use App\Models\Admin;
use Auth;
use Session;
use Helper;
use Hash;

class NotificationTemplateController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Notification Templates',
            'controller'        => 'NotificationTemplateController',
            'controller_route'  => 'notification-templates',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'notification-template.list';
            $data['rows']                   = NotificationTemplate::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
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
                    'type'                  => 'required',
                    'title'                 => 'required',
                    'description'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = NotificationTemplate::where('title', '=', $postData['title'])->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        $fields = [
                            'type'              => $postData['type'],
                            'title'             => $postData['title'],
                            'description'       => $postData['description'],
                            'created_by'        => $sessionData->id,
                            'updated_by'        => $sessionData->id,
                            'company_id'        => $sessionData->company_id,
                        ];
                        NotificationTemplate::insert($fields);
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
            $page_name                      = 'notification-template.add-edit';
            $data['row']                    = [];
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'notification-template.add-edit';
            $data['row']                    = NotificationTemplate::where($this->data['primary_key'], '=', $id)->first();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'type'                  => 'required',
                    'title'                 => 'required',
                    'description'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = NotificationTemplate::where('description', '=', $postData['description'])->where('id', '!=', $id)->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        $fields = [
                            'type'              => $postData['type'],
                            'title'             => $postData['title'],
                            'description'       => $postData['description'],
                            'created_by'        => $sessionData->id,
                            'updated_by'        => $sessionData->id,
                            'company_id'        => $sessionData->company_id,
                        ];
                        NotificationTemplate::where($this->data['primary_key'], '=', $id)->update($fields);
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
            NotificationTemplate::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = NotificationTemplate::find($id);
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
