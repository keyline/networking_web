<?php
use App\Helpers\Helper;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Employees;
use App\Models\EmployeeType;

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
          <div class="dt-responsive table-responsive">
            <?php if(count($rows)>0){?>
              <table id="simpletable" class="table table-striped table-bordered nowrap">
            <?php } else {?>
              <table class="table table-striped table-bordered nowrap">
            <?php }?>
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Order No</th>
                  <th scope="col">Employee Name</th>
                  <th scope="col">Client Name</th>  
                  <th scope="col">Order Date</th>
                  <th scope="col">Net Total</th>
                  <th scope="col">Status</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($rows)>0){ $sl=1; foreach($rows as $row){?>
                  <tr>
                    <th scope="row"><?=$sl++?></th>
                    <td><?=$row->order_no?></td>
                    <td>
                      <?=$row->employee_name?><br>
                      <span class="badge bg-success"><?=$row->employee_type_prefix?></span>
                    </td>
                    <td>
                      <?=$row->client_name?><br>
                      <span class="badge bg-success"><?=$row->client_type_name?></span>  
                    </td>
                    <td><?=date('M d Y h:i A', strtotime($row->order_timestamp))?></td>
                    <td><?=number_format($row->net_total,2)?></td>
                    <td>
                      <form method="post" action="<?=url('admin/orders/change-status/')?>">
                        @csrf
                        <input type="hidden" name="order_id" value="<?=Helper::encoded($row->id)?>">
                        <select id="product-sort-option" class="form-select form-select-sm btn btn-white shadow" name="order_status" onchange="this.form.submit();">
                           <option value="">Select</option>
                           <option value="1" <?=(($row->status == 1)?'selected':'')?>>Submitted</option>
                           <option value="2" <?=(($row->status == 2)?'selected':'')?>>Approved</option>
                           <option value="3" <?=(($row->status == 3)?'selected':'')?>>Dispatch</option>
                           <option value="4" <?=(($row->status == 4)?'selected':'')?>>Billing</option>
                           <option value="5" <?=(($row->status == 5)?'selected':'')?>>Completed</option>
                        </select>
                      </form>
                    </td>
                    <td>   
                      <?php
                      if($getOrderStatus == 1){
                        $order_status_name = 'submitted';
                      } elseif($getOrderStatus == 2){
                          $order_status_name = 'approved';
                      } elseif($getOrderStatus == 3){
                          $order_status_name = 'dispatch';
                      } elseif($getOrderStatus == 4){
                          $order_status_name = 'billing';
                      } elseif($getOrderStatus == 5){
                          $order_status_name = 'completed';
                      }
                      ?>                     
                      <a href="<?=url('admin/' . $controllerRoute .'/' . $order_status_name . '/view_order_details/'.Helper::encoded($row->id))?>" class="btn btn-outline-primary btn-xs" title="ViewDetails <?=$module['title']?>" target="_blank"><i class="fa fa-info-circle"></i> View Details</a>
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