<?php
use App\Helpers\Helper;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Companies;
use App\Models\Employees;
use App\Models\EmployeeType;

$controllerRoute = $module['controller_route'];
?>
<style>
  .lightbox .lb-nav {
      display: none !important;
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
          <ul class="nav nav-tabs nav-tabs-bordered">
            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1">Basic Info</button>
            </li>            
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab2">Attendance</button>
            </li>            
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab3">Check In</button>
            </li>  
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab4">Odometer</button>
            </li>            
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab5">Orders</button>
            </li>                               
          </ul> 
          <div class="tab-content pt-2">     
            <div class="tab-pane fade show active" id="tab1">
              <!-- <p style="float:right;">
                ?php if($row->is_final_edit){?>
                  ?php if($row->is_published == 1){?>
                    <a href="?=url('admin/' . $controllerRoute . '/generate-nelp-form/'.Helper::encoded($row->id))?>" class="btn btn-outline-info btn-sm" title="Generate NELP Form & Shared"><i class="fa fa-pdf"> Generate NELP Form & Shared</i></a>
                  ?php }?>
                ?php }
                if ($row->is_import == 0) { 
                ?>
                <a href="?=url('admin/' . $controllerRoute . '/edit/'.Helper::encoded($row->id))?>" class="btn btn-outline-primary btn-sm" title="Edit <?=$module['title']?>"><i class="fa fa-edit"> Edit</i></a>
                ?php } ?>
              </p> -->
              <table class="table table-striped table-bordered nowrap">                
                <tbody>                
                    <tr>   
                    <?php if($admin->company_id == 0){ ?>
                    <th scope="col">Company Name</th>                    
                    <td>
                    <?php
                      $getCompany = Companies::select('id', 'name')->where('id', '=', $row->company_id)->first();
                      echo (($getCompany)?$getCompany->category_name:'');
                      ?>
                    </td>
                    <?php } ?>                                                                  
                    </tr>  
                    <tr>
                      <td>Employee Type</td>
                      <td><?php
                      $getRole = EmployeeType::select('id', 'name')->where('id', '=', $row->employee_type_id)->first();
                      echo (($getRole)?$getRole->name:'');
                      ?></td>
                    </tr>
                    <tr>
                      <td>Parent Employee</td>
                      <td><?php
                      $getParent = Employees::select('id', 'name')->where('id', '=', $row->parent_id)->first();
                      echo (($getParent)?$getParent->name:'');
                      ?></td>
                    </tr>
                    <tr>
                      <td>Employee No</td>
                      <td><?= $row->employee_no ?></td>
                    </tr>                    
                    <tr>
                      <td>Name</td>
                      <td><?= $row->name ?></td>
                    </tr>                
                    <tr>
                      <td>Email</td>
                      <td><?= $row->email ?></td>
                    </tr>
                    <tr>
                      <td>Alt Email</td>
                      <td><?= $row->alt_email ?></td>
                    </tr>
                    <tr>
                      <td>Phone</td>
                      <td><?=$row->phone?></td>
                    </tr>
                    <tr>
                      <td>Whatsapp No</td>
                      <td><?=$row->whatsapp_no?></td>
                    </tr>
                    <tr>
                      <td>Short Bio</td>
                      <td><?= $row->short_bio?></td>
                    </tr>
                    <tr>
                      <td>Address</td>
                      <td><?=$row->address?></td>
                    </tr>
                    <tr>
                      <td>Date of Birth</td>
                      <td><?= $row->dob ?></td>
                    </tr>
                    <tr>
                      <td>Date of Joining</td>
                      <td><?= $row->doj?></td>
                    </tr>
                    <tr>
                      <td>Qualification</td>
                      <td><?= $row->qualification ?></td>
                    </tr>
                    <tr>
                      <td>Profile Image</td>
                      <td>
                        <?php if (!empty($row->profile_image)) {?>
                          <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $row->profile_image, $row->name,'','','','margin-top: 10px') ?>
                          <!-- <img src="<?=env('UPLOADS_URL').'user/'.$row->profile_image?>" alt="<?=$row->name?>" style="width: 150px; height: 150px; margin-top: 10px;"> -->
                        <?php } else {?>
                          <img src="<?= env('NO_IMAGE') ?>" alt="<?=$row->name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                        <?php } ?>
                      </td>
                    </tr>                                        
                </tbody>
              </table>              
            </div>
            <div class="tab-pane fade pt-3" id="tab2">
              <div class="col-lg-12">
                <div class="card">
                  <div class="card-body">                    
                    <div class="dt-responsive table-responsive">
                      <table id="<?=((count($attandence)>0)?'simpletable':'')?>" class="table table-striped table-bordered nowrap">
                        <thead>
                          <tr>
                            <th scope="col">#</th>                            
                            <th scope="col">Attandence Date</th>
                            <th scope="col">Start Details</th>
                            <th scope="col">End Details</th>                           
                          </tr>
                        </thead>
                        <tbody>
                          <?php if(count($attandence)>0){ $sl=1; foreach($attandence as $attandences){?>
                            <tr>
                              <th scope="row"><?=$sl++?></th>                              
                              <td><?=date('M d Y', strtotime($attandences->attendance_date))?></td>
                              <td>
                                <?php 
                                    // Check if client signature exists
                                    if (!empty($attandences->start_image)) { ?>
                                    <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $attandences->start_image, $row->name) ?>
                                        <!-- <img src="<?= env('UPLOADS_URL'). 'user/' . $attandences->start_image ?>" class="img-thumbnail" alt="" style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                    <?php } else { ?>
                                        <!-- Display default image if no client signature exists -->
                                        <!-- <img src="<?= env('NO_IMAGE') ?>" alt="" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                        <?= Helper::generateLightboxImage(env('NO_IMAGE'), $row->name) ?>
                                    <?php } ?>
                                    <p><?= date('M d, Y h:i A', strtotime($attandences->start_timestamp))?></p>                                                         
                                  <p><?=nl2br(wordwrap($attandences->start_address, 60, "\n", true)) ?></p>
                                </td>                                                            
                              <td>
                                <?php 
                                    // Check if client signature exists
                                    if (!empty($attandences->end_image)) { ?>
                                        <!-- <img src="<?= env('UPLOADS_URL'). 'user/' . $attandences->end_image ?>" 
                                            class="img-thumbnail" 
                                            alt="" 
                                            style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                            <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $attandences->end_image, $row->name) ?>
                                    <?php } else { ?>
                                        <!-- Display default image if no client signature exists -->
                                        <!-- <img src="<?= env('NO_IMAGE') ?>" 
                                            alt="" 
                                            class="img-thumbnail" 
                                            style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                            <?= Helper::generateLightboxImage(env('NO_IMAGE'), $row->name) ?>
                                    <?php } ?>
                                    <p><?= date('M d, Y h:i A', strtotime($attandences->end_timestamp))?></p>                                                         
                                  <p><?=nl2br(wordwrap($attandences->end_address, 60, "\n", true)) ?></p>
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
              </div>                             
            </div>
            <div class="tab-pane fade pt-3" id="tab3">
              <div class="col-lg-12">
                <div class="card">
                  <div class="card-body">                    
                    <div class="dt-responsive table-responsive">
                      <table id="<?=((count($checkin)>0)?'simpletable':'')?>" class="table table-striped table-bordered nowrap">
                        <thead>
                          <tr>
                            <th scope="col">#</th>                            
                            <th scope="col">Client Type</th>
                            <th scope="col">Client Name</th>                            
                            <th scope="col">Checkin Image</th>
                            <th scope="col">Visit With</th>
                            <th scope="col">Address</th>                                                   
                            <th scope="col">Checkin Time</th>
                            <th scope="col">Note</th>
                            <!-- <th scope="col">Created Info<hr>Updated Info</th>  -->
                            <!-- <th scope="col">Action</th> -->
                          </tr>
                        </thead>
                        <tbody>
                          <?php if(count($checkin)>0){ $sl=1; foreach($checkin as $checkins){?>
                            <tr>
                              <th scope="row"><?=$sl++?></th>                              
                              <td>
                                <?php
                                $getEmployeeType = ClientType::select('name')->where('id', '=', $checkins->client_type_id)->first();
                                echo (($getEmployeeType)?$getEmployeeType->name:'');
                                ?>
                              </td>
                              <td>
                                <?php
                                $getEmployee = Client::select('name')->where('id', '=', $checkins->client_id)->first();
                                echo (($getEmployee)?$getEmployee->name:'');
                                ?>
                              </td>                                                            
                              <td>
                                <?php 
                                    // Check if client signature exists
                                    if (!empty($checkins->checkin_image)) { ?>
                                        <!-- <img src="<?= env('UPLOADS_URL'). 'user/' . $checkins->checkin_image ?>" 
                                            class="img-thumbnail" 
                                            alt="" 
                                            style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                            <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $checkins->checkin_image, $row->name) ?>
                                    <?php } else { ?>
                                        <!-- Display default image if no client signature exists -->
                                        <!-- <img src="<?= env('NO_IMAGE') ?>" 
                                            alt="" 
                                            class="img-thumbnail" 
                                            style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                            <?= Helper::generateLightboxImage(env('NO_IMAGE'), $row->name) ?>
                                    <?php } ?>
                              </td>                 
                              <td>
                                <?php
                                $getEmployeewith = Employees::select('name')->where('id', '=', $checkins->employee_with_id)->first();
                                echo (($getEmployeewith)?$getEmployeewith->name:'');
                                ?>
                              </td>                
                              <td><?php
                                $getEmployee = Client::select('address')->where('id', '=', $checkins->client_id)->first();
                                echo (($getEmployee)?$getEmployee->address:'');
                                ?></td>                              
                              <td><?=date('M d Y h:i A', strtotime($checkins->checkin_timestamp))?></td>
                              <td><?=$checkins->note?></td>
                              <!-- <td>?php
                                $getCreateUser = Admin::select('id', 'name')->where('id', '=', $checkins->created_by)->first();
                                $getUpdateUser = Admin::select('id', 'name')->where('id', '=', $checkins->updated_by)->first();                      
                                ?>
                                ?=(($getCreateUser)?$getCreateUser->name:'')?><br>?=date('M d Y h:i A', strtotime($checkins->created_at))?><hr>?=(($getUpdateUser)?$getUpdateUser->name:'')?><br>?=date('M d Y h:i A', strtotime($checkins->updated_at))?>
                              </td>  -->
                              <!-- <td onclick="clientwiseorderList('<?= $checkins->id ?>','<?= $checkins->order_no ?>','<?= $slug ?>')">    
                              <i class="fa fa-eye"></i>                                                            
                              </td> -->
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
              </div>                
            </div>
            <div class="tab-pane fade pt-3" id="tab4">
              <div class="col-lg-12">
                <div class="card">
                  <div class="card-body">                    
                    <div class="dt-responsive table-responsive">
                      <table id="<?=((count($odometers)>0)?'simpletable':'')?>" class="table table-striped table-bordered nowrap">
                        <thead>
                          <tr>
                            <th scope="col">#</th>                            
                            <th scope="col">odometer Date</th>
                            <th scope="col">Start Details</th>
                            <th scope="col">End Details</th> 
                            <th scope="col">Total KM</th>                          
                          </tr>
                        </thead>
                        <tbody>
                          <?php if(count($odometers)>0){ $sl=1; foreach($odometers as $odometer){?>
                            <tr>
                              <th scope="row"><?=$sl++?></th>                              
                              <td><?=date('M d, Y', strtotime($odometer->odometer_date))?></td>
                              <td>
                                <?php 
                                    // Check if client signature exists
                                    if (!empty($odometer->start_image)) { ?>
                                        <!-- <img src="<?= env('UPLOADS_URL'). 'user/' . $odometer->start_image ?>" class="img-thumbnail" alt="" style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                        <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $odometer->start_image, $row->name) ?>
                                    <?php } else { ?>
                                        <!-- Display default image if no client signature exists -->
                                        <!-- <img src="<?= env('NO_IMAGE') ?>" alt="" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                        <?= Helper::generateLightboxImage(env('NO_IMAGE'), $row->name) ?>
                                    <?php } ?>
                                    <p><?= date('M d, Y h:i A', strtotime($odometer->start_timestamp))?></p> 
                                    <p><?=$odometer->start_km?>KM</p>                                                        
                                  <p><?=nl2br(wordwrap($odometer->start_address, 60, "\n", true)) ?></p>
                                </td>                                                            
                              <td>
                                <?php 
                                    // Check if client signature exists
                                    if (!empty($odometer->end_image)) { ?>
                                        <!-- <img src="<?= env('UPLOADS_URL'). 'user/' . $odometer->end_image ?>" 
                                            class="img-thumbnail" 
                                            alt="" 
                                            style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                            <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $odometer->end_image, $row->name) ?>
                                    <?php } else { ?>
                                        <!-- Display default image if no client signature exists -->
                                        <!-- <img src="<?= env('NO_IMAGE') ?>" 
                                            alt="" 
                                            class="img-thumbnail" 
                                            style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                            <?= Helper::generateLightboxImage(env('NO_IMAGE'), $row->name) ?>
                                    <?php } ?>
                                    <p><?= date('M d, Y h:i A', strtotime($odometer->end_timestamp))?></p>                                                         
                                    <p><?=$odometer->end_km?>KM</p>   
                                  <p><?=nl2br(wordwrap($odometer->end_address, 60, "\n", true)) ?></p>
                              </td>
                              <td><?=$travel_distance?>KM</td>                                                                                          
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
              </div>                
            </div>
            <div class="tab-pane fade pt-3" id="tab5">
              <div class="col-lg-12">
                <div class="card">
                  <div class="card-body">                    
                    <div class="dt-responsive table-responsive">
                      <table id="<?=((count($order)>0)?'simpletable':'')?>" class="table table-striped table-bordered nowrap">
                        <thead>
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Order No</th>
                            <th scope="col">Client Type</th>
                            <th scope="col">Client Name</th>                            
                            <th scope="col">Order Date</th>
                            <th scope="col">Net Total</th>                            
                            <th scope="col">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if(count($order)>0){ $sl=1; foreach($order as $orders){?>
                            <tr>
                              <th scope="row"><?=$sl++?></th>
                              <td><?=$orders->order_no?></td>
                              <td>
                                <?php
                                $getClientType = ClientType::select('name')->where('id', '=', $orders->client_type_id)->first();
                                echo (($getClientType)?$getClientType->name:'');
                                ?>
                              </td>
                              <td>
                                <?php
                                $getClient = Client::select('name')->where('id', '=', $orders->client_id)->first();
                                echo (($getClient)?$getClient->name:'');
                                ?>
                              </td>                                                            
                              <td><?=date('M d Y h:i A', strtotime($orders->order_timestamp))?></td>
                              <td><i class="fa fa-inr"></i> <?=number_format($orders->net_total,2)?></td>                               
                              <td>
                              <?php if($orders->status == 1){
                                  $order_status = 'submitted';
                              } elseif($orders->status == 2){
                                  $order_status = 'approved';
                              } elseif($orders->status == 3){
                                  $order_status = 'dispatch';
                              } elseif($orders->status == 4){
                                  $order_status = 'billing';
                              } elseif($orders->status == 5){
                                  $order_status = 'completed';
                              }?>
                                <a href="<?=url('admin/' . $controllerRoute .'/'.$order_status. '/view_order_details/'.Helper::encoded($orders->id))?>" class="btn btn-outline-primary btn-sm" title="ViewDetails <?=$module['title']?>" target="_blank"><i class="fa fa-eye"></i></a>
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
              </div>          
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-dialog-scrollable mx-auto modal-lg">
        <div class="modal-content" id="modalBody">
        </div>
    </div>
  </div>
</section>
<script>
   function employeewiseorderList(orderId, name, slug) {    
       $('#modalBody').html('');
       // Construct the dynamic URL using the provided slug
       const requestUrl = `<?php echo url('admin/employee-details/'); ?>/${slug}/employeewiseorderListRecords`;
      //  alert(requestUrl);
       $.ajax({
           url: requestUrl,
           type: 'GET',
           data: {
               orderId: orderId,
               name: name                                         
           },
           dataType: 'html',
           success: function(response) {
            console.log(JSON.parse(response).html);
               $('#modalBody').html(JSON.parse(response).html);
               $('#myModal').modal('show');
           },
           error: function(xhr, status, error) {
               console.error('Error fetching modal content:', error);
           }
       });
   }
</script>