<?php
use App\Helpers\Helper;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Models\Role;
use App\Models\District;
use Illuminate\Support\Facades\DB;
$controllerRoute = $module['controller_route'];
?>
<div class="pagetitle">
  <h1><?=$page_header?></h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Home</a></li>
      <li class="breadcrumb-item active"><?=$page_header?></li>
    </ol>
  </nav>
</div><!-- End Page Title -->
<section class="section">
  <div class="row">
    <div class="col-xl-12">
      @if(session('success_message'))
        <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide" role="alert">
          {{ session('success_message') }}
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if(session('error_message'))
        <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide" role="alert">
          {{ session('error_message') }}
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
    </div>
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <?php if($slug != 'all'){?>
            <h5 class="card-title">
              <a href="<?=url('admin/' . $controllerRoute .'/'.$slug. '/add/')?>" class="btn btn-outline-success btn-sm">Add <?=ucfirst($slug)?></a>
            </h5>
          <?php }?>
          <div class="dt-responsive table-responsive">
            <table id="<?=((count($rows)>0)?'simpletable':'')?>" class="table table-striped table-bordered nowrap">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Employee No</th>
                  <th scope="col">Parent Employees</th>
                  <th scope="col">Name</th>
                  <th scope="col">Email</th>
                  <th scope="col">Mobile</th>
                  <th scope="col">Assigned Districts</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($rows)>0){ $sl=1; foreach($rows as $row){?>
                  <tr>
                    <th scope="row"><?=$sl++?></th>
                    <td>
                      <?=$row->employee_no?>
                      <?php if($slug == 'all'){?>
                        <br>
                        <span class="badge bg-success" style="font-size: 9px;"><?=$row->employee_type_name?></span>
                      <?php }?>
                    </td>
                    <td>
                      <ul>
                        <?php
                        $parent_id = json_decode($row->parent_id);
                        if(!empty($parent_id)){ for($d=0;$d<count($parent_id);$d++){
                          $getEmployee = DB::table('employees')
                                                        ->join('employee_types', 'employees.employee_type_id', '=', 'employee_types.id')
                                                        ->select('employees.name as employee_name', 'employee_types.prefix as employee_type_prefix')
                                                        ->where('employees.id', '=', $parent_id[$d])
                                                        ->first();
                        ?>
                          <li><b><?=(($getEmployee)?$getEmployee->employee_name . '</b> ('.$getEmployee->employee_type_prefix.')':'')?></li>
                        <?php } }?>
                      </ul>
                    </td>
                    <td><?=$row->name?></td>
                    <td><?=$row->email?></td>
                    <td><?=$row->phone?></td>
                    <td>
                      <ul>
                        <?php
                        $assign_districts = json_decode($row->assign_district);
                        if(!empty($assign_districts)){ for($d=0;$d<count($assign_districts);$d++){
                          $getDistrict = District::select('name')->where('id', '=', $assign_districts[$d])->first();
                        ?>
                          <li><?=(($getDistrict)?$getDistrict->name:'')?></li>
                        <?php } }?>
                      </ul>
                    </td>
                    <td>
                      <a href="<?=url('admin/' . $controllerRoute .'/'.$slug. '/edit/'.Helper::encoded($row->id))?>" class="btn btn-outline-primary btn-sm" title="Edit <?=$module['title']?>"><i class="fa fa-edit"></i></a>
                      <a href="<?=url('admin/' . $controllerRoute .'/'.$slug. '/delete/'.Helper::encoded($row->id))?>" class="btn btn-outline-danger btn-sm" title="Delete <?=$module['title']?>" onclick="return confirm('Do You Want To Delete This <?=$module['title']?>');"><i class="fa fa-trash"></i></a>
                      <?php if($row->status){?>
                        <a href="<?=url('admin/' . $controllerRoute .'/'.$slug. '/change-status/'.Helper::encoded($row->id))?>" class="btn btn-outline-success btn-sm" title="Activate <?=$module['title']?>"><i class="fa fa-check"></i></a>
                      <?php } else {?>
                        <a href="<?=url('admin/' . $controllerRoute .'/'.$slug. '/change-status/'.Helper::encoded($row->id))?>" class="btn btn-outline-warning btn-sm" title="Deactivate <?=$module['title']?>"><i class="fa fa-times"></i></a>
                      <?php }?>
                      <a href="<?=url('admin/' . $controllerRoute .'/'.$slug. '/view_details/'.Helper::encoded($row->id))?>" class="btn btn-outline-primary btn-sm" title="ViewDetails <?=$module['title']?>" target="_blank"><i class="fa fa-eye"></i></a>
                    </td>
                  </tr>
                <?php } } else {?>
                  <tr>
                    <td colspan="8" style="text-align: center;color: red;">No Records Found !!!</td>
                  </tr>
                <?php }?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>