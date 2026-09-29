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
      $name           = $row->name;
      $short_desc     = $row->short_desc;
      $product_image  = $row->product_image;
      $markup_price   = $row->markup_price;
      $retail_price   = $row->retail_price;
      $product_catId  = $row->category_id;
      $unitId         = $row->unit_id;
      $sizeId         = $row->size_id;
    } else {
      $name           = '';
      $short_desc     = '';
      $product_image  = '';
      $markup_price   = '';
      $retail_price   = '';
      $product_catId  = '';
      $unitId         = '';
      $sizeId         = '';
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
              <label for="product_category" class="col-md-2 col-lg-2 col-form-label">Product Category</label>
              <div class="col-md-10 col-lg-8">                                                                                                              
              <select name="product_category" class="form-control" id="product_category" required>
                  <option value="" selected disabled>Select</option>
                  @if ($product_cat)
                      @foreach ($product_cat as $data)
                          <option value="{{ $data->id }}" @selected($data->id == $product_catId)>
                              {{ $data->category_name }}</option>
                      @endforeach
                  @endif
              </select>                           
              </div>
            </div>
            <div class="row mb-3">
              <label for="unit_id" class="col-md-2 col-lg-2 col-form-label">Product Unit</label>
              <div class="col-md-10 col-lg-8">                                                                                                              
              <select name="unit_id" class="form-control" id="unit_id" required>
                  <option value="" selected disabled>Select</option>
                  @if ($unit)
                      @foreach ($unit as $data)
                          <option value="{{ $data->id }}" @selected($data->id == $unitId)>
                              {{ $data->name }}</option>
                      @endforeach
                  @endif
              </select>                           
              </div>
            </div>
            <div class="row mb-3">
              <label for="size_id" class="col-md-2 col-lg-2 col-form-label">Product Size</label>
              <div class="col-md-10 col-lg-8">                                                                                                              
              <select name="size_id" class="form-control" id="size_id" required>
                  <option value="" selected disabled>Select</option>
                  @if ($size)
                      @foreach ($size as $data)
                          <option value="{{ $data->id }}" @selected($data->id == $sizeId)>
                              {{ $data->name }}</option>
                      @endforeach
                  @endif
              </select>                           
              </div>
            </div>
            <div class="row mb-3">
                <label for="short_description" class="col-md-2 col-lg-2 col-form-label">Short Description</label>
                <div class="col-md-10 col-lg-10">
                    <textarea name="short_desc" class="form-control"  rows="5" maxlength="75"><?= $short_desc ?></textarea>                    
                </div>
            </div>
            <div class="row mb-3">
              <label for="product_image" class="col-md-2 col-lg-2 col-form-label">Product Image</label>
              <div class="col-md-10 col-lg-10">
                <input type="file" name="product_image" class="form-control" id="product_image">
                <small class="text-info">* Only JPG, JPEG, ICO, SVG, PNG, WEBP files are allowed</small><br>
                <?php if($product_image != ''){?>
                  <img src="<?=env('UPLOADS_URL').'product/'.$product_image?>" class="img-thumbnail" alt="<?=$name?>" style="width: 150px; height: 150px; margin-top: 10px;">
                <?php } else {?>
                  <img src="<?=env('NO_IMAGE')?>" alt="<?=$name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                <?php }?>                                
              </div>
            </div>
            <div class="row mb-3">
              <label for="markup_price" class="col-md-2 col-lg-2 col-form-label">Markup Price</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="markup_price" class="form-control" id="markup_price" value="<?=$markup_price?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="retail_price" class="col-md-2 col-lg-2 col-form-label">Retail Price</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="retail_price" class="form-control" id="retail_price" value="<?=$retail_price?>">
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