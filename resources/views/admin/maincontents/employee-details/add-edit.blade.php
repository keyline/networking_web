<?php
use App\Helpers\Helper;
$controllerRoute                = $module['controller_route'];
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.css">
<script src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.js"></script>

<style type="text/css">
    .choices__list--multiple .choices__item {
        background-color: #48974e;
        border: 1px solid #48974e;
    }
</style>
<div class="pagetitle">
  <h1><?=$page_header?></h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Home</a></li>
      <li class="breadcrumb-item active"><a href="<?=url('admin/' . $controllerRoute .'/'.$slug. '/list/')?>"><?=$module['title'].''.$slug?> List</a></li>
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
      $assign_district         = json_decode($row->assign_district);
      $name         = $row->name;
      $employee_type_id         = $row->employee_type_id;
      $email        = $row->email;
      $alt_email    = $row->alt_email;
      $phone       = $row->phone;   
      $whatsapp_no        = $row->whatsapp_no;   
      // $parentId     = $row->parent_id;
      $dob       = $row->dob;
      $doj       = $row->doj;
      $short_bio    = $row->short_bio;
      $qualification  = $row->qualification;
      $image      = $row->image;
      $address      = $row->address;
    } else {
      $assign_district         = [];
      $name         = '';
      $employee_type_id         = '';
      $email        = '';
      $alt_email    = '';
      $phone       = '';
      $whatsapp_no           = '';
      // $parentId       = '';
      $employee_type       = '';
      $short_bio      = '';
      $doj        = '';
      $dob        = '';
      $image  = '';
      $qualification  = '';
      $address  = '';
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
            <!-- <div class="row mb-3">
                <label for="employee_type" class="col-md-2 col-lg-2 col-form-label">Employee Type</label>
                <div class="col-md-10 col-lg-10">                                                                
                  <input type="text" name="employee_type" class="form-control" id="employee_type" value="<?=$slug?>" readonly>                                         
                </div>
            </div> -->
            <div class="row mb-3">
                <label for="employee_type_id" class="col-md-2 col-lg-2 col-form-label">Employee Type</label>
                <div class="col-md-10 col-lg-10">
                  <select name="employee_type_id" class="form-control" id="employee_type_id" required>
                    @if ($empTypes)                      
                      @foreach ($empTypes as $empType)
                        <option value="{{ $empType->id }}" <?=(($empType->id == $employee_type_id)?'selected':'')?>>{{ $empType->name }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <label for="choices-multiple-remove-button" class="col-md-2 col-lg-2 col-form-label">Assign District</label>
                <div class="col-md-10 col-lg-10">
                  <select name="assign_district[]" class="form-control" id="choices-multiple-remove-button" multiple required>
                    @if ($districts)                      
                      @foreach ($districts as $district)
                        <option value="{{ $district->id }}" <?=((in_array($district->id, $assign_district))?'selected':'')?>>{{ $district->name }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
            </div> 
            <div class="row mb-3">
              <label for="email" class="col-md-2 col-lg-2 col-form-label">Email</label>
              <div class="col-md-10 col-lg-10">
                <input type="email" name="email" class="form-control" id="email" value="<?=$email?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="alt_email" class="col-md-2 col-lg-2 col-form-label">Alternate Email</label>
              <div class="col-md-10 col-lg-10">
                <input type="alt_email" name="alt_email" class="form-control" id="alt_email" value="<?=$alt_email?>">
              </div>
            </div>            
            <div class="row mb-3">
              <label for="phone" class="col-md-2 col-lg-2 col-form-label">Phone Number</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="phone" class="form-control" maxlength="10" id="phone" value="<?=$phone?>">
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
              <label for="password" class="col-md-2 col-lg-2 col-form-label">Password</label>
              <div class="col-md-10 col-lg-10">
                <input type="password" name="password" class="form-control" id="password" autocomplete="off" value="" <?=((!empty($row))?'':'required')?>>
                <?php if($row){?><small class="text-info">* Leave blank if you don't want to change password</small><br><?php }?>
              </div>
            </div>
            <div class="row mb-3">
                <label for="short_bio" class="col-md-2 col-lg-2 col-form-label">Short Bio</label>
                <div class="col-md-10 col-lg-10">
                    <textarea name="short_bio" class="form-control"  rows="5"><?= $short_bio ?></textarea>               
                </div>
            </div>
            <div class="row mb-3">
                <label for="address" class="col-md-2 col-lg-2 col-form-label">address</label>
                <div class="col-md-10 col-lg-10">
                    <textarea name="address" class="form-control"  rows="5"><?= $address ?></textarea>               
                </div>
            </div>
            <div class="row mb-3">
              <label for="dob" class="col-md-2 col-lg-2 col-form-label">Date Of Birth</label>
              <div class="col-md-10 col-lg-10">
                <input type="date" name="dob" class="form-control" id="dob" value="<?= $dob ?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="doj" class="col-md-2 col-lg-2 col-form-label">Date of Joining</label>
              <div class="col-md-10 col-lg-10">
                <input type="date" name="doj" class="form-control" id="doj" value="<?= $doj ?>">
              </div>
            </div>  
            <div class="row mb-3">
              <label for="qualification" class="col-md-2 col-lg-2 col-form-label">Qualification</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="qualification" class="form-control" id="qualification" value="<?=$qualification?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="image" class="col-md-2 col-lg-2 col-form-label">Profile Image</label>
              <div class="col-md-10 col-lg-10">
                <input type="file" name="image" class="form-control" id="image">
                <small class="text-info">* Only JPG, JPEG, ICO, SVG, PNG, WEBP files are allowed</small><br>
                <?php if($image != ''){?>
                  <img src="<?=env('UPLOADS_URL').$image?>" class="img-thumbnail" alt="<?=$name?>" style="width: 150px; height: 150px; margin-top: 10px;">
                <?php } else {?>
                  <img src="<?=env('NO_IMAGE')?>" alt="<?=$name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                <?php }?>                                
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
<script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
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
<script type="text/javascript">
  $(document).ready(function(){    
    var multipleCancelButton = new Choices('#choices-multiple-remove-button', {
        removeItemButton: true,
        maxItemCount:30,
        searchResultLimit:30,
        renderChoiceLimit:30
    });
  });
</script>