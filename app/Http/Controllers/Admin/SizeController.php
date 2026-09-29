<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use Auth;
use Session;
use Helper;
use Hash;
use App\Models\Size;
use App\Models\Unit;

class SizeController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Size',
            'controller'        => 'SizeController',
            'controller_route'  => 'size',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'size.list';
            $data['rows']                   = Size::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            if($request->isMethod('post')){
                $sessionData = Auth::guard('admin')->user();
                $postData = $request->all();
                $rules = [
                    'name'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = Size::where('name', '=', $postData['name'])->count();
                    if($checkValue <= 0){
                        $fields = [
                            'name'                  => $postData['name'],
                            'unit_id'               => $postData['unit_id'],
                            'created_by'            => $sessionData->id,
                            'company_id'            => $sessionData->company_id
                        ];
                        Size::insert($fields);
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
            $page_name                      = 'size.add-edit';
            $data['row']                    = [];
            $data['unit']                    = Unit::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'size.add-edit';
            $data['row']                    = Size::where($this->data['primary_key'], '=', $id)->first();
            $data['unit']                    = Unit::where('status', '=', 1)->orderBy('name', 'ASC')->get();

            if($request->isMethod('post')){
                $sessionData = Auth::guard('admin')->user();
                $postData = $request->all();
                $rules = [
                    'name'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = Size::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                    if($checkValue <= 0){
                        $fields = [
                            'name'                  => $postData['name'],
                            'unit_id'               => $postData['unit_id'],
                            'company_id'            => $sessionData->company_id,
                            'updated_by'            => $sessionData->id,
                            'updated_at'            => date('Y-m-d H:i:s')
                        ];
                        Size::where($this->data['primary_key'], '=', $id)->update($fields);
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
            Size::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Size::find($id);
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
