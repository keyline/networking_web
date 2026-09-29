<?php
use App\Helpers\Helper;
$controllerRoute                = $module['controller_route'];
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
      $name         = $row->name;
      $email        = $row->email;
      $mobile       = $row->mobile;
      $roleId       = $row->role_id;
    } else {
      $name         = '';
      $email        = '';
      $mobile       = '';
      $roleId       = '';
    }
    ?>
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body pt-3">
          <form method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
              <label for="name" class="col-md-2 col-lg-2 col-form-label">Name</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="name" class="form-control" id="name" value="<?=$name?>" required>
              </div>
            </div>
            <div class="row mb-3">
                <label for="role" class="col-md-2 col-lg-2 col-form-label">Role</label>
                <div class="col-md-10 col-lg-10">                                                                
                  <select name="role" class="form-control" id="role" required>
                      <option value="" selected disabled>Select</option>                      
                      @if ($role)                      
                          @foreach ($role as $data)
                              <option value="{{ $data->id }}" @selected($data->id == $roleId)>
                                  {{ $data->name }}</option>
                          @endforeach
                      @endif
                  </select>                         
                </div>
            </div> 
            <div class="row mb-3">
              <label for="email" class="col-md-2 col-lg-2 col-form-label">Email</label>
              <div class="col-md-10 col-lg-10">
                <input type="email" name="email" class="form-control" id="email" value="<?=$email?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="mobile" class="col-md-2 col-lg-2 col-form-label">Mobile</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="mobile" class="form-control" id="mobile" value="<?=$mobile?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="password" class="col-md-2 col-lg-2 col-form-label">Password</label>
              <div class="col-md-10 col-lg-10">
                <input type="password" name="password" class="form-control" id="password" value="" <?=((!empty($row))?'':'required')?>>
                <?php if($row){?><small class="text-info">* Leave blank if you don't want to change password</small><br><?php }?>
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