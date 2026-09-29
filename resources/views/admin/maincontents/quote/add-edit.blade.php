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
      $type             = $row->type;
      $description      = $row->description;
    } else {
      $type             = '';
      $description      = '';
    }
    ?>
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body pt-3">
          <form method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
                <label for="type" class="col-md-2 col-lg-2 col-form-label">Type</label>
                <div class="col-md-10 col-lg-10">                                                                
                  <select name="type" class="form-control" id="type" required>
                      <option value="" selected disabled>Select</option>                      
                      <option value="IN" <?=(($type == 'IN')?'selected':'')?>>PUNCH-IN</option>
                      <option value="OUT" <?=(($type == 'OUT')?'selected':'')?>>PUNCH-OUT</option>
                  </select>                         
                </div>
            </div>
            <div class="row mb-3">
              <label for="description" class="col-md-2 col-lg-2 col-form-label">Quote</label>
              <div class="col-md-10 col-lg-10">
                <textarea name="description" class="form-control" id="description" rows="5" required><?=$description?></textarea>
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
  // Common function to prevent spaces
  function disallowSpace(event) {
      if (event.key === ' ') {
          event.preventDefault(); // Prevent space key
      }
  }

  function removeSpacesOnInput(event) {
      event.target.value = event.target.value.replace(/\s/g, ''); // Remove spaces
  }

  // Select all inputs with the class 'no-space'
  const textboxes = document.querySelectorAll('.no-space');

  // Attach event listeners to each textbox
  textboxes.forEach((textbox) => {
      textbox.addEventListener('keydown', disallowSpace);
      textbox.addEventListener('input', removeSpacesOnInput);
  });
</script>