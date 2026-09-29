<?php
use App\Helpers\Helper;
use App\Models\Admin;
use App\Models\Companies;
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
          <h5 class="card-title">
            <a href="<?=url('admin/' . $controllerRoute . '/add/')?>" class="btn btn-outline-success btn-sm">Add <?=$module['title']?></a>
          </h5>
          <div class="dt-responsive table-responsive">
            <?php if(count($rows)>0){?>
              <table id="simpletable" class="table table-striped table-bordered nowrap">
            <?php } else {?>
              <table class="table table-striped table-bordered nowrap">
            <?php }?>
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <?php if($admin->company_id == 0){ ?>
                  <th scope="col">Company Name</th>
                  <?php } ?>
                  <th scope="col">Type</th>
                  <th scope="col">Title</th>
                  <th scope="col">Description</th>
                  <th scope="col">Created Info<hr>Updated Info</th> 
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($rows)>0){ $sl=1; foreach($rows as $row){?>
                  <tr>
                    <th scope="row"><?=$sl++?></th>
                    <?php if($row->company_id == 0){ ?>
                      <td>
                      <?php
                        $getCompany = Companies::select('id', 'name')->where('id', '=', $row->company_id)->first();
                        echo (($getCompany)?$getCompany->category_name:'');
                        ?>
                      </td>
                    <?php } ?>
                    <td><?=$row->type?></td>
                    <td><?=$row->title?></td>
                    <td><?=wordwrap($row->description,35,"<br>\n")?></td>
                    <td>
                      <?php
                      $getCreateUser = Admin::select('id', 'name')->where('id', '=', $row->created_by)->first();
                      $getUpdateUser = Admin::select('id', 'name')->where('id', '=', $row->updated_by)->first();                      
                      ?>
                      <?=(($getCreateUser)?$getCreateUser->name:'')?><br><?=date('M d Y h:i A', strtotime($row->created_at));?><hr>
                      <?=(($getUpdateUser)?$getUpdateUser->name. date('M d Y h:i A', strtotime($row->updated_at)):'')?>
                    </td>  
                    <td>
                      <a href="<?=url('admin/' . $controllerRoute . '/edit/'.Helper::encoded($row->id))?>" class="btn btn-outline-primary btn-sm" title="Edit <?=$module['title']?>"><i class="fa fa-edit"></i></a>
                      <a href="<?=url('admin/' . $controllerRoute . '/delete/'.Helper::encoded($row->id))?>" class="btn btn-outline-danger btn-sm" title="Delete <?=$module['title']?>" onclick="return confirm('Do You Want To Delete This <?=$module['title']?>');"><i class="fa fa-trash"></i></a>
                      <?php if($row->status){?>
                        <a href="<?=url('admin/' . $controllerRoute . '/change-status/'.Helper::encoded($row->id))?>" class="btn btn-outline-success btn-sm" title="Activate <?=$module['title']?>"><i class="fa fa-check"></i></a>
                      <?php } else {?>
                        <a href="<?=url('admin/' . $controllerRoute . '/change-status/'.Helper::encoded($row->id))?>" class="btn btn-outline-warning btn-sm" title="Deactivate <?=$module['title']?>"><i class="fa fa-times"></i></a>
                      <?php }?>
                    </td>
                  </tr>
                <?php } } else {?>
                  <tr>
                    <td colspan="7" style="text-align: center;color: red;">No Records Found !!!</td>
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