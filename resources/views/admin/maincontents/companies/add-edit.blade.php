<?php
use App\Helpers\Helper;
use App\Models\Admin;

$controllerRoute = $module['controller_route'];
?>
<div class="pagetitle">
  <h1><?=$page_header?></h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Home</a></li>
      <li class="breadcrumb-item active"><a href="<?=url('admin/' . $controllerRoute . '/list/')?>"><?=$module['title']?> List</a></li>
      <li class="breadcrumb-item active"><?=$page_header?></li>
    </ol>
  </nav>
</div><!-- End Page Title -->
<section class="section profile">
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
    <?php
    if($row){
      $name               = $row->name;
      $email              = $row->email;
      $alternate_email    = $row->alternate_email;
      $phone              = $row->phone;
      $whatsapp_no        = $row->whatsapp_no;
      $address         = $row->address;
      $logo      = $row->logo;
      $start_date       = $row->start_date;
      $end_date       = $row->end_date;
      $license_no      = $row->license_no;
      $last_renewal_date      = $row->last_renewal_date; 
      $getAdmin = Admin::where('company_id', '=', $row->id)->first();  
      $username = $getAdmin->email;               
    } else {
      $name           = '';
      $email           = '';
      $alternate_email           = '';
      $phone           = '';
      $whatsapp_no           = '';
      $address     = '';
      $logo  = '';
      $start_date   = '';
      $end_date   = '';     
      $last_renewal_date  = '';
      $license_no  = '';
      $username  = '';
      $getAdmin  = '';
    }
    ?>
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body pt-3">
          @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
          </div>
          @endif
          <form method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
              <label for="name" class="col-md-2 col-lg-2 col-form-label">Name</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="name" class="form-control" id="name" value="<?=$name?>" required>
              </div>
            </div>            
            <div class="row mb-3">
              <label for="email" class="col-md-2 col-lg-2 col-form-label">Email</label>
              <div class="col-md-10 col-lg-10">
                <input type="email" name="email" class="form-control" id="email" value="<?=$email?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="alternate_email" class="col-md-2 col-lg-2 col-form-label">Alternate Email</label>
              <div class="col-md-10 col-lg-10">
                <input type="alternate_email" name="alternate_email" class="form-control" id="alternate_email" value="<?=$alternate_email?>" >
              </div>
            </div>
            <div class="row mb-3">
              <label for="phone" class="col-md-2 col-lg-2 col-form-label">phone</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="phone" class="form-control" id="phone" value="<?=$phone?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-2 col-lg-2"></div>
              <div class="col-md-10 col-lg-10">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="sameAsPhone">
                  <label for="sameAsPhone" class="form-check-label">Same as Phone Number</label>
                </div>
              </div>
            </div>
            <div class="row mb-3">
              <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">Whatsapp No</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="whatsapp_no" class="form-control" id="whatsapp_no" value="<?=$whatsapp_no?>" >
              </div>
            </div>                        
            <div class="row mb-3">
                <label for="address" class="col-md-2 col-lg-2 col-form-label">Address</label>
                <div class="col-md-10 col-lg-10">
                    <textarea name="address" class="form-control"  rows="5"><?= $address ?></textarea>                    
                </div>
            </div>
            <div class="row mb-3">
              <label for="logo" class="col-md-2 col-lg-2 col-form-label">Company Logo</label>
              <div class="col-md-10 col-lg-10">
                <input type="file" name="logo" class="form-control" id="logo">
                <small class="text-info">* Only JPG, JPEG, ICO, SVG, PNG, WEBP files are allowed</small><br>
                <?php if($logo != ''){?>
                  <img src="<?=env('UPLOADS_URL').$logo?>" class="img-thumbnail" alt="<?=$name?>" style="width: 150px; height: 150px; margin-top: 10px;">
                <?php } else {?>
                  <img src="<?=env('NO_IMAGE')?>" alt="<?=$name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                <?php }?>                                
              </div>
            </div>
            <div class="row mb-3">
              <label for="start_date" class="col-md-2 col-lg-2 col-form-label">Start Date</label>
              <div class="col-md-10 col-lg-10">
                <input type="date" name="start_date" class="form-control" id="start_date" value="<?= $start_date ?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="end_date" class="col-md-2 col-lg-2 col-form-label">End Date</label>
              <div class="col-md-10 col-lg-10">
                <input type="date" name="end_date" class="form-control" id="end_date" value="<?= $end_date ?>">
              </div>
            </div>            
            <div class="row mb-3">
              <label for="license_no" class="col-md-2 col-lg-2 col-form-label">License No</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="license_no" class="form-control" id="license_no" value="<?= $license_no ?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="last_renewal_date" class="col-md-2 col-lg-2 col-form-label">Last Renewal Date</label>
              <div class="col-md-10 col-lg-10">
                <input type="date" name="last_renewal_date" class="form-control" id="last_renewal_date" value="<?= $last_renewal_date ?>">
              </div>
            </div>
            
            <div class="row mb-3">
              <label for="username" class="col-md-2 col-lg-2 col-form-label">Username</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="username" class="form-control" id="username" value="<?=$username?>" required>
              </div>
            </div>  
            <div class="row mb-3">
              <label for="password" class="col-md-2 col-lg-2 col-form-label">Password</label>
              <div class="col-md-10 col-lg-10">
                <input type="password" name="password" class="form-control" id="password" value="" <?=((!empty($getAdmin))?'':'required')?>>
                <?php if($getAdmin){?><small class="text-info">* Leave blank if you don't want to change password</small><br><?php }?>
              </div>
            </div>                        
            <div class="text-center">
              <button type="submit" class="btn btn-primary"><?=(($row)?'Save':'Add')?></button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
  document.getElementById('sameAsPhone').addEventListener('change', function () {
    const phoneField = document.getElementById('phone');
    const whatsappField = document.getElementById('whatsapp_no');
    
    if (this.checked) {
      whatsappField.value = phoneField.value;
      whatsappField.setAttribute('readonly', true); // Make the field readonly when autofilled
    } else {
      whatsappField.value = '';
      whatsappField.removeAttribute('readonly'); // Allow editing when unchecked
    }
  });

  // Ensure WhatsApp number updates when phone number changes and checkbox is checked
  document.getElementById('phone').addEventListener('input', function () {
    const sameAsPhoneCheckbox = document.getElementById('sameAsPhone');
    const whatsappField = document.getElementById('whatsapp_no');
    if (sameAsPhoneCheckbox.checked) {
      whatsappField.value = this.value;
    }
  });
</script>