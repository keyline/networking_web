<?php

namespace App\Http\Controllers\Admin\BusinessType;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\business\BusinessRequest;
use App\Models\business\BusinessMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessTypeController extends Controller
{
    public $data;

    public function __construct()
    {
        $this->data = [
            'title'             => 'Business',
            'controller'        => 'BusinessTypeController',
            'controller_route'  => 'business-master',
            'primary_key'       => 'btm_id',
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['module']                 = $this->data;
        $title                          = $this->data['title'] . ' List';
        $page_name                      = 'businessMaster.list';
        $data['rows']                   = BusinessMaster::select('btm_id', 'btm_name')->where('status', '!=', 3)->orderBy($this->data['primary_key'], 'DESC')->get()->toDecodedJson();

        echo $this->admin_after_login_layout($title, $page_name, $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['module']                 = $this->data;
        $title                          = $this->data['title'] . ' Add';
        $page_name                      = 'businessMaster.add-edit';
        $data['row']                    = null;
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BusinessRequest $request)
    {
        try {
            // Start transaction
            DB::beginTransaction();

            businessMaster::insert([
                'btm_name'         => $request->input('name')
            ]);

            DB::commit();

            return redirect(route('business-master.index'))->with('success_message', $this->data['title'] . ' Inserted Successfully !!!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return redirect()->back()
                ->with('error_message', 'Something went wrong! Please try again.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data['module']                 = $this->data;
        $id                             = Helper::decoded($id);
        $title                          = $this->data['title'] . ' Update';
        $page_name                      = 'businessMaster.add-edit';
        $data['row']                    = businessMaster::select('btm_id', 'btm_name')->where($this->data['primary_key'], '=', $id)->first();
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BusinessRequest $request, string $id)
    {
        try {
            // Start transaction
            DB::beginTransaction();
            $id = Helper::decoded($id);
            businessMaster::where('btm_id', $id)
                ->update([
                    'btm_name' => $request->input('name')
                ]);

            DB::commit();

            return redirect(route('business-master.index'))->with('success_message', $this->data['title'] . ' Updated Successfully !!!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return redirect()->back()
                ->with('error_message', 'Something went wrong! Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $id = Helper::decoded($id);

        try {
            // Start transaction
            DB::beginTransaction();
            $fields = [
                'status' => 3
            ];
            businessMaster::where($this->data['primary_key'], '=', $id)->update($fields);
            DB::commit();
            return redirect(route('business-master.index'))->with('success_message', $this->data['title'] . ' Deleted Successfully !!!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return redirect()->back()
                ->with('error_message', 'Something went wrong! Please try again.');
        }
    }
}
