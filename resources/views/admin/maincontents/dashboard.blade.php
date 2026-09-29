<?php
use App\Models\District;
use App\Models\Employees;
use App\Models\EmployeeType;
use App\Helpers\Helper;
?>
<style>
  .tree ul {
      list-style: none;
      margin: 0;
      padding-left: 20px;
      position: relative;
      display: none; /* Initially hide all child nodes */
  }

  .tree li {
      margin: 0;
      padding: 0 0 10px 20px;
      line-height: 1.5em;
      position: relative;
  }

  .tree li::before, .tree li::after {
      content: '';
      position: absolute;
      left: -10px;
  }

  .tree li::before {
      border-left: 2px solid #ccc;
      top: 0;
      bottom: 50%;
      height: 100%;
      width: 10px;
  }

  .tree li::after {
      border-top: 2px solid #ccc;
      top: 1.5em;
      width: 10px;
      height: 0;
  }

  .tree li:last-child::before {
      height: 50%;
  }

  .node {
      display: inline-block;
      border: 1px solid #ccc;
      border-radius: 4px;
      padding: 5px 10px;
      background: #f9f9f9;
      cursor: pointer;
  }

  .node:hover {
      background: #e0e0e0;
  }
</style>
<!-- Content -->
  <div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
      <div class="row align-items-center">
        <div class="col">
          <h1 class="page-header-title"><?=$page_header?></h1>
        </div>
        <!-- End Col -->
        <!-- <div class="col-auto">
          <a class="btn btn-primary" href="javascript:;" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
            <i class="bi-person-plus-fill me-1"></i> Invite users
          </a>
        </div> -->
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
    <!-- End Page Header -->
    <!-- Stats -->
    {{-- <div class="dashboad_top">
        <h4>Today's Report</h4>
      <div class="row">
        <div class="col-sm-6 col-lg-4 mb-2 mb-lg-1">
          <!-- Card -->
          <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openPucnchpop()">
            <div class="card-body">
              <h6 class="card-subtitle">Employee Punched In</h6>
              <div class="row align-items-center gx-2 mb-1">
                <div class="col-12">
                  <h2 class="card-title text-inherit"><?=$totalattandence?></h2>
                </div>
              </div>
              <!-- End Row -->
            </div>
          </a>
          <!-- End Card -->
        </div>
        <div class="col-sm-6 col-lg-4 mb-2 mb-lg-1">
          <!-- Card -->
          <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openOrderpop()">
            <div class="card-body">
              <h6 class="card-subtitle">Total Order</h6>
              <div class="row align-items-center gx-2 mb-1">
                <div class="col-12">
                  <h2 class="card-title text-inherit"><?=$totalorder?></h2>
                </div>
              </div>
              <!-- End Row -->
              <!-- <span class="badge bg-soft-success text-success">
                <i class="bi-graph-up"></i> 1.7%
              </span>
              <span class="text-body fs-6 ms-1">from 29.1%</span> -->
            </div>
          </a>
          <!-- End Card -->
        </div>
        <div class="col-sm-6 col-lg-4 mb-2 mb-lg-1">
          <!-- Card -->
          <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openOrderpop()">
            <div class="card-body">
              <h6 class="card-subtitle">Order Value</h6>
              <div class="row align-items-center gx-2 mb-1">
                <div class="col-12">
                  <h2 class="card-title text-inherit"><i class="fa-solid fa-indian-rupee-sign"></i><?=number_format($totalordervalue,2);?></h2>
                </div>
              </div>
              <!-- End Row -->
              <!-- <span class="badge bg-soft-danger text-danger">
                <i class="bi-graph-down"></i> 4.4%
              </span>
              <span class="text-body fs-6 ms-1">from 61.2%</span> -->
            </div>
          </a>
          <!-- End Card -->
        </div>
      </div>
      <div class="vist_repot_dash">
          <div class="col-md-12"><h3>Visit Report</h3></div>
          <div class="row">
              <div class="col-sm-6 col-lg-3 mb-2 mb-lg-1">
              <!-- Card -->
                <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openCheckingpop(<?=$dealer?>)">
                  <div class="card-body">
                    <h6 class="card-subtitle">Distributor</h6>
                    <div class="row align-items-center gx-2 mb-1">
                      <div class="col-12">
                        <h2 class="card-title text-inherit"><?=$todaydistributor?></h2>
                      </div>
                    </div>
                    <!-- End Row -->
                  </div>
                </a>
                <!-- End Card -->
              </div>
              <div class="col-sm-6 col-lg-3 mb-2 mb-lg-1">
              <!-- Card -->
                <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openCheckingpop(<?=$distributor?>)">
                  <div class="card-body">
                    <h6 class="card-subtitle">Dealer</h6>
                    <div class="row align-items-center gx-2 mb-1">
                      <div class="col-12">
                        <h2 class="card-title text-inherit"><?=$todaydealer?></h2>
                      </div>
                    </div>
                    <!-- End Row -->
                  </div>
                </a>
                <!-- End Card -->
              </div>
              <div class="col-sm-6 col-lg-3 mb-2 mb-lg-1">
              <!-- Card -->
                <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openCheckingpop(<?=$retailer?>)">
                  <div class="card-body">
                    <h6 class="card-subtitle">Retailer</h6>
                    <div class="row align-items-center gx-2 mb-1">
                      <div class="col-12">
                        <h2 class="card-title text-inherit"><?=$todayretailer?></h2>
                      </div>
                    </div>
                    <!-- End Row -->
                  </div>
                </a>
                <!-- End Card -->
              </div>
              <div class="col-sm-6 col-lg-3 mb-2 mb-lg-1">
              <!-- Card -->
                <a class="card card-hover-shadow h-100" href="javascript:void(0);" onclick="openCheckingpop(<?=$farmer?>)">
                  <div class="card-body">
                    <h6 class="card-subtitle">Farmar</h6>
                    <div class="row align-items-center gx-2 mb-1">
                      <div class="col-12">
                        <h2 class="card-title text-inherit"><?=$todayfarmer?></h2>
                      </div>
                    </div>
                    <!-- End Row -->
                  </div>
                </a>
                <!-- End Card -->
              </div>
          </div>
      </div>
    </div> --}}
    <div class="dasbbaord_total">
        {{-- <h3>Total Client</h3> --}}
        <div class="row">
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <!-- Card -->
            <a class="card card-hover-shadow h-100" target="_blank" href="<?=url('admin/clients/business/list')?>">
              <div class="card-body">
                <h6 class="card-subtitle">Business</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-12">
                    <h2 class="card-title text-inherit"><?=$totalBusiness?></h2>
                  </div>
                </div>
                <!-- End Row -->
              </div>
            </a>
            <!-- End Card -->
          </div>

          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <!-- Card -->
            <a class="card card-hover-shadow h-100" target="_blank" href="<?=url('admin/clients/buyer/list')?>">
              <div class="card-body">
                <h6 class="card-subtitle">Buyer</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-12">
                    <h2 class="card-title text-inherit"><?=$totalBuyer?></h2>
                  </div>
                </div>
                <!-- End Row -->
              </div>
            </a>
            <!-- End Card -->
          </div>
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <!-- Card -->
            <a class="card card-hover-shadow h-100" target="_blank" href="<?=url('admin/clients/seller/list')?>">
              <div class="card-body">
                <h6 class="card-subtitle">Seller</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-12">
                    <h2 class="card-title text-inherit"><?=$totalSeller?></h2>
                  </div>
                </div>
                <!-- End Row -->
              </div>
            </a>
            <!-- End Card -->
          </div>
          <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
            <!-- Card -->
            <a class="card card-hover-shadow h-100" target="_blank" href="#">
              <div class="card-body">
                <h6 class="card-subtitle">Business Types</h6>
                <div class="row align-items-center gx-2 mb-1">
                  <div class="col-12">
                    <h2 class="card-title text-inherit"><?=$totalTypes?></h2>
                  </div>
                </div>
                <!-- End Row -->
              </div>
            </a>
            <!-- End Card -->
          </div>
        </div>
    </div>
    <!-- End Stats -->
    {{-- <div class="row">
      <div class="col-sm-12 col-lg-12 mb-3 mb-lg-5">
        <div class="card">
          <div class="card-header"><h5>Employee Tree</h5></div>
          <div class="card-body">
            <?php
            $districtIds = [];
            $emps = Employees::select('assign_district')->where('status', '!=', 3)->get();
            if($emps){
              foreach($emps as $emp){
                $assign_districts = json_decode($emp->assign_district);
                if(!empty($assign_districts)){
                  for($d=0;$d<count($assign_districts);$d++){
                    if(!in_array($assign_districts[$d], $districtIds)){
                      $districtIds[] = $assign_districts[$d];
                    }
                  }
                }
              }
            }
            ?>
            <div id="tree-view">
              <ul class="tree">
                  <li>
                      <span class="node">WEST BENGAL</span>
                      <ul>
                        <?php
                        ini_set('memory_limit', '512M'); // Or '512M', depending on your needs
                        $level1_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 1)->first();
                        $level2_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 2)->first();
                        $level3_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 3)->first();
                        $level4_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 4)->first();
                        $level5_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 5)->first();
                        $level6_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 6)->first();
                        $level7_emp_type = EmployeeType::select('prefix')->where('status', '=', 1)->where('level', '=', 7)->first();
                        if(!empty($districtIds)){ for($d=0;$d<count($districtIds);$d++){
                          $getDistrict = District::select('id', 'name')->where('id', '=', $districtIds[$d])->first();
                        ?>
                          <li>
                              <span class="node"><?=(($getDistrict)?$getDistrict->name:'')?></span>
                              <ul>
                                <?php
                                $getEmps1 = Employees::select('name')->where('employee_type_id', '=', 1)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                if($getEmps1){ foreach($getEmps1 as $getEmp1){
                                ?>
                                  <li>
                                    <span class="node" style="<?=(($getEmp1)?(($getEmp1->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp1)?$getEmp1->name:'-VACANT-')?> (<?=(($level1_emp_type)?$level1_emp_type->prefix:'')?>)</span>
                                    <ul>
                                      <?php
                                      $getEmps2 = Employees::select('name')->where('employee_type_id', '=', 2)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                      if($getEmps2){ foreach($getEmps2 as $getEmp2){
                                      ?>
                                        <li>
                                            <span class="node" style="<?=(($getEmp2)?(($getEmp2->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp2)?$getEmp2->name:'-VACANT-')?> (<?=(($level2_emp_type)?$level2_emp_type->prefix:'')?>)</span>
                                            <ul>
                                              <?php
                                              $getEmps3 = Employees::select('name')->where('employee_type_id', '=', 3)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                              if($getEmps3){ foreach($getEmps3 as $getEmp3){
                                              ?>
                                                <li>
                                                    <span class="node" style="<?=(($getEmp3)?(($getEmp3->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp3)?$getEmp3->name:'-VACANT-')?> (<?=(($level3_emp_type)?$level3_emp_type->prefix:'')?>)</span>
                                                    <ul>
                                                      <?php
                                                      $getEmps4 = Employees::select('name')->where('employee_type_id', '=', 4)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                                      if($getEmps4){ foreach($getEmps4 as $getEmp4){
                                                      ?>
                                                        <li>
                                                            <span class="node" style="<?=(($getEmp4)?(($getEmp4->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp4)?$getEmp4->name:'-VACANT-')?> (<?=(($level4_emp_type)?$level4_emp_type->prefix:'')?>)</span>
                                                            <ul>
                                                              <?php
                                                              $getEmps5 = Employees::select('name')->where('employee_type_id', '=', 5)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                                              if($getEmps5){ foreach($getEmps5 as $getEmp5){
                                                              ?>
                                                                <li>
                                                                    <span class="node" style="<?=(($getEmp5)?(($getEmp5->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp5)?$getEmp5->name:'-VACANT-')?> (<?=(($level5_emp_type)?$level5_emp_type->prefix:'')?>)</span>
                                                                    <ul>
                                                                      <?php
                                                                      $getEmps6 = Employees::select('name')->where('employee_type_id', '=', 6)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                                                      if($getEmps6){ foreach($getEmps6 as $getEmp6){
                                                                      ?>
                                                                        <li>
                                                                            <span class="node" style="<?=(($getEmp6)?(($getEmp6->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp6)?$getEmp6->name:'-VACANT-')?> (<?=(($level6_emp_type)?$level6_emp_type->prefix:'')?>)</span>
                                                                            <ul>
                                                                              <?php
                                                                              $getEmps7 = Employees::select('name')->where('employee_type_id', '=', 7)->where('status', '=', 1)->whereJsonContains('assign_district', $districtIds[$d])->get();
                                                                              if($getEmps7){ foreach($getEmps7 as $getEmp7){
                                                                              ?>
                                                                                <li>
                                                                                    <span class="node" style="<?=(($getEmp7)?(($getEmp7->name != 'VACANT')?'':'color:red;'):'color:red;')?>"><?=(($getEmp7)?$getEmp7->name:'-VACANT-')?> (<?=(($level7_emp_type)?$level7_emp_type->prefix:'')?>)</span>
                                                                                </li>
                                                                              <?php } }?>
                                                                            </ul>
                                                                        </li>
                                                                      <?php } }?>
                                                                    </ul>
                                                                </li>
                                                              <?php } }?>
                                                            </ul>
                                                        </li>
                                                      <?php } }?>
                                                    </ul>
                                                </li>
                                              <?php } }?>
                                            </ul>
                                        </li>
                                      <?php } }?>
                                    </ul>
                                  </li>
                                <?php } }?>
                              </ul>
                          </li>
                        <?php } }?>
                      </ul>
                  </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
  </div>
