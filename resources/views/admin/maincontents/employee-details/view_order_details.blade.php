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
  .ml-85{
    margin-left: 85px;
  }
  .border_bottom{
    border-bottom: 2px solid #ccc;
    padding: 20px;
  }
  /* .row.border_bottom:last-child{
    border-bottom: none;
  } */
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
  <div class="row justify-content-center">
    <!-- <div class="col-xl-12">
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
    </div> -->
    <div class="col-lg-12">
      <div class="card" style="border: 2px solid #546c3f;">
        <div class="card-body">                              
          <div class="row">
            <div class="col-md-6">
              <h4>Order Id : <span><?=$order_details->order_no?></span></h4>
              <h4>Employee Name : <span><?=$employee_details->name?> (<?=$employee_types->name?>)</span></h4>
            </div>
            <div class="col-md-6">
              <h4>Order For : <span><?=$client_details->name?></span> (<?=$order_client_types->name;?>)</h4>
              <p class="ml-85"> <?=$client_details->address?></p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <h4>Items :</h4>
              <div class="card mb-3" style="border: 2px solid #8fba59;">
                <div class="card-body">
                  <?php 
                    $total = 0;
                    foreach ($row as $rows) {  
                      $total += $rows->subtotal;                   
                     ?>
                  <div class="row border_bottom">
                    <div class="col-md-6">
                      <h4><?=$rows->product_name .' - '. $rows->size_name .''. $rows->unit_name?></h4>
                      <p><?=$rows->product_short_desc?></p>
                    </div>
                    <div class="col-md-2">
                      <h4>Qty: <?=$rows->qty?></h4>
                    </div>
                    <div class="col-md-2">
                      <h4>Rate: <?=number_format($rows->rate, 2)?></h4>
                    </div>
                    <div class="col-md-2">
                      <h4>Subtotal: <?=number_format($rows->subtotal, 2)?></h4>
                    </div>
                  </div>
                  <?php } ?>                  
                  <div class="row">
                    <div class="col-md-12">
                      <h4 style="text-align: right; margin-right: 100px;">Total : <?=number_format($total, 2)?></h4>
                    </div>                    
                  </div>         
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <h4>Order Images :</h4>
            </div>
          </div>
          <div class="row mb-3 justify-content-center">
            <?php 
              // Decode the JSON data to get the images
              $order_images = json_decode($order_details->order_images);
              // dd($order_images);
              // Check if order images exist
              if (!empty($order_images)) {
                // Loop through the images and display each one
                foreach ($order_images as $image) { ?>                  
                  <div class="col-md-3">
                    <img src="<?= env('UPLOADS_URL'). 'user/' . $image ?>" class="img-thumbnail" alt="<?= $order_details->order_no ?>" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid green;">
                  </div>                  
                  <?php }
              } else { ?>
                  <!-- Display default image if no order images exist -->
                <div class="col-md-3">
                  <img src="<?= env('NO_IMAGE') ?>" alt="<?= $order_details->order_no ?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid green">
                </div>
            <?php } ?>                      
          </div>
          <div class="row">
            <div class="col-md-12">
              <h4>Client Signature :</h4>
            </div>
          </div>
          <div class="row justify-content-center">
            <div class="col-md-6">
              <?php               
                if (!empty($order_details->client_signature)) { ?>                                      
                    <img src="<?= env('UPLOADS_URL'). 'user/' . $order_details->client_signature ?>" class="img-thumbnail" alt="<?= $order_details->order_no ?>" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid green;">
                    <?php
                } else { ?>
                    <!-- Display default image if no order images exist -->                  
                    <img src="<?= env('NO_IMAGE') ?>" alt="<?= $order_details->order_no ?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid green">
              <?php } ?>   
            </div>                   
          </div>
        </div>
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