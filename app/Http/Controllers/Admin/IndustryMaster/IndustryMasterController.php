<?php

namespace App\Http\Controllers\Admin\IndustryMaster;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\industry\IndustryRequest;
use App\Models\industry\IndustryMaster;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IndustryMasterController extends Controller
{
    public $data;

    public function __construct()
    {
        $this->data = [
            'title'             => 'Industry',
            'controller'        => 'IndustryMasterController',
            'controller_route'  => 'industry-master',
            'primary_key'       => 'im_id',
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['module']                 = $this->data;
        $title                          = $this->data['title'] . ' List';
        $page_name                      = 'industryMaster.list';
        $data['rows']                   = IndustryMaster::select('im_id', 'im_name')->where('status', '!=', 3)->orderBy($this->data['primary_key'], 'DESC')->get()->toDecodedJson();

        echo $this->admin_after_login_layout($title, $page_name, $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['module']                 = $this->data;
        $title                          = $this->data['title'] . ' Add';
        $page_name                      = 'industryMaster.add-edit';
        $data['row']                    = null;
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IndustryRequest $request)
    {
        try {
            // Start transaction
            DB::beginTransaction();

            IndustryMaster::insert([
                'im_name'         => $request->input('name'),
                'im_tag'          => strtolower(str_replace([' ', '/', ' & '], '_', $request->input('name')))
            ]);

            DB::commit();

            return redirect(route('industry-master.index'))->with('success_message', $this->data['title'] . ' Inserted Successfully !!!');
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
        $page_name                      = 'industryMaster.add-edit';
        $data['row']                    = IndustryMaster::select('im_id', 'im_name')->where($this->data['primary_key'], '=', $id)->first();
        echo $this->admin_after_login_layout($title, $page_name, $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IndustryRequest $request, string $id)
    {
        try {
            // Start transaction
            DB::beginTransaction();
            $id = Helper::decoded($id);
            IndustryMaster::where('im_id', $id)
                ->update([
                    'im_name' => $request->input('name'),
                    'im_tag'  => strtolower(str_replace([' ', '/', ' & '], '_', $request->input('name'))),
                ]);

            DB::commit();

            return redirect(route('industry-master.index'))->with('success_message', $this->data['title'] . ' Updated Successfully !!!');
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
        $id                             = Helper::decoded($id);

        try {
            // Start transaction
            DB::beginTransaction();
            $fields = [
                'status' => 3
            ];
            IndustryMaster::where($this->data['primary_key'], '=', $id)->update($fields);
            DB::commit();
            return redirect(route('industry-master.index'))->with('success_message', $this->data['title'] . ' Deleted Successfully !!!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return redirect()->back()
                ->with('error_message', 'Something went wrong! Please try again.');
        }
    }


}
