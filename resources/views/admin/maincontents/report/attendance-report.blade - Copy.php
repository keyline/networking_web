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
</div><!-- End Page Title -->
<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            Attendance Report
          </h5>
          <form>
            <div class="row mb-3" style="border:1px solid #00c9a759; border-radius: 10px; padding: 10px;">
                <div class="col-md-6">
                  <input type="month" name="month_year" class="form-control" value="<?=date('Y-m')?>">
                </div>
                <div class="col-md-6">
                  <button type="submit" class="btn btn-primary btn-sm">Generate</button>
                </div>
            </div>
          </form>
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
                  <?php for($k=1;$k<=date('d');$k++){?>
                    <?php
                    $date = date('Y-m').'-'.$k; // Input date
                    $weekdayName = Helper::getWeekdayName($date);
                    ?>
                  <th><?=$weekdayName?><br><?=$k?></th>
                  <?php }?>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php if($rows){ $sl=1; foreach($rows as $row){?>
                  <tr>
                    <td><?=$sl++?></td>
                    <td>
                      <img src="http://localhost/e0n1e0r1g0i1c/public/uploads/user/6761473b2e215.jpeg" alt="image" class="img-thumbnail me-2" style="width: 40px; height: 40px; border-radius: 50%;">
                      <h6><?=$row->name?></h6>
                      <span class="badge bg-success" style="font-size: 9px;"><?=$row->employee_type_name?></span><br>
                      (<small><?=$row->employee_no?></small>)
                    </td>
                    <?php
                    $total_km = 0; 
                    for($k=1;$k<=date('d');$k++){?>
                    <td>
                      <?php
                      $loopDate = date('Y-m').'-'.(($k <= 9)?'0'.$k:$k);
                      $punchInRow = Attendance::select('start_timestamp', 'status')->where('employee_id', '=', $row->id)->where('attendance_date', '=', $loopDate)->orderBy('id', 'ASC')->first();
                      if($punchInRow){
                      ?>
                        <p>
                          <span class="badge badge-tracker-danger d-block h-100" style="cursor:pointer;" onclick="openAttendanceModal(<?=$row->id?>, '<?=$row->name?>', '<?=$loopDate?>');">
                            <span class="mt-3">IN: <?=date_format(date_create($punchInRow->start_timestamp), "H:i")?></span>
                          </span>
                        </p>
                        <?php
                        $punchOutRow = Attendance::select('end_timestamp', 'status')->where('employee_id', '=', $row->id)->where('attendance_date', '=', $loopDate)->orderBy('id', 'DESC')->first();
                        if($punchOutRow->status == 2){?>
                          <p>
                            <span class="badge badge-desktime-success d-block h-100" style="cursor:pointer;" onclick="openAttendanceModal(<?=$row->id?>, '<?=$row->name?>', '<?=$loopDate?>');">
                              <span class="mt-3">OUT: <?=date_format(date_create($punchOutRow->end_timestamp), "H:i")?></span>
                            </span>
                          </p>
                        <?php }?>
                      <?php }?>
                      <br>
                      <?php
                      $tripDetails = Odometer::where('employee_id', '=', $row->id)->where('odometer_date', '=', $loopDate)->sum('travel_distance');
                      if($tripDetails) {
                      ?>
                        <p>
                          <span class="badge badge-desktime-primary d-block h-100" style="cursor:pointer;">
                            <span class="mt-3"><?=$tripDetails?> km</span>
                          </span>
                        </p>
                        <?php $total_km += $tripDetails;?>
                      <?php }?>
                    </td>
                    <?php }?>
                    <td><?=$total_km?> km</td>
                  </tr>
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
<div class="modal fade" id="attendance_info_popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
        dataType: 'JSON',
        success: function(response) {
          $('#attendance_info_popup').empty();
          // $('#attendance_info_popup').modal('show');
          // var attnHtml = '';
          // $.each(response.attnDatas, function(index, value) {
          //     attnHtml += `<div class="d-flex align-items-center mb-3">
          //                     <img src="${value.image}" alt="Profile" class="rounded-circle me-3 table_user">
          //                     <div>
          //                         <p><strong>${value.time}</strong> &middot; ${value.label}</p>
          //                         <small class="text-muted">${value.address}</small>
          //                     </div>
          //                   </div>`;
          // });
          // var modal_html = `<div class="modal-dialog" role="document">
          //                     <div class="modal-content">
          //                       <div class="modal-header">
          //                           <h5 class="modal-title" id="exampleModalLabel">Edit Attendance: ${response.name}</h5>
          //                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          //                       </div>
          //                       <div class="modal-body">
          //                           <div class="d-flex align-items-center mb-3">
          //                               <div>
          //                                   <h6 class="mb-0">${response.attn_date}</h6>
          //                               </div>
          //                           </div>
          //                           <div class="status-buttons mb-3">
          //                               <button class="btn btn-outline-danger mb-1">ABSENT</button>
          //                               <button class="btn btn-outline-success mb-1">PRESENT</button>
          //                           </div>
          //                           <div class="mb-3">
          //                               <h6 class="mb-1">Attendance History</h6>
          //                               ${attnHtml}
          //                           </div>
          //                           <div class="odo-card">
          //                               <div class="card-body">
          //                                 <h6 class="mb-1">ODO History</h6>

          //                                 <div class="timeline-section">
          //                                     <div class="row align-items-center">
          //                                         <div class="col-sm-5">
          //                                             <h5 class="text-success">START</h5>
          //                                             <img src="img.jpeg" alt="start">
          //                                             <p><i class="fa-solid fa-gauge me-2"></i>1995 KM</p>
          //                                             <p><i class="fa-regular fa-clock me-2"></i>12:32 PM</p>
          //                                             <p class="location"><i class="fa-solid fa-location-dot me-2"></i>983, 25th A Cross Rd, HSR Layout</p>
          //                                         </div>
          //                                         <div class="col-sm-2">
          //                                             <div class="distance_box">
          //                                                 <i class="fa-solid fa-right-left"></i>
          //                                                 <div class="distance">10 KM</div>
          //                                             </div>
          //                                         </div>
          //                                         <div class="col-sm-5">
          //                                             <h5 class="text-danger">END</h5>
          //                                             <img src="img.jpeg" alt="start">
          //                                             <p><i class="fa-solid fa-gauge me-2"></i>1995 KM</p>
          //                                             <p><i class="fa-regular fa-clock me-2"></i>12:32 PM</p>
          //                                             <p class="location"><i class="fa-solid fa-location-dot me-2"></i>983, 25th A Cross Rd, HSR Layout</p>
          //                                         </div>
          //                                     </div>
          //                                 </div>
                                            
          //                               </div>
          //                           </div>
          //                       </div>
          //                     </div>
          //                   </div>`;
          
          

          // Append HTML to the modal body
          $('#attendance_info_popup').html('<p>modal content goes here</p>');
          $('#attendance_info_popup').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error fetching modal content:', error);
        }
    });
  }
</script>