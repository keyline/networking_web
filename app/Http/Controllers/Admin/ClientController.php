<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessCategoryMaster;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\Companies;
use App\Models\Client;
use App\Models\ClientCheckIn;
use App\Models\ClientOrder;
use App\Models\ClientType;
use App\Models\Companies\CategoryToCompany;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\Companies\UserToCompanies;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Models\District;
use App\Models\Role;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use App\Models\User\UserTypeMaster;
use App\Models\UserType;
use Auth;
use Exception;
use Session;
use Helper;
use Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->data = array(
            'title'             => 'User : ',
            'controller'        => 'ClientController',
            'controller_route'  => 'clients',
            'primary_key'       => 'um_id',
        );
    }

    public function businessList()
    {
        $data['slug']                   = 'business';
        $data['rows'] = [];
        $data['module']                 = $this->data;
        $title                          = 'Business List';
        $page_name                      = 'client.businessList';
        $data['client_type']            = null;
        $list                   = CompaniesMaster::with(['details', 'owner', 'owner.userDtl'])->get()->toDecodedJson();

        foreach ($list  as $row) {
            if (!is_null($row->owner)) {
                $tagList = [];
                $category = CategoryToCompany::select('ctc_bcm_id')->with('category:bcm_id,name')->where('ctc_cmp_id', $row->cmp_id)->get()->toArray();

                $userDetails = UserDetails::select('ud_first_name')->where('ud_um_id', $row->owner->ucm_um_id)->first();



                if (count($category)) {
                    foreach ($category as $tag) {
                        if (count($tag['category'])) {
                            $tagList[] = $tag['category']['name'];
                        }
                    }
                }





                $data['rows'][] = [
                    'cmp_id' => $row->cmp_id,
                    'name' => $row->details->cmpd_name ?? '',
                    'description' => $row->details->cmpd_description ?? '',
                    'email' => $row->details->cmpd_email ?? '',
                    'phone' => $row->details->cmpd_phone ?? '',
                    'address1' => $row->details->cmpd_address1 ?? '',
                    'district' =>  $row->details->cmpd_district ?? '',
                    'pincode' =>  $row->details->cmpd_pincode ?? '',
                    'license_start_datetime' =>  date('d-m-Y', strtotime($row->details->cmpd_license_start_datetime)),
                    'license_end_datetime' => date('d-m-Y', strtotime($row->details->cmpd_license_end_datetime)),
                    'last_renewal_date' => date('d-m-Y', strtotime($row->details->cmpd_last_renewal_date)),
                    'status' =>  $row->details->cmpd_status,
                    'owner_name' =>  $userDetails->ud_first_name ?? '',
                    'tagCount' => implode(', ', $tagList),
                ];
            }
        }

        echo $this->admin_after_login_layout($title, $page_name, $data);
    }


    /* edit */
    public function businessEdit(Request $request, $slug = 'business', $id = 0, $uid = null)
    {

        $data['module']                 = $this->data;
        $data['slug']                   = $slug;
        $id                             = $id ? Helper::decoded($id) : $id;
        $title                          = ucfirst($data['slug']) . $id ? 'Update' : 'Add';

        $page_name                      = 'client.business-add-edit';
        $data['category']               = BusinessCategoryMaster::select('bcm_id', 'name')->get()->toDecodedJson();

        $data['row']                    = CompaniesDetail::where('cmpd_cmp_id', $id)->first();

        $catToCom                       = CategoryToCompany::where('ctc_cmp_id', $id)->first();



        $data['selectedCategory'] = $catToCom['ctc_bcm_id'] ?? '';
        if ($request->isMethod('post')) {
            $postData = $request->all();

            $validator = Validator::make($postData, [
                'id' => 'required|integer',
                'category_id' => 'required',
                'regn_no' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'email' => 'nullable|email|max:255',
                'alternate_email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|regex:/^\d{10}$/',
                'whatsapp_no' => 'nullable|string|regex:/^\d{10}$/',
                'address1' => 'nullable|string|max:255',
                'address2' => 'nullable|string|max:255',
                'address3' => 'nullable|string|max:255',
                'estd_year' => 'nullable|integer|digits:4|min:1900|max:' . date('Y'),
                // 'district' => 'nullable|string|max:255',
                "country" => 'required|integer',
                "state" => 'required|integer',
                "district" => 'required|integer',

                'pincode' => 'nullable|string|regex:/^\d{6}$/',
                'license_start' => 'required|date',
                'license_end' => 'required|date',
                'license_ref' => 'nullable|string|max:255',
                'renewal_date' => 'required|date',
                'logo' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return back()->with('errors', $validator->errors());
            } else {

                $details_id = $request->input('id');

                /* profile image */
                $imageFile      = $request->file('logo');
                if ($imageFile != '') {
                    $imageName      = $imageFile->getClientOriginalName();
                    $uploadedFile   = $this->upload_single_file('logo', $imageName, 'company', 'image');
                    if ($uploadedFile['status']) {
                        $logo = $uploadedFile['newFilename'];
                    } else {
                        return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                    }
                } else {
                    $logo = $data['row']->cmpd_logo ?? null;
                }
                /* profile image */


                $fields =  [
                    'cmpd_company_regn_no' => $postData['regn_no'],
                    'cmpd_name' => $postData['name'],
                    'cmpd_description' => $postData['description'],
                    'cmpd_email' => $postData['email'],
                    'cmpd_alternate_email' => $postData['alternate_email'],
                    'cmpd_phone' => $postData['phone'],
                    'cmpd_whatsapp_no' => $postData['whatsapp_no'],
                    'cmpd_address1' => $postData['address1'],
                    'cmpd_address2' => $postData['address2'],
                    'cmpd_address3' => $postData['address3'],
                    'cmpd_estd_year' => $postData['estd_year'],
                    'cmpd_country' => $postData['country'],
                    'cmpd_state' => $postData['state'],
                    'cmpd_district' => $postData['district'],
                    'cmpd_pincode' => $postData['pincode'],
                    'cmpd_license_start_datetime' => $postData['license_start'],
                    'cmpd_license_end_datetime' => $postData['license_end'],
                    'cmpd_license_ref' => $postData['license_ref'],
                    'cmpd_last_renewal_date' => $postData['renewal_date'],
                    'cmpd_logo' => $logo,
                    'cmpd_updated_at' => date('Y-m-d H:i:s')
                ];

                try {
                    $cmpId = 0;
                    DB::beginTransaction();

                    if ($details_id) {
                        $companie = CompaniesDetail::where('cmpd_id', $details_id)->update($fields);
                        $cmpId = $id;
                    } else {
                        $cmpId = CompaniesMaster::insertGetId(['cmp_created_at' => date('Y-m-d 00:00:00')]);

                        UserToCompanies::insert([
                            'ucm_cmp_id' => $cmpId,
                            'ucm_um_id' => $uid
                        ]);

                        $fields['cmpd_cmp_id'] = $cmpId;
                        $companie = CompaniesDetail::insertGetId($fields);
                    }

                    if ($companie) {
                        if ($details_id) {
                            CategoryToCompany::where('ctc_cmp_id', $cmpId)->delete();
                        }
                        CategoryToCompany::insert([
                            'ctc_bcm_id' => $postData['category_id'],
                            'ctc_cmp_id' => $id ?? $companie,
                        ]);
                        DB::commit();
                        // Successful update

                    } else {
                        // Update didn't affect any rows (e.g., invalid ID or no changes)
                        throw new Exception("Update failed: no rows affected.");
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    // Handle exceptions or errors
                    dd($e->getMessage());
                }



                return redirect("admin/" . $this->data['controller_route'] . "/" . $data['slug'] . "/list")->with('success_message', $this->data['title'] . "/" . $data['slug'] . 'data save successful.');
            }
        }
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* edit */




    /* list */
    public function list($slug)
    {

        $data['slug']                   = $slug;
        $data['module']                 = $this->data;
        $title                          = ucfirst($data['slug']) . ' List';
        $page_name                      = 'client.list';
        // $sessionType                    = Session::get('type');
        $data['client_type']            = UserTypeMaster::select('utm_id', 'utm_name')->where('utm_status', 1)->where('utm_name', '=', $data['slug'])->orderBy('utm_id', 'ASC')->first();

        $UserMaster = UserMaster::with('userDetail');

        // Conditionally add `withCount` for `companiesMap` if `client_type->utm_id` is 2
        if ($data['client_type']->utm_id == 2) {
            $UserMaster->withCount('companiesMap');
            $UserMaster->with('companiesMap.companie:cmpd_cmp_id,cmpd_name');
        }

        // Apply filters and retrieve results
        $data['rows'] = $UserMaster
            ->where('um_status', '!=', 3)
            ->where('um_utm_id', $data['client_type']->utm_id)
            ->orderByDesc('um_id')
            ->get()->toArray();


        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* list */
    /* add */
    public function add(Request $request, $slug)
    {
        $data['module']             = $this->data;
        $data['slug']               = $slug;
        $data['client_type']        = ClientType::where('status', '!=', 3)->where('slug', '=', $data['slug'])->orderBy('id', 'ASC')->first();
        $data['districts']          = District::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

        if ($request->isMethod('post')) {
            $postData = $request->all();
            $rules = [
                'name'                  => 'required',
                'email'                 => 'required',
                'phone'                 => 'required',
                'whatsapp_no'           => 'required',
                'address'               => 'required',
                'district_id'           => 'required',
            ];
            if ($this->validate($request, $rules)) {
                $checkValue = Client::where('name', '=', $postData['name'])->count();
                if ($checkValue <= 0) {
                    $sessionData    = Auth::guard('admin')->user();
                    $prefix         = (($data['client_type']) ? $data['client_type']->prefix : '');
                    /* profile image */
                    $imageFile      = $request->file('profile_image');
                    if ($imageFile != '') {
                        $imageName      = $imageFile->getClientOriginalName();
                        $uploadedFile   = $this->upload_single_file('profile_image', $imageName, 'client', 'image');
                        if ($uploadedFile['status']) {
                            $profile_image = $uploadedFile['newFilename'];
                        } else {
                            return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                        }
                    } else {
                        $profile_image = '';
                    }
                    /* profile image */
                    /* generate client no  */
                    $getLastEnquiry = Client::orderBy('id', 'DESC')->first();
                    if ($getLastEnquiry) {
                        $sl_no                  = $getLastEnquiry->sl_no;
                        $next_sl_no             = $sl_no + 1;
                        $next_sl_no_string      = str_pad($next_sl_no, 5, 0, STR_PAD_LEFT);
                        $client_no            = $prefix . $next_sl_no_string;
                    } else {
                        $next_sl_no             = 1;
                        $next_sl_no_string      = str_pad($next_sl_no, 5, 0, STR_PAD_LEFT);
                        $client_no            = $prefix . $next_sl_no_string;
                    }
                    /* generate client no */
                    $fields = [
                        'company_id'                => session('company_id'),
                        'client_type_id'            => $data['client_type']->id,
                        'sl_no'                     => $next_sl_no,
                        'client_no'                 => $client_no,
                        'name'                      => $postData['name'],
                        'email'                     => $postData['email'],
                        'alt_email'                 => $postData['alt_email'],
                        'phone'                     => $postData['phone'],
                        'whatsapp_no'               => $postData['whatsapp_no'],
                        'short_bio'                 => $postData['short_bio'],
                        'district_id'               => $postData['district_id'],
                        'address'                   => $postData['address'],
                        'country'                   => $postData['country'],
                        'state'                     => $postData['state'],
                        'city'                      => $postData['city'],
                        'locality'                  => $postData['locality'],
                        'street_no'                 => $postData['street_no'],
                        'zipcode'                   => $postData['zipcode'],
                        'latitude'                  => $postData['latitude'],
                        'longitude'                 => $postData['longitude'],
                        'profile_image'             => $profile_image,
                        'created_by'                => $sessionData->id,
                    ];
                    // Helper::pr($fields);
                    Client::insert($fields);
                    return redirect("admin/" . $this->data['controller_route'] . "/" . $data['slug'] . "/list")->with('success_message', $this->data['title'] . ' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', $this->data['title'] . ' Already Exists !!!');
                }
            } else {
                return redirect()->back()->with('error_message', 'All Fields Required !!!');
            }
        }
        $data['module']                 = $this->data;
        $title                          = ucfirst($data['slug']) . ' Add';
        $page_name                      = 'client.add-edit';
        $data['row']                    = [];
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* add */
    /* edit */
    public function edit(Request $request, $slug, $id,)
    {
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;
        $id                             = Helper::decoded($id);
        $title                          = ucfirst($data['slug']) . ' Update';
        $page_name                      = 'client.add-edit';
        $data['row']                    = Client::where($this->data['primary_key'], '=', $id)->first();
        $data['client_type']            = ClientType::where('status', '!=', 3)->where('slug', '=', $data['slug'])->orderBy('id', 'ASC')->first();
        $data['districts']              = District::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

        if ($request->isMethod('post')) {
            $postData = $request->all();
            $rules = [
                'name'                  => 'required',
                'email'                 => 'required',
                'phone'                 => 'required',
                'whatsapp_no'           => 'required',
                'address'               => 'required',
                'district_id'           => 'required',
            ];
            if ($this->validate($request, $rules)) {
                $checkValue = Client::where('name', '=', $postData['name'])->where('id', '!=', $id)->count();
                if ($checkValue <= 0) {
                    $sessionData = Auth::guard('admin')->user();
                    /* profile image */
                    $imageFile      = $request->file('profile_image');
                    if ($imageFile != '') {
                        $imageName      = $imageFile->getClientOriginalName();
                        $uploadedFile   = $this->upload_single_file('profile_image', $imageName, 'client', 'image');
                        if ($uploadedFile['status']) {
                            $profile_image = $uploadedFile['newFilename'];
                        } else {
                            return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                        }
                    } else {
                        $profile_image = $data['row']->profile_image;
                    }
                    /* profile image */
                    $fields = [
                        'company_id'                => session('company_id'),
                        'client_type_id'            => $data['client_type']->id,
                        'name'                      => $postData['name'],
                        'email'                     => $postData['email'],
                        'alt_email'                 => $postData['alt_email'],
                        'phone'                     => $postData['phone'],
                        'whatsapp_no'               => $postData['whatsapp_no'],
                        'short_bio'                 => $postData['short_bio'],
                        'district_id'               => $postData['district_id'],
                        'address'                   => $postData['address'],
                        'country'                   => $postData['country'],
                        'state'                     => $postData['state'],
                        'city'                      => $postData['city'],
                        'locality'                  => $postData['locality'],
                        'street_no'                 => $postData['street_no'],
                        'zipcode'                   => $postData['zipcode'],
                        'latitude'                  => $postData['latitude'],
                        'longitude'                 => $postData['longitude'],
                        'profile_image'             => $profile_image,
                        'created_by'                => $sessionData->id,
                        'updated_by'                => $sessionData->id,
                        'updated_at'                => date('Y-m-d H:i:s')
                    ];
                    // Helper::pr($fields);
                    Client::where($this->data['primary_key'], '=', $id)->update($fields);
                    return redirect("admin/" . $this->data['controller_route'] . "/" . $data['slug'] . "/list")->with('success_message', $this->data['title'] . "/" . $data['slug'] . ' Updated Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', $this->data['title'] . ' Already Exists !!!');
                }
            } else {
                return redirect()->back()->with('error_message', 'All Fields Required !!!');
            }
        }
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    /* edit */
    /* delete */
    public function delete(Request $request, $slug, $id)
    {
        $id                             = Helper::decoded($id);
        $fields = [
            'status'             => 3
        ];
        Client::where($this->data['primary_key'], '=', $id)->update($fields);
        return redirect("admin/" . $this->data['controller_route'] . "/" . $slug . "/list")->with('success_message', ucfirst($slug) . ' Deleted Successfully !!!');
    }
    /* delete */
    /* change status */
    public function change_status(Request $request, $slug, $id)
    {
        $id                             = Helper::decoded($id);

        $model                          = UserMaster::select('um_status')->find($id);

        $status = (int) $model->um_status ? 0 : 1;

        $msg = $status ? 'Activated' : 'Deactivated';

        UserMaster::where($this->data['primary_key'], $id)->update(['um_status' => $status]);

        return redirect("admin/" . $this->data['controller_route'] . "/" . $slug . "/list")->with('success_message', ucfirst($slug) . ' ' . $msg . ' Successfully !!!');
    }
    /* change status */
    // view details
    public function viewDetails($slug, $id)
    {
        // \DB::enableQueryLog();
        // dd($id);
        $id                             = Helper::decoded($id);
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;
        $page_name                      = 'client.view_details';
        // $data['row']                    = Client::where('status', '!=', 3)->where('id', '=', $id)->orderBy('id', 'DESC')->first();
        $data['row']                    =  UserMaster::select('um_id', 'um_utm_id', 'um_user_name', 'um_mobile_no', 'um_email_id', 'um_created_at')
            ->with(['userType:utm_id,utm_name', 'userDetail'])
            ->where('um_id', '=', $id)->first();

        $data['business']               = $data['row']->um_utm_id == 2 ? UserToCompanies::with(['details'])->where('ucm_um_id', $id)->get() : null;

        $firstName = $data['row']?->userDetail?->ud_first_name ?? '';
        $lastName = $data['row']?->userDetail?->ud_last_name ?? '';


        $title                          = $this->data['title'] . ' View Details : ' . $firstName . ' ' . $lastName;




        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    // view details
    public function viewOrderDetails($slug, $id)
    {
        // dd($id);
        $id                             = Helper::decoded($id);
        $data['module']                 = $this->data;
        $data['slug']                   = $slug;
        $page_name                      = 'client.view_order_details';
        $rows                           = DB::table('client_order_details')
            ->join('client_orders', 'client_orders.id', '=', 'client_order_details.order_id')
            ->join('products', 'products.id', '=', 'client_order_details.product_id')
            ->join('units', 'units.id', '=', 'client_order_details.case_unit')
            ->join('admins as created_by_admins', 'created_by_admins.id', '=', 'client_order_details.created_by')
            ->join('admins as updated_by_admins', 'updated_by_admins.id', '=', 'client_order_details.updated_by')
            ->select(
                'client_order_details.*',
                'client_orders.order_no',
                'products.name as product_name',
                'products.short_desc as product_short_desc',
                'sizes.name as size_name',
                'units.name as unit_name',
                'created_by_admins.name as created_by',
                'updated_by_admins.name as updated_by'
            )
            ->where('client_order_details.order_id', $id)
            ->get();

        $data['row']                    = $rows;
        $data['order_details']    = ClientOrder::where('status', '=', 1)->where('id', '=', $id)->first();
        $data['client_details']    = Client::where('status', '=', 1)->where('id', '=', $data['order_details']->client_id)->first();
        $data['employee_details']    = Employees::where('status', '=', 1)->where('id', '=', $data['order_details']->employee_id)->first();
        $data['employee_types']    = EmployeeType::where('status', '=', 1)->where('id', '=', $data['order_details']->employee_type_id)->first();
        // Helper::pr($data['order_details'])  ;
        $title                          = $this->data['title'] . ' View Order Details : ' . (($data['order_details']) ? $data['order_details']->order_no : '');
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }
    public function clientwiseorderListRecords(Request $request)
    {
        // Retrieve query parameters
        $orderId = $request->query('orderId');
        $name = $request->query('name');

        // Ensure $orderId is present
        if (empty($orderId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order ID is required.'
            ], 400);
        }

        // Use Laravel's query builder for safe and efficient SQL generation
        $rows = DB::table('client_order_details')
            ->join('client_orders', 'client_orders.id', '=', 'client_order_details.order_id')
            ->join('products', 'products.id', '=', 'client_order_details.product_id')
            ->join('sizes', 'sizes.id', '=', 'client_order_details.size_id')
            ->join('units', 'units.id', '=', 'client_order_details.unit_id')
            ->join('admins as created_by_admins', 'created_by_admins.id', '=', 'client_order_details.created_by')
            ->join('admins as updated_by_admins', 'updated_by_admins.id', '=', 'client_order_details.updated_by')
            ->select(
                'client_order_details.*',
                'client_orders.order_no',
                'products.name as product_name',
                'sizes.name as size_name',
                'units.name as unit_name',
                'created_by_admins.name as created_by',
                'updated_by_admins.name as updated_by'
            )
            ->where('client_order_details.order_id', $orderId)
            ->get();

        // Start building the HTML response
        $html = '<div class="modal-header" style="justify-content: center;">
                    <h6 class="modal-title">Orders Details for <b><u>' . htmlspecialchars($name) . '</u></b></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="table-responsive table-card">
                            <table class="table general_table_style">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Order No</th>
                                        <th>Product Name</th>
                                        <th>Order Unit</th>
                                        <th>Order Qty</th>
                                        <th>Rate</th>
                                        <th>Subtotal</th>
                                        <th>Created Info</th>
                                        <th>Updated Info</th>
                                    </tr>
                                </thead>
                                <tbody>';

        // Add rows to the table
        if ($rows->isNotEmpty()) {
            $sl = 1;
            foreach ($rows as $record) {
                $html .= '<tr>
                            <td>' . $sl++ . '</td>
                            <td>' . htmlspecialchars($record->order_no) . '</td>
                            <td>' . htmlspecialchars($record->product_name) . '</td>
                            <td>' . htmlspecialchars($record->size_name) . ' ' . htmlspecialchars($record->unit_name) . '</td>
                            <td>' . htmlspecialchars($record->qty) . '</td>
                            <td>' . htmlspecialchars($record->rate) . '</td>
                            <td>' . htmlspecialchars($record->subtotal) . '</td>
                            <td>' . htmlspecialchars($record->created_by) . '<br>' . date('M d Y h:i A', strtotime($record->created_at)) . '</td>
                            <td>' . htmlspecialchars($record->updated_by) . '<br>' . date('M d Y h:i A', strtotime($record->updated_at)) . '</td>
                        </tr>';
            }
        } else {
            $html .= '<tr>
                        <td colspan="9" class="text-center">No records found for this order.</td>
                    </tr>';
        }

        $html .= '</tbody>
                        </table>
                    </div>
                </div>
            </div>';

        // Return the HTML response
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }
}
