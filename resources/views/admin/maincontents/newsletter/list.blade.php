<?php
use App\Models\User;
use App\Helpers\Helper;
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
            <table id="simpletable" class="table table-striped table-bordered nowrap">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">To</th>
                  <th scope="col">Users</th>
                  <?php if($admin->company_id == 0){ ?>
                  <th scope="col">Company Name</th>
                  <?php } ?>
                  <th scope="col">Title</th>
                  <th scope="col">Description</th>
                  <th scope="col">Attachment</th>
                  <th scope="col">Send</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($rows) > 0){ $sl=1; foreach($rows as $key =>$value){?>
                  <tr>
                    <td scope="row"><?=$sl++?></td>                         
                    <td>
                      <?php
                      $to_users   = $value->to_users;
                      if($to_users == 0){
                        echo 'All';
                      } elseif($to_users == 1){
                        echo 'Subscriber';
                      } elseif($to_users == 2){
                        echo 'User';
                      }
                      ?>
                    </td>
                    <td>
                      <div class="row">
                        <?php
                        $users = json_decode($value->users);
                        if(!empty($users)){ for($u=0;$u<count($users);$u++){
                        ?>
                          <div class="col-md-4">
                            <span class="badge bg-primary"><?=$users[$u]?></span>
                          </div>
                        <?php } }?>
                      </div>
                    </td>
                    <td>{{ $value->title }}</td>
                    <?php if($admin->company_id == 0){ ?>
                    <td>
                    <?php
                      $getCompany = Companies::select('id', 'name')->where('id', '=', $row->company_id)->first();
                      echo (($getCompany)?$getCompany->category_name:'');
                      ?>
                    </td>
                    <?php } ?>
                    <td>{{ $value->description }}</td>
                    <td>
                      <?php if($value->attachment != ''){?>
                        <img src="<?=env('UPLOADS_URL').'newsletter/'.$value->attachment?>" class="img-thumbnail" alt="<?=$value->title?>" style="width: 150px; height: 150px; margin-top: 10px;">
                      <?php } else {?>
                        <img src="<?=env('NO_IMAGE')?>" alt="<?=$value->title?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                      <?php }?>
                    </td>
                    <td>
                        <?php if($value->is_send){?>
                            <span class="badge bg-success">YES</span>
                            <p><?=date_format(date_create($value->updated_at), "M d, Y h:i A")?></p>
                        <?php } else {?>
                            <span class="badge bg-danger">NO</span>
                        <?php }?>
                    </td>
                    <td>
                        <?php if(!$value->is_send){?>
                          <a href="<?=url('admin/' . $controllerRoute . '/edit/'.Helper::encoded($value->id))?>" class="btn btn-outline-primary btn-sm" title="Edit <?=$module['title']?>"><i class="fa fa-edit"></i></a>
                          <a href="<?=url('admin/' . $controllerRoute . '/send/'.Helper::encoded($value->id))?>" class="btn btn-outline-info btn-sm" title="Send" onclick="return confirm('Do you want to send this notifications ?');"><i class="fa fa-paper-plane"></i></a>
                          <?php if($value->status){?>
                            <a href="<?=url('admin/' . $controllerRoute . '/change-status/'.Helper::encoded($value->id))?>" class="btn btn-outline-success btn-sm" title="Activate <?=$module['title']?>"><i class="fa fa-check"></i></a>
                          <?php } else {?>
                            <a href="<?=url('admin/' . $controllerRoute . '/change-status/'.Helper::encoded($value->id))?>" class="btn btn-outline-warning btn-sm" title="Deactivate <?=$module['title']?>"><i class="fa fa-times"></i></a>
                          <?php }?>
                        <?php }?>
                        <a href="<?=url('admin/' . $controllerRoute . '/delete/'.Helper::encoded($value->id))?>" class="btn btn-outline-danger btn-sm" title="Delete <?=$module['title']?>" onclick="return confirm('Do You Want To Delete This <?=$module['title']?>');"><i class="fa fa-trash"></i></a>
                    </td>
                  </tr>
                <?php } } else {?>
                  <tr>
                    <td colspan="8" style="text-align: center;color: red;">No Records Found !!!</td>
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