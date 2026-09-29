<?php
use App\Helpers\Helper;
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
      $user_id      = $row->user_id;
      $module_id    = json_decode($row->module_id);
    } else {
      $user_id      = '';
      $module_id    = [];
    }
    ?>
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body pt-3">
          <form method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
              <label for="user_id" class="col-md-2 col-lg-2 col-form-label">Sub User</label>
              <div class="col-md-10 col-lg-10">
                <select name="user_id" class="form-control" id="user_id" required>
                  <option value="" selected>Select Sub User</option>
                  <?php if($subUsers){ foreach($subUsers as $subUser){?>
                  <option value="<?=$subUser->id?>" <?=(($subUser->id == $user_id)?'selected':'')?>><?=$subUser->name?></option>
                  <?php } }?>
                </select>
              </div>
            </div>
            <div class="row mb-5">
              <label class="col-sm-2 col-form-label">Modules</label>
              <div class="row">
                <?php if($modules){ foreach($modules as $module){?>
                  <div class="col-md-4 col-lg-4 mt-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="module_id[]" value="<?=$module->id?>" id="module<?=$module->id?>" <?=((in_array($module->id, $module_id))?'checked':'')?>>
                      <label class="form-check-label" for="module<?=$module->id?>"><?=$module->name?></label>
                    </div>
                  </div>
                <?php } }?>
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