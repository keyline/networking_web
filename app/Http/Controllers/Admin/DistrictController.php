<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Country;
use App\Models\State;
use App\Models\District;
use Auth;
use Session;
use Helper;
use Hash;
use DB;
class DistrictController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'District',
            'controller'        => 'DistrictController',
            'controller_route'  => 'district',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'district.list';
            $data['rows']                   = DB::table('districts')
                                                ->join('countries', 'countries.id', '=', 'districts.country_id')
                                                ->join('states', 'states.id', '=', 'districts.state_id')
                                                ->select('districts.*', 'states.name as state_name', 'countries.name as country_name')
                                                ->where('districts.status', '!=', 3)
                                                // ->where('states.country_id', '=', 101)
                                                ->orderBy('districts.id', 'DESC')
                                                ->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'country_id'                => 'required',
                    'state_id'                  => 'required',
                    'name'                      => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = District::where('name', '=', $postData['name'])->count();
                    if($checkValue <= 0){
                        $fields = [
                            'country_id'            => $postData['country_id'],
                            'state_id'              => $postData['state_id'],
                            'name'                  => $postData['name'],
                            'status'                => $postData['status'],
                        ];
                        District::insert($fields);
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
            $page_name                      = 'district.add-edit';
            $data['row']                    = [];
            $data['countries']              = Country::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['states']                 = State::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'district.add-edit';
            $data['row']                    = District::where($this->data['primary_key'], '=', $id)->first();
            $data['countries']              = Country::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['states']                 = State::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'country_id'                => 'required',
                    'state_id'                  => 'required',
                    'name'                      => 'required',
                ];
                if($this->validate($request, $rules)){
                    $checkValue = District::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                    if($checkValue <= 0){
                        $fields = [
                            'country_id'            => $postData['country_id'],
                            'state_id'              => $postData['state_id'],
                            'name'                  => $postData['name'],
                            'status'                => $postData['status'],
                        ];
                        District::where($this->data['primary_key'], '=', $id)->update($fields);
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
            District::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = District::find($id);
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
