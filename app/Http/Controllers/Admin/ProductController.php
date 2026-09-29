<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Admin;
use App\Models\ProductCategories;
use App\Models\Size;
use App\Models\Unit;
use Auth;
use Session;
use Helper;
use Hash;

class ProductController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Product',
            'controller'        => 'ProductController',
            'controller_route'  => 'product',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'product.list';
            $data['rows']                   = Product::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            $sessionData = Auth::guard('admin')->user();
            $data['admin'] = Admin::where('id', '=', $sessionData->id)->orderBy('id', 'DESC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            $data['product_cat']      = ProductCategories::where('status', '=', 1)->orderBy('category_name', 'ASC')->get();
            $data['unit']                    = Unit::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['size']                    = Size::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'name'           => 'required',                                       
                ];                
                if($this->validate($request, $rules)){
                    $checkValue = Product::where('name', '=', $postData['name'])->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        /* page image */
                        $imageFile      = $request->file('product_image');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('product_image', $imageName, 'product', 'image');
                            if($uploadedFile['status']){
                                $product_image = $uploadedFile['newFilename'];
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $product_image = '';
                        }
                        /* page image */
                        $fields = [
                            'name'                  => $postData['name'],
                            'product_slug'          => Helper::clean($postData['name']),
                            'product_image'         => $product_image,
                            'short_desc'            => $postData['short_desc'],
                            'category_id'           => $postData['product_category'],
                            'markup_price'          => $postData['markup_price'],
                            'retail_price'          => $postData['retail_price'],
                            'unit_id'               => $postData['unit_id'],
                            'size_id'               => $postData['size_id'],
                            'created_by'            => $sessionData->id,
                            'company_id'            => $sessionData->company_id,
                        ];
                        Product::insert($fields);
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
            $page_name                      = 'product.add-edit';
            $data['row']                    = [];
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $data['product_cat']            = ProductCategories::where('status', '=', 1)->orderBy('category_name', 'ASC')->get();
            $data['unit']                    = Unit::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['size']                    = Size::where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'product.add-edit';
            $data['row']                    = Product::where($this->data['primary_key'], '=', $id)->first();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'name'           => 'required',                    
                ];
                if($this->validate($request, $rules)){
                    $checkValue = Product::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                    if($checkValue <= 0){
                        $sessionData = Auth::guard('admin')->user();
                        /* page image */
                        $imageFile      = $request->file('product_image');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('product_image', $imageName, 'product', 'image');
                            if($uploadedFile['status']){
                                $product_image = $uploadedFile['newFilename'];
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $product_image = $data['row']->product_image;
                        }
                        /* page image */
                        $fields = [
                            'name'                  => $postData['name'],
                            'product_slug'          => Helper::clean($postData['name']),
                            'product_image'         => $product_image,
                            'short_desc'            => $postData['short_desc'],
                            'category_id'           => $postData['product_category'],
                            'markup_price'          => $postData['markup_price'],
                            'retail_price'          => $postData['retail_price'],
                            'unit_id'               => $postData['unit_id'],
                            'size_id'               => $postData['size_id'],
                            'company_id'            => $sessionData->company_id,
                            'updated_by'            => $sessionData->id,
                            'updated_at'            => date('Y-m-d H:i:s')
                        ];
                        Product::where($this->data['primary_key'], '=', $id)->update($fields);
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
            Product::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect("admin/" . $this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Product::find($id);
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
