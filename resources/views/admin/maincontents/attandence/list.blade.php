<?php
use App\Helpers\Helper;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Employees;
use App\Models\EmployeeType;

$controllerRoute = $module['controller_route'];
$currentDate = date('Y-m-d');
// Split the date into an array
  $dateParts = explode('-', $currentDate);

  // Extract the year and month
  $year = $dateParts[0]; // 2024
  $month = $dateParts[1]; // 12
?>
<style>        
  .attendance-dashboard-holder .card {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  .attendance-dashboard-holder .table th{
      font-size: 14px;
      position: relative;
      /* border-right: 1px solid #ccc */
  }
  .attendance-dashboard-holder .table th::after{
      content: '';
      width: 1px;
      height: 50%;
      background: #ccc;
      position: absolute;
      top: 50%;
      right: 0;
      transform: translateY(-50%);
  }
  .attendance-dashboard-holder .table th:last-child:after{
      content: '';
      width: 1px;
      height: 50%;
      background: transparent;
      position: absolute;
      top: 50%;
      right: 0;
      transform: translateY(-50%);
  }
  .attendance-dashboard-holder .table th, .attendance-dashboard-holder .table td {
      vertical-align: middle;
      text-align: left;
      padding: 8px 15px;
  }
  .attendance-dashboard-holder .table-hover tbody tr:hover {
      background-color: #f1f1f1;
  }
  .table_user{
      width: 35px;
      height: 35px;
      border-radius: 50%;
      margin-right: 10px;
  }
</style>
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
  <!-- <div class="row"> -->
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
    <!-- <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            <a href="<?=url('admin/' . $controllerRoute . '/add/')?>" class="btn btn-outline-success btn-sm">Add <?=$module['title']?></a>
          </h5>
          <div class="dt-responsive table-responsive">
            <table id="simpletable" class="table table-striped table-bordered nowrap">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Order No</th>
                  <th scope="col">Employee Type</th>
                  <th scope="col">Employee Name</th>
                  <th scope="col">Client Type</th>
                  <th scope="col">Client Name</th>                  
                  <th scope="col">Address</th>
                  <th scope="col">Order Date</th>
                  <th scope="col">Net Total</th>                  
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($rows)>0){ $sl=1; foreach($rows as $row){?>
                  <tr>
                    <th scope="row"><?=$sl++?></th>
                    <td><?=$row->order_no?></td>
                    <td>
                      <?php
                      $getEmployeeType = EmployeeType::select('name')->where('id', '=', $row->employee_type_id)->first();
                      echo (($getEmployeeType)?$getEmployeeType->name:'');
                      ?>
                    </td>
                    <td>
                      <?php
                      $getEmployee = Employees::select('name')->where('id', '=', $row->employee_id)->first();
                      echo (($getEmployee)?$getEmployee->name:'');
                      ?>
                    </td> 
                    <td>
                      <?php
                      $getClientType = ClientType::select('name')->where('id', '=', $row->client_type_id)->first();
                      echo (($getClientType)?$getClientType->name:'');
                      ?>
                    </td>
                    <td>
                      <?php
                      $getClient = Client::select('name')->where('id', '=', $row->client_id)->first();
                      echo (($getClient)?$getClient->name:'');
                      ?>
                    </td>                                                    
                    <td><?php
                      $getClient = Client::select('address')->where('id', '=', $row->client_id)->first();
                      echo (($getClient)?$getClient->address:'');
                      ?></td>
                    <td><?=date('M d Y h:i A', strtotime($row->order_timestamp))?></td>
                    <td><?=$row->net_total?></td>                    
                    <td>    
                      <a href="<?=url('admin/' . $controllerRoute . '/view_order_details/'.Helper::encoded($row->id))?>" class="btn btn-outline-primary btn-sm" title="ViewDetails <?=$module['title']?>" target="_blank"><i class="fa fa-eye"></i></a>                                                           
                    </td>
                  </tr>
                <?php } } else {?>
                  <tr>
                    <td colspan="12" style="text-align: center;color: red;">No Records Found !!!</td>
                  </tr>
                <?php }?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div> -->
    <div class="attendance-dashboard-holder">
      <div class="container-fluid">
          <div class="row">
              <div class="d-flex justify-content-between align-items-center mb-3 border-bottom p-2">
                  <h4>Attendance Dashboard</h4>
              </div>
          </div>
          <div class="row">
              <!-- Sidebar Filters -->
              <div class="col-md-3 mb-4">
                  <div class="card">
                      <div class="card-body">
                          <h5 class="card-title">Filters</h5>
                          <form method="post">
                              <div class="mb-3">
                                  <label for="month" class="form-label">Month</label>
                                  <input type="month" name="month" value="{{ $year . '-' . $month }}"  class="form-control" id="month">
                                  <!-- <input type="hidden" name="form_month" value="{{ $month }}"> -->
                              </div>                                                            
                              <div class="mb-3">
                                  <label for="department" class="form-label">Select Department</label>
                                  <select class="form-select" name="department" id="department">
                                      <option value="all">All Departments</option>
                                      <?php foreach($employee_types as $employee_dpt){ ?>
                                      <option value="<?=$employee_dpt->id?>"><?=$employee_dpt->name?></option>
                                      <?php } ?>
                                  </select>
                              </div>                              
                              <div class="form-check">
                                  <input class="form-check-input" name="inactiveStaff" type="checkbox" id="inactiveStaff">
                                  <label class="form-check-label" for="inactiveStaff">
                                      Show Inactive Staff
                                  </label>
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
      
              <!-- Main Content -->
              <div class="col-md-9">
                  <div class="card">
                      <div class="card-header">
                          <div class="row">
                              <div class="col-lg-4 col-md-6">
                                  <div class="input-group">
                                      <input type="text" class="form-control" placeholder="Search staff by Name or Phone" aria-label="Username" aria-describedby="basic-addon1">
                                  </div>
                              </div>
                              <div class="col-lg-8 col-md-6 d-flex justify-content-end">
                                  <button class="btn btn-primary">Download Report</button>
                              </div>
                          </div>
                      </div>
                      <div class="card-body">
                          <div class="table-responsive">
                              <table class="table table-hover">
                                  <thead class="table-light">
                                  <tr>
                                      <th>Name</th>
                                      <th>Phone Number</th>
                                      <th>Employee ID</th>
                                      <th>Job Title</th>
                                      <th>Present</th>
                                  </tr>
                                  </thead>
                                  <tbody>
                                  <?php if(count($rows)>0){ $sl=1; foreach($rows as $row){ ?>
                                    
                                  <tr>                                                                        
                                      <td><img src="<?=env('UPLOADS_URL').$row->profile_image?>" class="table_user" alt="<?=$row->name?>" style="width: 150px; height: 150px; margin-top: 10px;"><?=$row->name?></td>
                                      <td><?=$row->phone?></td>
                                      <td><?=$row->employee_no?></td>
                                      <td><?php
                                      $getRole = EmployeeType::select('id', 'name')->where('id', '=', $row->employee_type_id)->first();
                                      echo (($getRole)?$getRole->name:'');
                                      ?>
                                      </td>
                                      <td><?php
                                      $getattandence = Attendance::whereMonth('attendance_date', $month)
                                                                  ->whereYear('attendance_date', $year)
                                                                  ->where('employee_id', '=', $row->id)->count();
                                      echo $getattandence; ?>
                                      </td>
                                    
                                  </tr> 
                                  
                                  <?php } } ?>                                
                                  <!-- Add more rows as needed -->
                                  </tbody>
                              </table>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
    </div>
  <!-- </div> -->
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function () {
        function filterEmployees() {
          let monthfetch = $('#month').val();
          let parts = monthfetch.split('-');
          let year = parts[0];  // "2024"
          let month = parts[1];  // "12"
          // let month =  || new Date().toISOString().slice(0, 7);
          // let year = $('#month').val() ? $('#month').val().split('-')[0] : new Date().getFullYear();
            let department = $('#department').val() || 'all';
            let inactiveStaff = $('#inactiveStaff').is(':checked') ? 1 : 0;
            // alert(month);
            
            $.ajax({
                url: 'filter',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    month: month,
                    year: year,
                    department: department,
                    inactiveStaff: inactiveStaff
                },
                success: function (data) {
                    let tableBody = '';
                    if (data.length > 0) {
                        data.forEach(employee => {
                          const baseUrl = "<?= url('admin/' . $controllerRoute . '/view_details') ?>";
                            tableBody += `
                                <tr>
                                    <td>
                                        <a href="${baseUrl}/${employee.encoded_id}" class="btn btn-outline-primary btn-sm" title="View Details ${employee.employee_name}" target="_blank">
                                            <img src="<?=env('UPLOADS_URL')?>${employee.profile_image}" alt="" class="table_user">
                                            ${employee.employee_name}
                                        </a>
                                    </td>
                                    <td>${employee.phone}</td>
                                    <td>${employee.employee_no}</td>
                                    <td>${employee.employee_dept}</td>
                                    <td>${employee.attendance_count || 0}</td>
                                </tr>`;
                        });
                    } else {
                        tableBody = '<tr><td colspan="5" class="text-center">No employees found</td></tr>';
                    }
                    $('tbody').html(tableBody);
                }
            });
        }

        // Trigger default filter on page load
        filterEmployees();

        // Re-filter on user action
        $('#month, #department, #inactiveStaff').on('change', filterEmployees);
    });
</script>