<!-- End Content -->

<!-- Modal -->
<div class="modal fade" id="pucnchpop" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

</div>

<div class="modal fade" id="orderpop" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

</div>

<div class="modal fade" id="checkingpop" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

</div>

<script>
  document.querySelectorAll('.node').forEach(node => {
      node.addEventListener('click', function (e) {
          e.stopPropagation(); // Prevent event from bubbling up

          const parentLi = this.parentElement;
          const childUl = parentLi.querySelector('ul');

          if (childUl) {
              // Toggle visibility
              if (childUl.style.display === 'none' || childUl.style.display === '') {
                  childUl.style.display = 'block'; // Expand
              } else {
                  childUl.style.display = 'none'; // Collapse
              }
          }
      });
  });
</script>

<script>
  function openPucnchpop(){
    // $('#pucnchpop').modal('show');
    $.ajax({
        url: '<?php echo url('admin/today-attandence-details'); ?>',
        type: 'POST',
        data: {
            "_token": "{{ csrf_token() }}",
        },
        dataType: 'html',
        success: function(response) {
          $('#pucnchpop').html(response);
          $('#pucnchpop').modal('show');
        }
    });
  }

  function openOrderpop(){
    $.ajax({
        url: '<?php echo url('admin/today-order-details'); ?>',
        type: 'POST',
        data: {
            "_token": "{{ csrf_token() }}",
        },
        dataType: 'html',
        success: function(response) {
          $('#orderpop').html(response);
          $('#orderpop').modal('show');
        }
    });
  }

  function openCheckingpop(clienttype){
    $.ajax({
        url: '<?php echo url('admin/today-client-details'); ?>',
        type: 'POST',
        data: {
            "_token": "{{ csrf_token() }}",
            clienttype: clienttype,
        },
        dataType: 'html',
        success: function(response) {
          $('#orderpop').html(response);
          $('#orderpop').modal('show');
        }
    });
  }

</script>

