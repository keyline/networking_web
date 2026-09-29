<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Companies;
use App\Models\ProductCategories;
use App\Models\Admin;
use Auth;
use Session;
use Helper;
use Hash;

class CompaniesController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Companies',
            'controller'        => 'CompaniesController',
            'controller_route'  => 'companies',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'companies.list';
            $data['rows']                   = Companies::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
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
                    'address'        => 'required',
                    'phone'          => 'required',
                    'email'          => 'required',                                       
                ];                
                if($this->validate($request, $rules)){
                    $checkValue = Companies::where('name', '=', $postData['name'])->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        /* company logo */
                        $imageFile      = $request->file('logo');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('logo', $imageName, '', 'image');
                            if($uploadedFile['status']){
                                $logo = $uploadedFile['newFilename'];
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $logo = '';
                        }
                        /* company logo */
                        /* generate company no */  
                            $currentMonth   = date('m');
                            $currentYear    = date('y');                          
                            $getLastEnquiry = Companies::orderBy('id', 'DESC')->first();
                            if($getLastEnquiry){
                                $sl_no              = $getLastEnquiry->sl_no;
                                $next_sl_no         = $sl_no + 1;
                                $next_sl_no_string  = str_pad($next_sl_no, 5, 0, STR_PAD_LEFT);
                                $company_no         = 'ENERGIC-'.$currentMonth.$currentYear.'-'.$next_sl_no_string;
                            } else {
                                $next_sl_no         = 1;
                                $next_sl_no_string  = str_pad($next_sl_no, 5, 0, STR_PAD_LEFT);
                                $company_no         = 'ENERGIC-'.$currentMonth.$currentYear.'-'.$next_sl_no_string;
                            }
                        /* generate company no */
                        $fields = [
                            'sl_no'                 => $next_sl_no,
                            'company_no'            => $company_no,
                            'name'                  => $postData['name'],
                            'email'                  => $postData['email'],
                            'alternate_email'                  => $postData['alternate_email'],
                            'phone'                  => $postData['phone'],
                            'whatsapp_no'                  => $postData['whatsapp_no'],
                            'last_renewal_date'                  => $postData['last_renewal_date'],                                                       
                            'logo'         => $logo,
                            'address'            => $postData['address'],
                            'start_date'           => $postData['start_date'],
                            'end_date'          => $postData['end_date'],
                            'license_no'          => $postData['license_no'],
                            'created_by'            => $sessionData->id,
                        ];                        
                        // dd($fields);
                        
                        Companies::insert($fields);                        
                        $company = Companies::orderBy('id', 'DESC')->first();                                  
                        $company_id = $company ? $company->id : null;
                        $fields2 = [
                            'name'                  => $postData['name'],
                            'company_id'               =>  $company_id ,
                            'type'                  => 'ca',
                            'mobile'                  => $postData['phone'],
                            'email'                  => $postData['username'],
                            'password'                 => Hash::make($postData['password']), 
                            'image'                     => $logo,
                            'created_by'            => $sessionData->id,                            
                        ];
                        // dd($fields2);
                        Admin::insert($fields2);
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
            $page_name                      = 'companies.add-edit';
            $data['row']                    = [];
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $data['product_cat']            = ProductCategories::where('status', '=', 1)->orderBy('category_name', 'ASC')->get();
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'companies.add-edit';
            $data['row']                    = Companies::where($this->data['primary_key'], '=', $id)->first();                       

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'name'           => 'required',
                    'address'        => 'required',
                    'phone'          => 'required',
                    'email'          => 'required',                    
                ];
                if($this->validate($request, $rules)){
                    $checkValue = Companies::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        /* page image */
                        $imageFile      = $request->file('logo');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('logo', $imageName, 'Companies', 'image');
                            if($uploadedFile['status']){
                                $logo = $uploadedFile['newFilename'];
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $logo = $data['row']->logo;
                        }
                        /* page image */
                        $fields = [
                            'name'                  => $postData['name'],                            
                            'logo'         => $logo,
                            'address'            => $postData['address'],
                            'start_date'           => $postData['start_date'],
                            'end_date'          => $postData['end_date'],
                            'license_no'          => $postData['license_no'],
                            'updated_by'            => $sessionData->id,
                            'updated_at'            => date('Y-m-d H:i:s')
                        ];
                        Companies::where($this->data['primary_key'], '=', $id)->update($fields);
                        $company = Companies::where('id', '=', $id)->first();  // Retrieve the company record  
                        //  dd($company) ;   
                        $company_id = $company ? $company->id : null;
                        $admin = Admin::where('company_id', '=', $company_id)->first(); 
                        // dd($admin);   
                        if($postData['password'] != ''){
                            $fields2 = [
                                'name'                  => $postData['name'],
                                'company_id'            =>  $company_id ,
                                'type'                  => 'ca',
                                'mobile'                => $postData['phone'],
                                'email'                 => $postData['username'],
                                'password'              => Hash::make($postData['password']), 
                                'image'                 => $logo,
                                'updated_by'            => $sessionData->id,   
                                'updated_at'            => date('Y-m-d H:i:s')       
                            ];
                        } else {
                            $fields2 = [
                                'name'                  => $postData['name'],
                                'company_id'            =>  $company_id ,
                                'type'                  => 'ca',
                                'mobile'                => $postData['phone'],
                                'email'                 => $postData['username'],                            
                                'image'                 => $logo,
                                'updated_by'            => $sessionData->id, 
                                'updated_at'            => date('Y-m-d H:i:s')         
                            ];
                        }                                                              
                        // dd($fields2);
                        Admin::where('id', '=', $admin->id)->update($fields2);
                        
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
            Companies::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Companies::find($id);
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
