<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Admin;
use App\Models\User;
use App\Models\Enquiry;
use Auth;
use Session;
use Helper;
use Hash;

class EnquiryController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Contact Enquiry',
            'controller'        => 'EnquiryController',
            'controller_route'  => 'enquiry',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = 'Manage ' . $this->data['title'];
            $page_name                      = 'enquiry.list';
            $data['rows']                   = Enquiry::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            $sessionData = Auth::guard('admin')->user();
            $data['admin'] = Admin::where('id', '=', $sessionData->id)->orderBy('id', 'DESC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* details */
        public function details($id){
            $id                             = Helper::decoded($id);
            $data['module']                 = $this->data;
            $title                          = 'Manage ' . $this->data['title'];
            $page_name                      = 'enquiry.details';
            $data['row']                    = Enquiry::where('status', '!=', 3)->where('id', '=', $id)->first();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* details */
    /* delete */
        public function delete(Request $request, $id){
            $id                             = Helper::decoded($id);
            $fields = [
                'status'             => 3
            ];
            Enquiry::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
}
