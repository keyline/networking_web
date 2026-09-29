<?php
use App\Helpers\Helper;
use App\Models\User;
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
          <div class="dt-responsive table-responsive">
            <table id="simpletable" class="table table-striped table-bordered nowrap">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Name</th>
                  <?php if($admin->company_id == 0){ ?>
                  <th scope="col">Company Name</th>
                  <?php } ?>
                  <th scope="col">Email</th>
                  <th scope="col">Phone</th>
                  <th scope="col">Question For</th>
                  <th scope="col">Subject</th>
                  <th scope="col">Enquiry Date/Time</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if($rows){ $sl=1; foreach($rows as $row){?>
                  <tr>
                    <th scope="row"><?=$sl++?></th>
                    <td><?=$row->name?></td>
                    <?php if($admin->company_id == 0){ ?>
                    <td>
                    <?php
                      $getCompany = Companies::select('id', 'name')->where('id', '=', $row->company_id)->first();
                      echo (($getCompany)?$getCompany->category_name:'');
                      ?>
                    </td>
                    <?php } ?>
                    <td><?=$row->email?></td>
                    <td><?=$row->phone?></td>
                    <td><?=$row->question_for?></td>
                    <td><?=$row->subject?></td>
                    <td><?=date_format(date_create($row->created_at), "M d, Y h:i A")?></td>
                    <td>
                      <a href="<?=url('admin/' . $controllerRoute . '/view-details/'.Helper::encoded($row->id))?>" class="btn btn-outline-info btn-sm" title="Edit <?=$module['title']?>"><i class="fa fa-eye"></i></a>
                      <a href="<?=url('admin/' . $controllerRoute . '/delete/'.Helper::encoded($row->id))?>" class="btn btn-outline-danger btn-sm" title="Delete <?=$module['title']?>" onclick="return confirm('Do You Want To Delete This <?=$module['title']?>');"><i class="fa fa-trash"></i></a>
                    </td>
                  </tr>
                <?php } }?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>