<?php
use App\Helpers\Helper;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Companies;
use App\Models\Employees;
use App\Models\EmployeeType;
use PHPUnit\TextUI\Help;

$controllerRoute = $module['controller_route'];
?>
<style>
  .ml-85{
    margin-left: 85px;
  }
  .bold600{font-weight:600}
  .border_bottom{
    border-bottom: 1px solid #ccc;
    padding: 10px 0;
    margin-bottom: 10px;
  }
  .orderdtl{
    /* width: 33.33%; */
    padding: 10px 0;
    /* border-right: 1px solid #ccc; */
    display: flex;
    color: #000;
  }
  .orderdtl:last-child{
    border-right: none;
  }
  /* .row.border_bottom:last-child{
    border-bottom: none;
  } */

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
    <div class="col-lg-8">
      <div class="card" style="border: 2px solid #546c3f;">
        <div class="card-body">                              
          <div class="row">
            <div class="col-md-12">
              <h3>Order Id : <span><?=$order_details->order_no?></span></h3>
            </div>
            <div class="col-md-6">
              <h5 class="mb-0">Employee Name : </h5><?=$employee_details->name?>(<?=$employee_types->name?>)
            </div>
            <div class="col-md-6">
              <h5 class="mb-0">Order For : <span><?=$client_details->name?></span></h5>
              <p class=""> <?=$client_details->address?></p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <h4>Items :</h4>
              <div class="card mb-3" style="border: 2px solid #8fba59;">
                <div class="card-body">
                  <?php                     
                    $total = 0;
                      foreach ($row as $item) {
                        // Helper::pr($item);
                        // var_dump($item['subtotal']);
                          // $total += $item['subtotal']; // Accessing array keys
                          $subtotal = str_replace(',', '', $item['subtotal']);
                          $total += (float)$subtotal;
                          ?>
                          <div class="row border_bottom">
                              <div class="col-md-6">
                                  <h4 class="mb-0"><?=$item['product_name']?></h4>
                                  <p class="mb-1"><?=$item['product_short_desc']?></p>                                  
                              </div>
                              <div class="col-md-2">
                                  <h4>Qty: <?=$item['qty']?></h4>
                              </div>
                              <div class="col-md-2">
                                  <h4>Rate: <?=$item['rate']?></h4>
                              </div>
                              <div class="col-md-2">
                                  <h4>Subtotal: <?=$item['subtotal']?></h4>
                              </div>
                              <div class="col-md-12">
                                <ul class="d-flex w-100" style="margin-bottom: 0;list-style-type: none; padding-left: 0; align-items: center;">
                                    <li class="orderdtl"><p class="bold600 mb-0 pe-1">PACKING SIZE</p>: <p class="mb-0 ps-1"><?=$item['package_size']?></p></li>
                                    <li class="pe-1 ps-1">|</li>
                                    <li class="orderdtl"><p class="bold600 mb-0 pe-1">CASE SIZE</p>: <p class="mb-0 ps-1"><?=$item['case_size']?></p></li>
                                    <li class="pe-1 ps-1">|</li>
                                    <li class="orderdtl"><p class="bold600 mb-0 pe-1">QTY. PER CASE</p>: <p class="mb-0 ps-1"><?=$item['qty_per_case']?></p></li>
                                </ul>
                              </div>
                          </div>
                  <?php } ?>
                  <!-- <div class="row border_bottom">
                    <div class="col-md-10">
                      <h4>Item name - Size</h4>
                      <p>description</p>
                    </div>
                    <div class="col-md-2">
                      <h4>qty-1</h4>
                    </div>
                  </div> -->    
                  <div class="row">
                    <div class="col-md-12">
                      <h4 style="text-align: right; margin-right: 20px;">Total : <?=$total?></h4>
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
          <div class="row mb-3">
            <?php 
              // Decode the JSON data to get the images
              $order_images = json_decode($order_details->order_images);
              // dd($order_images);
              // Check if order images exist
              if (!empty($order_images)) {
                // Loop through the images and display each one
                foreach ($order_images as $image) { ?>                  
                  <div class="col-md-3">
                    <!-- <img src="<?= env('UPLOADS_URL') .'user/'. $image ?>" class="img-thumbnail" alt="<?= $order_details->order_no ?>" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid green;"> -->
                    <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $image, $order_details->order_no, '150', '150', 'img-thumbnail', 'margin-top: 10px; border: 2px solid green;') ?>
                  </div>                  
                  <?php }
              } else { ?>
                  <!-- Display default image if no order images exist -->
                <div class="col-md-3">
                  <!-- <img src="<?= env('NO_IMAGE') ?>" alt="<?= $order_details->order_no ?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid green"> -->
                  <?= Helper::generateLightboxImage(env('NO_IMAGE'), $order_details->order_no, '150', '150', 'img-thumbnail', 'margin-top: 10px; border: 2px solid green;') ?>
                </div>
            <?php } ?>                      
          </div>
          <div class="row">
            <div class="col-md-12">
              <h4>Client Signature :</h4>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <?php               
                if (!empty($order_details->client_signature)) { ?>                                      
                    <!-- <img src="<?= env('UPLOADS_URL') .'user/'. $order_details->client_signature ?>" class="img-thumbnail" alt="<?= $order_details->order_no ?>" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid green;"> -->
                    <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $order_details->client_signature, $order_details->order_no, '150', '150', 'img-thumbnail', 'margin-top: 10px; border: 2px solid green;') ?>
                    <?php
                } else { ?>
                    <!-- Display default image if no order images exist -->                  
                    <!-- <img src="<?= env('NO_IMAGE') ?>" alt="<?= $order_details->order_no ?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid green"> -->
                    <?= Helper::generateLightboxImage(env('NO_IMAGE'), $order_details->order_no, '150', '150', 'img-thumbnail', 'margin-top: 10px; border: 2px solid green;') ?>
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