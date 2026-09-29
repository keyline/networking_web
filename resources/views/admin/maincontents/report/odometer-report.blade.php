<?php
use App\Helpers\Helper;
use App\Models\GeneralSetting;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Odometer;
use App\Models\Admin;
?>
<style type="text/css">
  .table .badge {
    border-radius: 100px;
    padding: 4.5px 3px;
    font-size: 10px;
  }
  .badge-tracker-danger {
    background: #FFE1E1;
    border: 1px solid #EA2A2A;
    color: #EA2A2A;
    margin: 2px 0;
    height: 25px;
    width: 60px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .badge-desktime-success {
    background: #F0FFF0;
    border: 1px solid #96c098;
    color: #4CAB4F;
    margin: 2px 0;
    height: 25px;
    width: 65px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .badge-desktime-primary {
    background: #F0FFF0;
    border: 1px solid #2c7afa;
    color: #2c7afa;
    margin: 2px 0;
    height: 25px;
    width: 65px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  /* modal css */
  .modal.drawer {
      display: flex !important;
      pointer-events: none;
  }

  .modal.drawer * {
      pointer-events: none;
  }

  .modal.drawer .modal-dialog {
      margin: 0px;
      display: flex;
      flex: auto;
      transform: translate(25%, 0);
  }

  .modal.drawer .modal-dialog .modal-content {
      border: none;
      border-radius: 0px;
  }

  .modal.drawer .modal-dialog .modal-content .modal-body {
      overflow: auto;
  }

  .modal.drawer.show {
      pointer-events: auto;
  }

  .modal.drawer.show * {
      pointer-events: auto;
  }

  .modal.drawer.show .modal-dialog {
      transform: translate(0, 0);
  }

  .modal.drawer.right-align {
      flex-direction: row-reverse;
  }

  .modal.drawer.left-align:not(.show) .modal-dialog {
      transform: translate(-25%, 0);
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
</div>
<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            Attendance Report
          </h5>
          <form method="GET" action="<?=url('admin/report/odometer-report-search')?>">
            <div class="row mb-3" style="border:1px solid #00c9a759; border-radius: 10px; padding: 10px;">
                <div class="col-md-6">
                  <input type="month" name="month_year" class="form-control" value="<?=date($year.'-'.$month)?>">
                </div>
                <div class="col-md-6">
                  <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane"></i> Generate</button>
                  <?php if($is_search){?><a href="<?=url('admin/report/odometer-report')?>" class="btn btn-secondary btn-sm"><i class="fa fa-refresh"></i> Reset</a><?php }?>
                </div>
            </div>
          </form>
          <?php
          if(date('m') == $month){
            $dateLoop = date('d');
          } else {
            $lastDay  = date("t", strtotime("$year-$month-01"));
            $dateLoop = $lastDay;
          }
          ?>
          <div class="dt-responsive table-responsive">
            <?php if(count($rows)>0){?>
              <table id="simpletable" class="table table-striped table-bordered">
            <?php } else {?>
              <table class="table table-striped table-bordered">
            <?php }?>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <?php for($k=1;$k<=$dateLoop;$k++){?>
                    <?php
                    $date = date($year.'-'.$month).'-'.$k; // Input date
                    $weekdayName = Helper::getWeekdayName($date);
                    ?>
                  <th><?=$weekdayName?><br><?=$k?></th>
                  <?php }?>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php if($rows){ $sl=1; foreach($rows as $row){?>
                  <?php
                  $tripTotal = Odometer::where('employee_id', '=', $row->id)->where('odometer_date', 'LIKE', '%'.$year.'-'.$month.'%')->sum('travel_distance');
                  if($tripTotal > 0){
                  ?>
                    <tr>
                      <td><?=$sl++?></td>
                      <td>
                        <img src="<?=(($row->profile_image != '')?env('UPLOADS_URL'). 'user/' . $row->profile_image:env('NO_IMAGE'))?>" alt="<?=$row->name?>" class="img-thumbnail me-2" style="width: 40px; height: 40px; border-radius: 50%;">
                        <h6><?=$row->name?></h6>
                        <span class="badge bg-success" style="font-size: 9px;"><?=$row->employee_type_name?></span><br>
                        (<small><?=$row->employee_no?></small>)
                      </td>
                      <?php
                      $total_km = 0; 
                      for($k=1;$k<=$dateLoop;$k++){?>
                        <td>
                          <?php
                          $loopDate    = date($year.'-'.$month).'-'.(($k <= 9)?'0'.$k:$k);
                          $tripDetails = Odometer::where('employee_id', '=', $row->id)->where('odometer_date', '=', $loopDate)->sum('travel_distance');
                          if($tripDetails) {
                          ?>
                            <p>
                              <span class="badge badge-desktime-primary d-block h-100" style="cursor:pointer;" onclick="openAttendanceModal(<?=$row->id?>, '<?=$row->name?>', '<?=$loopDate?>');">
                                <span class="mt-3"><?=$tripDetails?> km</span>
                              </span>
                            </p>
                            <?php $total_km += $tripDetails;?>
                          <?php }?>
                        </td>
                      <?php }?>
                      <td><?=$total_km?> km</td>
                    </tr>
                  <?php }?>
                <?php } }?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Modal -->
<div class="modal fade drawer right-align" id="attendance_popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  
</div>
<script type="text/javascript">
  function openAttendanceModal(userId, name, date) {
    $.ajax({
        url: '<?php echo url('admin/report/get-attendance-details'); ?>',
        type: 'POST',
        data: {
            "_token": "{{ csrf_token() }}",
            userId: userId,
            name: name,
            date: date
        },
        dataType: 'html',
        success: function(response) {
          $('#attendance_popup').html(response);
          $('#attendance_popup').modal('show');
        }
    });
  }
</script>