<?php
$user_type = session('type');
?>
<style>
  .settings-page { --sp-primary: #397ef6; --sp-text: #263650; --sp-muted: #738097; --sp-border: #e5eaf2; }
  .settings-page .page-header { margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px solid #edf1f6; }
  .settings-page .breadcrumb { margin-bottom: 7px; font-size: 12px; }
  .settings-page .page-header-title { margin-bottom: 4px; color: #17233c; font-size: 24px; font-weight: 750; letter-spacing: -.02em; }
  .settings-page .page-subtitle { margin: 0; color: var(--sp-muted); font-size: 13px; }
  .settings-page .settings-layout { align-items: flex-start; }
  .settings-page .settings-card { overflow: hidden; border: 1px solid var(--sp-border); border-radius: 12px; box-shadow: 0 8px 24px rgba(23, 35, 60, .05); }
  .settings-page .settings-card > .card-body { display: grid; grid-template-columns: 220px minmax(0, 1fr); padding: 0; }
  .settings-page .settings-nav { display: flex; flex-direction: column; gap: 3px; min-height: 690px; padding: 15px 12px; border: 0; border-right: 1px solid var(--sp-border); background: #fafbfd; }
  .settings-page .settings-nav::before { display: block; margin: 1px 10px 10px; color: #96a0af; font-size: 10px; font-weight: 750; letter-spacing: .07em; text-transform: uppercase; content: 'Configuration'; }
  .settings-page .settings-nav .nav-item { width: 100%; }
  .settings-page .settings-nav .nav-link { display: flex; align-items: center; gap: 10px; width: 100%; min-height: 40px; padding: 9px 11px; color: #53637b; border: 0; border-radius: 7px; background: transparent; font-size: 12px; font-weight: 650; text-align: left; white-space: nowrap; }
  .settings-page .settings-nav .nav-link i { width: 16px; color: #8d99aa; font-size: 13px; text-align: center; }
  .settings-page .settings-nav .nav-link:hover { color: #2e69ce; background: #f0f5fd; }
  .settings-page .settings-nav .nav-link.active { color: var(--sp-primary); background: #eaf2ff; box-shadow: inset 3px 0 var(--sp-primary); }
  .settings-page .settings-nav .nav-link.active i { color: var(--sp-primary); }
  .settings-page .settings-content { min-width: 0; padding: 22px 26px 26px !important; }
  .settings-page .settings-content > .tab-pane { padding-top: 0 !important; }
  .settings-page .settings-content form { max-width: 920px; }
  .settings-page .settings-content form::before { display: block; margin-bottom: 21px; padding-bottom: 13px; color: var(--sp-text); border-bottom: 1px solid #edf1f6; font-size: 15px; font-weight: 750; }
  .settings-page #tab1 form::before { content: 'Administrator profile & portal logo'; }
  .settings-page #tab2 form::before { content: 'Portal information & integrations'; }
  .settings-page #tab3 form::before { content: 'Account security'; }
  .settings-page #tab4 form::before { content: 'Outgoing email configuration'; }
  .settings-page #tab5 form::before { content: 'SMS gateway configuration'; }
  .settings-page #tab6 form::before { content: 'Footer content & navigation'; }
  .settings-page #tab7 form::before { content: 'Search engine information'; }
  .settings-page #tab8 form::before { content: 'Payment gateway configuration'; }
  .settings-page #tab9 form::before { content: 'Transactional email templates'; }
  .settings-page #tab10 form::before { content: 'Portal colors & appearance'; }
  .settings-page #tab11 form::before { content: 'Membership plans & renewal rules'; }
  .settings-page .settings-content .row.mb-3 { margin-bottom: 14px !important; }
  .settings-page .settings-content .col-form-label, .settings-page .settings-content .control-label { padding-top: 9px; color: #44536a; font-size: 12px; font-weight: 700; }
  .settings-page .settings-content .form-control, .settings-page .settings-content .form-select { min-height: 41px; padding: 8px 11px; color: #33435c; border: 1px solid #dce3ed; border-radius: 7px; background-color: #fff; font-size: 13px; box-shadow: none; }
  .settings-page .settings-content textarea.form-control { min-height: 92px; line-height: 1.5; }
  .settings-page .settings-content .form-control:focus, .settings-page .settings-content .form-select:focus { border-color: var(--sp-primary); box-shadow: 0 0 0 3px rgba(57, 126, 246, .1); }
  .settings-page .settings-content input[type='file'] { padding: 6px; }
  .settings-page .settings-content input[type='color'] { width: 92px; min-height: 42px; padding: 4px; }
  .settings-page .settings-content small, .settings-page .settings-content .text-muted { color: #8792a3 !important; font-size: 11px; line-height: 1.5; }
  .settings-page .settings-content .img-thumbnail { max-width: 190px !important; max-height: 120px !important; padding: 8px; border-color: #e3e8f0; border-radius: 9px; object-fit: contain; }
  .settings-page .settings-content .input-group-text { color: #5f6f86; border-color: #dce3ed; background: #f6f8fb; font-size: 12px; }
  .settings-page .settings-content .form-check { padding: 12px 14px 12px 38px; border: 1px solid #e7ebf2; border-radius: 8px; background: #fbfcfe; }
  .settings-page .settings-content .form-switch { padding: 0 0 0 42px; border: 0; background: transparent; }
  .settings-page .settings-content .form-check-input:checked { border-color: var(--sp-primary); background-color: var(--sp-primary); }
  .settings-page .settings-content .text-center { display: flex; justify-content: flex-end; margin-top: 23px; padding-top: 17px; border-top: 1px solid #edf1f6; }
  .settings-page .settings-content .btn-primary { min-width: 126px; padding: 9px 16px; border-color: var(--sp-primary); border-radius: 7px; background: var(--sp-primary); font-size: 12px; font-weight: 700; box-shadow: 0 5px 12px rgba(57, 126, 246, .2); }
  .settings-page .template-delivery { display: grid; grid-template-columns: 1fr 1.5fr; gap: 14px; margin-bottom: 18px; padding: 16px; border: 1px solid #e6ebf2; border-radius: 10px; background: #f9fbfd; }
  .settings-page .template-delivery label { display: block; margin-bottom: 6px; color: #45546b; font-size: 11px; font-weight: 750; }
  .settings-page .template-manager { display: grid; grid-template-columns: 205px minmax(0, 1fr); overflow: hidden; border: 1px solid #e4e9f1; border-radius: 10px; }
  .settings-page .template-list { padding: 12px; border-right: 1px solid #e4e9f1; background: #f8fafc; }
  .settings-page .template-list-title { margin: 2px 6px 10px; color: #909bad; font-size: 9px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
  .settings-page .template-selector { display: flex; align-items: center; gap: 9px; width: 100%; margin-bottom: 4px; padding: 10px; color: #56667d; border: 0; border-radius: 7px; background: transparent; font-size: 11px; font-weight: 700; text-align: left; }
  .settings-page .template-selector i { display: grid; place-items: center; width: 26px; height: 26px; flex: 0 0 26px; border-radius: 6px; background: #edf1f6; color: #8390a4; }
  .settings-page .template-selector:hover { background: #f0f5fc; color: #326dcc; }
  .settings-page .template-selector.active { background: #eaf2ff; color: var(--sp-primary); box-shadow: inset 3px 0 var(--sp-primary); }
  .settings-page .template-selector.active i { background: #fff; color: var(--sp-primary); }
  .settings-page .template-workspace { min-width: 0; padding: 17px; background: #fff; }
  .settings-page .template-editor-panel { display: none; }
  .settings-page .template-editor-panel.active { display: block; }
  .settings-page .template-editor-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 13px; }
  .settings-page .template-editor-head h4 { margin: 0 0 3px; color: #2e3d56; font-size: 14px; font-weight: 750; }
  .settings-page .template-editor-head p { margin: 0; color: #8490a2; font-size: 10.5px; }
  .settings-page .preview-toggle { display: inline-flex; align-items: center; gap: 6px; padding: 7px 10px; color: #53657d; border: 1px solid #dce3ed; border-radius: 6px; background: #fff; font-size: 10px; font-weight: 750; }
  .settings-page .preview-toggle:hover { color: var(--sp-primary); border-color: #b8cef5; background: #f7faff; }
  .settings-page .variable-bar { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; padding: 9px 10px; border-radius: 7px; background: #f6f8fb; }
  .settings-page .variable-bar > span { margin-right: 2px; color: #7b8799; font-size: 9.5px; font-weight: 750; text-transform: uppercase; }
  .settings-page .variable-chip { padding: 4px 7px; color: #45628c; border: 1px solid #dce5f2; border-radius: 5px; background: #fff; font-family: monospace; font-size: 10px; cursor: pointer; }
  .settings-page .variable-chip:hover { color: #fff; border-color: var(--sp-primary); background: var(--sp-primary); }
  .settings-page .template-workspace .ck-editor { border-radius: 7px; }
  .settings-page .template-workspace .ck-editor__editable { min-height: 330px; max-height: 520px; }
  .settings-page .email-preview { display: none; min-height: 360px; padding: 24px; overflow: auto; border: 1px solid #e1e6ee; border-radius: 7px; background: #f3f5f8; }
  .settings-page .email-preview.active { display: block; }
  .settings-page .email-preview-paper { max-width: 650px; min-height: 280px; margin: auto; padding: 28px; border: 1px solid #e4e8ee; border-radius: 6px; background: #fff; box-shadow: 0 5px 18px rgba(23,35,60,.07); }
  .settings-page .template-note { display: flex; gap: 8px; margin-top: 12px; padding: 10px; color: #6e7d92; border-radius: 7px; background: #fff8e8; font-size: 10.5px; line-height: 1.5; }
  .settings-page .alert { border-radius: 9px; font-size: 13px; }
  @media (max-width: 1199px) { .settings-page .settings-card > .card-body { grid-template-columns: 185px minmax(0, 1fr); } .settings-page .settings-nav { min-height: 0; } }
  @media (max-width: 991px) { .settings-page .settings-card > .card-body { display: block; } .settings-page .settings-nav { flex-direction: row; overflow-x: auto; padding: 10px; border-right: 0; border-bottom: 1px solid var(--sp-border); } .settings-page .settings-nav::before { display: none; } .settings-page .settings-nav .nav-item { width: auto; } .settings-page .settings-nav .nav-link { width: auto; } .settings-page .template-manager { grid-template-columns: 170px minmax(0, 1fr); } }
  @media (max-width: 767px) { .settings-page .template-delivery { grid-template-columns: 1fr; } .settings-page .template-manager { display: block; } .settings-page .template-list { display: flex; overflow-x: auto; border-right: 0; border-bottom: 1px solid #e4e9f1; } .settings-page .template-list-title { display: none; } .settings-page .template-selector { flex: 0 0 auto; width: auto; white-space: nowrap; } }
  @media (max-width: 575px) { .settings-page .settings-content { padding: 18px 15px 22px !important; } .settings-page .settings-content .col-form-label { padding-bottom: 5px; } }
</style>
<div class="settings-page">
<!-- Page Header -->
<div class="page-header">
  <div class="row align-items-end">
    <div class="col-sm mb-2 mb-sm-0">
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Home</a></li>
          <li class="breadcrumb-item active"><?=$page_header?></li>
        </ol>
      </nav>
      <h1 class="page-header-title"><?=$page_header?></h1>
      <p class="page-subtitle">Manage your portal identity, communications, appearance, and membership preferences.</p>
    </div>
    <!-- End Col -->
  </div>
  <!-- End Row -->
</div>
<!-- End Page Header -->

<div class="row settings-layout">
  <div class="col-lg-12">
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
    @if($errors->any())
      <div class="alert alert-danger border-0" role="alert">
        <strong>The settings could not be saved:</strong>
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
  <div class="col-lg-12">
    <div class="d-grid gap-3 gap-lg-5">
      <div class="card settings-card">
        <div class="card-body pt-3">
          <!-- Bordered Tabs -->
          <ul class="nav nav-tabs nav-tabs-bordered settings-nav">
            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1"><i class="fa fa-user"></i>Profile</button>
            </li>
            <?php if($user_type == 'ma'){?>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab2"><i class="fa fa-sliders-h"></i>General</button>
            </li>
            <?php }?>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab3"><i class="fa fa-lock"></i>Password</button>
            </li>
            <?php if($user_type == 'ma'){?>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab4"><i class="fa fa-envelope"></i>Email</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab5"><i class="fa fa-comment-alt"></i>SMS</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab6"><i class="fa fa-columns"></i>Footer</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab7"><i class="fa fa-search"></i>SEO</button>
            </li>
            <!-- <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab8">Payment</button>
            </li> -->
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab9"><i class="fa fa-file-alt"></i>Email Templates</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab10"><i class="fa fa-palette"></i>Appearance</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab11"><i class="fa fa-id-card"></i>Membership</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab12"><i class="fa fa-trophy"></i>Top Brands</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab13"><i class="fa fa-file-contract"></i>Legal Pages</button>
            </li>
            <?php }?>
          </ul>
          <div class="tab-content pt-2 settings-content">
            <div class="tab-pane fade show active profile-overview" id="tab1">
              <!-- profile settings Form -->
              <form method="POST" action="{{ url('admin/profile-settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="name" class="col-md-4 col-lg-3 col-form-label">Name</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="name" class="form-control" id="name" value="<?=$admin->name?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="email" class="form-control" id="email" value="<?=$admin->email?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="mobile" class="col-md-4 col-lg-3 col-form-label">Mobile</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="mobile" class="form-control" id="mobile" value="<?=$admin->mobile?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="profile_site_logo" class="col-md-4 col-lg-3 col-form-label">Site Logo</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="file" name="site_logo" class="form-control" id="profile_site_logo" accept=".jpg,.jpeg,.png,.webp,.ico">
                    <small class="text-info">Used as the portal brand logo and by the mobile app. JPG, JPEG, PNG, WEBP or ICO; maximum 2 MB.</small><br>
                    <?php if($setting->site_logo != ''){?>
                      <img src="<?=env('UPLOADS_URL').$setting->site_logo?>" alt="<?=$setting->site_name?>" class="img-thumbnail" style="max-width: 240px; max-height: 160px; margin-top: 10px;">
                    <?php } else {?>
                      <img src="<?=env('NO_IMAGE')?>" alt="<?=$setting->site_name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                    <?php }?>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End profile settings Form -->
              <!-- <span>Quantico Area Realestate Properties</span> -->
            </div>
            <div class="tab-pane fade profile-edit pt-3" id="tab2">
              <!-- general settings Form -->
              <form method="POST" action="{{ url('admin/general-settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="site_name" class="col-md-4 col-lg-3 col-form-label">Site Name</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="site_name" type="text" class="form-control" id="site_name" value="<?=$setting->site_name?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="site_phone" class="col-md-4 col-lg-3 col-form-label">Site Phone</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="site_phone" type="text" class="form-control" id="site_phone" value="<?=$setting->site_phone?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="site_phone2" class="col-md-4 col-lg-3 col-form-label">Site Phone 2</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="site_phone2" type="text" class="form-control" id="site_phone2" value="<?=$setting->site_phone2?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="site_mail" class="col-md-4 col-lg-3 col-form-label">Site Email</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="site_mail" type="email" class="form-control" id="site_mail" value="<?=$setting->site_mail?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="system_email" class="col-md-4 col-lg-3 col-form-label">System Email</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="system_email" type="email" class="form-control" id="system_email" value="<?=$setting->system_email?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="site_url" class="col-md-4 col-lg-3 col-form-label">Site URL</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="site_url" type="url" class="form-control" id="site_url" value="<?=$setting->site_url?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="description" class="col-md-4 col-lg-3 col-form-label">Address</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea name="description" class="form-control" id="description" rows="5"><?=$setting->description?></textarea>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="copyright_statement" class="col-md-4 col-lg-3 col-form-label">Copyright Statement</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea name="copyright_statement" class="form-control" id="copyright_statement" rows="5"><?=$setting->copyright_statement?></textarea>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="google_map_api_code" class="col-md-4 col-lg-3 col-form-label">Google Map API Code</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea name="google_map_api_code" class="form-control" id="google_map_api_code" rows="5"><?=$setting->google_map_api_code?></textarea>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="google_analytics_code" class="col-md-4 col-lg-3 col-form-label">Google Analytics Code</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea name="google_analytics_code" class="form-control" id="google_analytics_code" rows="5"><?=$setting->google_analytics_code?></textarea>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="google_pixel_code" class="col-md-4 col-lg-3 col-form-label">Google Pixel Code</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea name="google_pixel_code" class="form-control" id="google_pixel_code" rows="5"><?=$setting->google_pixel_code?></textarea>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="facebook_tracking_code" class="col-md-4 col-lg-3 col-form-label">Facebook Tracking Code</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea name="facebook_tracking_code" class="form-control" id="facebook_tracking_code" rows="5"><?=$setting->facebook_tracking_code?></textarea>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="twitter_profile" class="col-md-4 col-lg-3 col-form-label">Twitter Profile</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="twitter_profile" type="text" class="form-control" id="twitter_profile" value="<?=$setting->twitter_profile?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="facebook_profile" class="col-md-4 col-lg-3 col-form-label">Facebook Profile</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="facebook_profile" type="text" class="form-control" id="facebook_profile" value="<?=$setting->facebook_profile?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="instagram_profile" class="col-md-4 col-lg-3 col-form-label">Instagram Profile</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="instagram_profile" type="text" class="form-control" id="instagram_profile" value="<?=$setting->instagram_profile?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="linkedin_profile" class="col-md-4 col-lg-3 col-form-label">Pinterest Profile</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="linkedin_profile" type="text" class="form-control" id="linkedin_profile" value="<?=$setting->linkedin_profile?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="youtube_profile" class="col-md-4 col-lg-3 col-form-label">Youtube Profile</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="youtube_profile" type="text" class="form-control" id="youtube_profile" value="<?=$setting->youtube_profile?>">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="site_logo" class="col-md-4 col-lg-3 col-form-label">Logo</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="file" name="site_logo" class="form-control" id="site_logo" accept=".jpg,.jpeg,.png,.webp,.ico">
                    <small class="text-info">JPG, JPEG, PNG, WEBP or ICO; maximum 2 MB.</small><br>
                    <?php if($setting->site_logo != ''){?>
                      <img src="<?=env('UPLOADS_URL').$setting->site_logo?>" alt="<?=$setting->site_name?>" class="img-thumbnail" style="max-width: 240px; max-height: 120px; margin-top: 10px;">
                    <?php } else {?>
                      <img src="<?=env('NO_IMAGE')?>" alt="<?=$setting->site_name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                    <?php }?>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="site_footer_logo" class="col-md-4 col-lg-3 col-form-label">Footer Logo</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="file" name="site_footer_logo" class="form-control" id="site_footer_logo" accept=".jpg,.jpeg,.png,.webp,.ico">
                    <small class="text-info">JPG, JPEG, PNG, WEBP or ICO; maximum 2 MB.</small><br>
                    <?php if($setting->site_footer_logo != ''){?>
                      <img src="<?=env('UPLOADS_URL').$setting->site_footer_logo?>" alt="<?=$setting->site_name?>" class="img-thumbnail" style="max-width: 240px; max-height: 120px; margin-top: 10px;">
                    <?php } else {?>
                      <img src="<?=env('NO_IMAGE')?>" alt="<?=$setting->site_name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                    <?php }?>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="site_favicon" class="col-md-4 col-lg-3 col-form-label">Favicon</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="file" name="site_favicon" class="form-control" id="site_favicon" accept=".jpg,.jpeg,.png,.webp,.ico">
                    <small class="text-info">JPG, JPEG, PNG, WEBP or ICO; maximum 1 MB.</small><br>
                    <?php if($setting->site_favicon != ''){?>
                      <img src="<?=env('UPLOADS_URL').$setting->site_favicon?>" alt="<?=$setting->site_name?>" class="img-thumbnail" style="max-width: 96px; max-height: 96px; margin-top: 10px;">
                    <?php } else {?>
                      <img src="<?=env('NO_IMAGE')?>" alt="<?=$setting->site_name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                    <?php }?>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End general settings Form -->
            </div>
            <div class="tab-pane fade pt-3" id="tab3">
              <!-- chnage password Form -->
              <form method="POST" action="{{ url('admin/change-password') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="old_password" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="password" name="old_password" class="form-control" id="old_password">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="new_password" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="password" name="new_password" class="form-control" id="new_password">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="confirm_password" class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="password" name="confirm_password" class="form-control" id="confirm_password">
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End chnage password Form -->
            </div>
            <div class="tab-pane fade pt-3" id="tab4">
              <!-- email settings Form -->
              <form method="POST" action="{{ url('admin/email-settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="from_email" class="col-md-4 col-lg-3 col-form-label">From Email</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="from_email" class="form-control" id="from_email" value="<?=$setting->from_email?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="from_name" class="col-md-4 col-lg-3 col-form-label">From Name</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="from_name" class="form-control" id="from_name" value="<?=$setting->from_name?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="smtp_host" class="col-md-4 col-lg-3 col-form-label">SMTP Host</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="smtp_host" class="form-control" id="smtp_host" value="<?=$setting->smtp_host?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="smtp_username" class="col-md-4 col-lg-3 col-form-label">SMTP Username</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="smtp_username" class="form-control" id="smtp_username" value="<?=$setting->smtp_username?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="smtp_password" class="col-md-4 col-lg-3 col-form-label">SMTP Password</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="smtp_password" class="form-control" id="smtp_password" value="<?=$setting->smtp_password?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="smtp_port" class="col-md-4 col-lg-3 col-form-label">SMTP Port</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="smtp_port" class="form-control" id="smtp_port" value="<?=$setting->smtp_port?>">
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End email settings Form -->
            </div>
            <div class="tab-pane fade pt-3" id="tab9">
              <form method="POST" action="{{ url('admin/email-template') }}" enctype="multipart/form-data" class="email-template-form">
                @csrf
                <div class="template-delivery">
                  <div>
                    <label for="email_template_user_signup_sender_name">Sender name</label>
                    <input type="text" name="email_template_user_signup_sender_name" class="form-control" id="email_template_user_signup_sender_name" value="<?=$setting->email_template_user_signup_sender_name?>">
                  </div>
                  <div>
                    <label for="email_template_user_signup_subject">Subject</label>
                    <input type="text" name="email_template_user_signup_subject" class="form-control" id="email_template_user_signup_subject" value="<?=$setting->email_template_user_signup_subject?>">
                  </div>
                </div>

                <div class="template-manager">
                  <aside class="template-list" aria-label="Email template selection">
                    <div class="template-list-title">Choose template</div>
                    <button type="button" class="template-selector active" data-template="signup"><i class="fa fa-user-plus"></i>New signup</button>
                    <button type="button" class="template-selector" data-template="forgot"><i class="fa fa-key"></i>Forgot password</button>
                    <button type="button" class="template-selector" data-template="changed"><i class="fa fa-lock"></i>Password changed</button>
                    <button type="button" class="template-selector" data-template="failed"><i class="fa fa-exclamation-triangle"></i>Failed login</button>
                    <button type="button" class="template-selector" data-template="contact"><i class="fa fa-comment"></i>Contact request</button>
                  </aside>

                  <div class="template-workspace">
                    <div class="template-editor-panel active" data-template-panel="signup">
                      <div class="template-editor-head"><div><h4>New signup</h4><p>Welcomes a newly registered member and shares their account details.</p></div><button type="button" class="preview-toggle"><i class="fa fa-eye"></i> Preview</button></div>
                      <div class="variable-bar"><span>Available variables</span><button type="button" class="variable-chip">@{{name}}</button><button type="button" class="variable-chip">@{{email}}</button><button type="button" class="variable-chip">@{{password}}</button></div>
                      <textarea name="email_template_user_signup" class="form-control" id="ckeditor1" rows="5"><?=$setting->email_template_user_signup?></textarea>
                      <div class="email-preview"><div class="email-preview-paper"></div></div>
                    </div>

                    <div class="template-editor-panel" data-template-panel="forgot">
                      <div class="template-editor-head"><div><h4>Forgot password</h4><p>Sends the one-time password used for account recovery.</p></div><button type="button" class="preview-toggle"><i class="fa fa-eye"></i> Preview</button></div>
                      <div class="variable-bar"><span>Available variables</span><button type="button" class="variable-chip">@{{otp1}}</button><button type="button" class="variable-chip">@{{otp2}}</button><button type="button" class="variable-chip">@{{otp3}}</button><button type="button" class="variable-chip">@{{otp4}}</button></div>
                      <textarea name="email_template_forgot_password" class="form-control" id="ckeditor2" rows="5"><?=$setting->email_template_forgot_password?></textarea>
                      <div class="email-preview"><div class="email-preview-paper"></div></div>
                    </div>

                    <div class="template-editor-panel" data-template-panel="changed">
                      <div class="template-editor-head"><div><h4>Password changed</h4><p>Confirms that a member's password was successfully updated.</p></div><button type="button" class="preview-toggle"><i class="fa fa-eye"></i> Preview</button></div>
                      <div class="variable-bar"><span>Available variables</span><button type="button" class="variable-chip">@{{name}}</button><button type="button" class="variable-chip">@{{email}}</button></div>
                      <textarea name="email_template_change_password" class="form-control" id="ckeditor3" rows="5"><?=$setting->email_template_change_password?></textarea>
                      <div class="email-preview"><div class="email-preview-paper"></div></div>
                    </div>

                    <div class="template-editor-panel" data-template-panel="failed">
                      <div class="template-editor-head"><div><h4>Failed login alert</h4><p>Notifies a member about an unsuccessful login attempt.</p></div><button type="button" class="preview-toggle"><i class="fa fa-eye"></i> Preview</button></div>
                      <div class="variable-bar"><span>Available variables</span><button type="button" class="variable-chip">@{{email}}</button></div>
                      <textarea name="email_template_failed_login" class="form-control" id="ckeditor4" rows="5"><?=$setting->email_template_failed_login?></textarea>
                      <div class="email-preview"><div class="email-preview-paper"></div></div>
                    </div>

                    <div class="template-editor-panel" data-template-panel="contact">
                      <div class="template-editor-head"><div><h4>Contact request</h4><p>Controls the response sent after a portal contact submission.</p></div><button type="button" class="preview-toggle"><i class="fa fa-eye"></i> Preview</button></div>
                      <div class="variable-bar"><span>Template content</span></div>
                      <textarea name="email_template_contactus" class="form-control" id="ckeditor5" rows="5"><?=$setting->email_template_contactus?></textarea>
                      <div class="email-preview"><div class="email-preview-paper"></div></div>
                    </div>

                    <div class="template-note"><i class="fa fa-info-circle"></i><span>Click a variable to copy it, then paste it into the editor. Keep the braces unchanged so the system can replace it when sending.</span></div>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Save all templates</button>
                </div>
              </form>
            </div>
            <div class="tab-pane fade pt-3" id="tab5">
              <!-- sms settings Form -->
              <form method="POST" action="{{ url('admin/sms-settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="sms_authentication_key" class="col-md-4 col-lg-3 col-form-label">Authentication Key</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="sms_authentication_key" class="form-control" id="sms_authentication_key" value="<?=$setting->sms_authentication_key?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="sms_sender_id" class="col-md-4 col-lg-3 col-form-label">Sender ID</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="sms_sender_id" class="form-control" id="sms_sender_id" value="<?=$setting->sms_sender_id?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="sms_base_url" class="col-md-4 col-lg-3 col-form-label">Base URL</label>
                  <div class="col-md-8 col-lg-9">
                    <input type="text" name="sms_base_url" class="form-control" id="sms_base_url" value="<?=$setting->sms_base_url?>">
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End sms settings Form -->
            </div>
            <div class="tab-pane fade pt-3" id="tab6">
              <!-- footer settings Form -->
              <form method="POST" action="{{ url('admin/footer-settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="footer_text" class="col-md-4 col-lg-3 col-form-label">Footer Text</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea type="text" name="footer_text" class="form-control" id="ckeditor6" rows="5"><?=$setting->footer_text?></textarea>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="footer_description" class="col-md-4 col-lg-3 col-form-label">Footer Description</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea type="text" name="footer_description" class="form-control" id="ckeditor7" rows="5"><?=$setting->footer_description?></textarea>
                  </div>
                </div>
                <label for="" class="col-md-4 col-lg-3 col-form-label">Quick Links</label>
                <div class="field_wrapper1" style="border: 1px solid #8144f0;padding: 10px;margin-bottom: 10px;">
                  <?php
                  $footer_link_name = (($setting->footer_link_name != '')?json_decode($setting->footer_link_name):[]);
                  $footer_link = (($setting->footer_link != '')?json_decode($setting->footer_link):[]);
                  if(!empty($footer_link_name)){ for($i=0;$i<count($footer_link_name);$i++){
                  ?>
                      <div class="row">
                          <div class="col-md-5">
                              <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>
                              <span class="input-with-icon">
                                  <input type="text" class="form-control requiredCheck" data-check="Link Text" name="footer_link_name[]" value="<?=$footer_link_name[$i]?>" autocomplete="off">
                              </span>
                          </div>
                          <div class="col-md-5">
                              <label for="lefticon" class="control-label">Link<span class="red">*</span></label>
                              <span class="input-with-icon">
                                  <input type="text" class="form-control requiredCheck" data-check="Link" value="<?=$footer_link[$i]?>" name="footer_link[]" autocomplete="off">
                              </span>
                          </div>
                          <div class="col-md-2" style="margin-top: 26px;">
                              <a href="javascript:void(0);" class="remove_button1" title="Add field"><i class="fa fa-minus-circle fa-2x text-danger"></i></a>
                          </div>
                      </div>
                  <?php } }?>
                  <div class="row">
                      <div class="col-md-5">
                          <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>
                          <span class="input-with-icon">
                              <input type="text" class="form-control requiredCheck" data-check="Link Text" name="first_col_link_text[]" autocomplete="off">
                          </span>
                      </div>
                      <div class="col-md-5">
                          <label for="lefticon" class="control-label">Link<span class="red">*</span></label>
                          <span class="input-with-icon">
                              <input type="text" class="form-control requiredCheck" data-check="Link" name="first_col_link[]" autocomplete="off">
                          </span>
                      </div>
                      <div class="col-md-2" style="margin-top: 26px;">
                          <a href="javascript:void(0);" class="add_button1" title="Add field"><i class="fa fa-plus-circle fa-2x text-success"></i></a>
                      </div>
                  </div>
                </div>

                <label for="" class="col-md-4 col-lg-3 col-form-label">Services</label>
                <div class="field_wrapper2" style="border: 1px solid #8144f0;padding: 10px;margin-bottom: 10px;">
                  <?php
                  $footer_link_name2 = (($setting->footer_link_name2 != '')?json_decode($setting->footer_link_name2):[]);
                  $footer_link2 = (($setting->footer_link2 != '')?json_decode($setting->footer_link2):[]);
                  if(!empty($footer_link_name2)){ for($i=0;$i<count($footer_link_name2);$i++){
                  ?>
                      <div class="row">
                          <div class="col-md-5">
                              <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>
                              <span class="input-with-icon">
                                  <input type="text" class="form-control requiredCheck" data-check="Link Text" name="second_col_link_text[]" value="<?=$footer_link_name2[$i]?>" autocomplete="off">
                              </span>
                          </div>
                          <div class="col-md-5">
                              <label for="lefticon" class="control-label">Link<span class="red">*</span></label>
                              <span class="input-with-icon">
                                  <input type="text" class="form-control requiredCheck" data-check="Link" value="<?=$footer_link2[$i]?>" name="second_col_link[]" autocomplete="off">
                              </span>
                          </div>
                          <div class="col-md-2" style="margin-top: 26px;">
                              <a href="javascript:void(0);" class="remove_button2" title="Add field"><i class="fa fa-minus-circle fa-2x text-danger"></i></a>
                          </div>
                      </div>
                  <?php } }?>
                  <div class="row">
                      <div class="col-md-5">
                          <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>
                          <span class="input-with-icon">
                              <input type="text" class="form-control requiredCheck" data-check="Link Text" name="second_col_link_text[]" autocomplete="off">
                          </span>
                      </div>
                      <div class="col-md-5">
                          <label for="lefticon" class="control-label">Link<span class="red">*</span></label>
                          <span class="input-with-icon">
                              <input type="text" class="form-control requiredCheck" data-check="Link" name="second_col_link[]" autocomplete="off">
                          </span>
                      </div>
                      <div class="col-md-2" style="margin-top: 26px;">
                          <a href="javascript:void(0);" class="add_button2" title="Add field"><i class="fa fa-plus-circle fa-2x text-success"></i></a>
                      </div>
                  </div>
                </div>

                <label for="" class="col-md-4 col-lg-3 col-form-label">Useful Links</label>
                <div class="field_wrapper3" style="border: 1px solid #8144f0;padding: 10px;margin-bottom: 10px;">
                  <?php
                  $footer_link_name3 = (($setting->footer_link_name3 != '')?json_decode($setting->footer_link_name3):[]);
                  $footer_link3 = (($setting->footer_link3 != '')?json_decode($setting->footer_link3):[]);
                  if(!empty($footer_link_name3)){ for($i=0;$i<count($footer_link_name3);$i++){
                  ?>
                      <div class="row">
                          <div class="col-md-5">
                              <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>
                              <span class="input-with-icon">
                                  <input type="text" class="form-control requiredCheck" data-check="Link Text" name="footer_link_name3[]" value="<?=$footer_link_name3[$i]?>" autocomplete="off">
                              </span>
                          </div>
                          <div class="col-md-5">
                              <label for="lefticon" class="control-label">Link<span class="red">*</span></label>
                              <span class="input-with-icon">
                                  <input type="text" class="form-control requiredCheck" data-check="Link" value="<?=$footer_link3[$i]?>" name="footer_link3[]" autocomplete="off">
                              </span>
                          </div>
                          <div class="col-md-2" style="margin-top: 26px;">
                              <a href="javascript:void(0);" class="remove_button3" title="Add field"><i class="fa fa-minus-circle fa-2x text-danger"></i></a>
                          </div>
                      </div>
                  <?php } }?>
                  <div class="row">
                      <div class="col-md-5">
                          <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>
                          <span class="input-with-icon">
                              <input type="text" class="form-control requiredCheck" data-check="Link Text" name="footer_link_name3[]" autocomplete="off">
                          </span>
                      </div>
                      <div class="col-md-5">
                          <label for="lefticon" class="control-label">Link<span class="red">*</span></label>
                          <span class="input-with-icon">
                              <input type="text" class="form-control requiredCheck" data-check="Link" name="footer_link3[]" autocomplete="off">
                          </span>
                      </div>
                      <div class="col-md-2" style="margin-top: 26px;">
                          <a href="javascript:void(0);" class="add_button3" title="Add field"><i class="fa fa-plus-circle fa-2x text-success"></i></a>
                      </div>
                  </div>
                </div>

                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End footer settings Form -->
            </div>
            <div class="tab-pane fade pt-3" id="tab7">
              <!-- seo settings Form -->
              <form method="POST" action="{{ url('admin/seo-settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="meta_title" class="col-md-4 col-lg-3 col-form-label">Meta Title</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea type="text" name="meta_title" class="form-control" id="meta_title" rows="5"><?=$setting->meta_title?></textarea>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="meta_description" class="col-md-4 col-lg-3 col-form-label">Meta Description</label>
                  <div class="col-md-8 col-lg-9">
                    <textarea type="text" name="meta_description" class="form-control" id="meta_description" rows="5"><?=$setting->meta_description?></textarea>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End seo settings Form -->
            </div>
            <div class="tab-pane fade pt-3" id="tab8">
              <!-- payment settings Form -->
              <form method="POST" action="{{ url('admin/payment-settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="stripe_payment_type" class="col-md-4 col-lg-3 col-form-label">Stripe Sandbox Secret Key</label>
                  <div class="col-md-8 col-lg-9">
                    <select name="stripe_payment_type" class="form-control" id="stripe_payment_type" required>
                      <option value="" selected>Select Payment Environment</option>
                      <option value="1" <?=(($setting->stripe_payment_type == 1)?'selected':'')?>>SANDBOX</option>
                      <option value="2" <?=(($setting->stripe_payment_type == 2)?'selected':'')?>>LIVE</option>
                    </select>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="stripe_sandbox_sk" class="col-md-4 col-lg-3 col-form-label">Stripe Sandbox Secret Key</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="stripe_sandbox_sk" type="text" class="form-control" id="stripe_sandbox_sk" value="<?=$setting->stripe_sandbox_sk?>" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="stripe_sandbox_pk" class="col-md-4 col-lg-3 col-form-label">Stripe Sandbox Public Key</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="stripe_sandbox_pk" type="text" class="form-control" id="stripe_sandbox_pk" value="<?=$setting->stripe_sandbox_pk?>" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="stripe_live_sk" class="col-md-4 col-lg-3 col-form-label">Stripe Live Secret Key</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="stripe_live_sk" type="text" class="form-control" id="stripe_live_sk" value="<?=$setting->stripe_live_sk?>" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="stripe_live_pk" class="col-md-4 col-lg-3 col-form-label">Stripe Live Public Key</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="stripe_live_pk" type="text" class="form-control" id="stripe_live_pk" value="<?=$setting->stripe_live_pk?>" required>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End payment settings Form -->
            </div>
            <div class="tab-pane fade pt-3" id="tab10">
              <!-- color settings Form -->
              <form method="POST" action="{{ url('admin/color-settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                  <label for="theme_color" class="col-md-4 col-lg-3 col-form-label">Theme Color</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="theme_color" type="color" class="form-control" id="theme_color" value="<?=$setting->theme_color?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="font_color" class="col-md-4 col-lg-3 col-form-label">Font Color</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="font_color" type="color" class="form-control" id="font_color" value="<?=$setting->font_color?>">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="sidebar_bgcolor" class="col-md-4 col-lg-3 col-form-label">Sidebar Background Color</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="sidebar_bgcolor" type="color" class="form-control" id="sidebar_bgcolor" value="<?=$setting->sidebar_bgcolor?>" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="header_bgcolor" class="col-md-4 col-lg-3 col-form-label">Header Background Color</label>
                  <div class="col-md-8 col-lg-9">
                    <input name="header_bgcolor" type="color" class="form-control" id="header_bgcolor" value="<?=$setting->header_bgcolor?>" required>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form><!-- End payment settings Form -->
            </div>
            <div class="tab-pane fade pt-3" id="tab11">
              <form method="POST" action="{{ url('admin/membership-settings') }}">
                @csrf
                <div class="mb-4">
                  <h4>Membership fees</h4>
                  <p class="text-muted">Configure the fee and availability of each membership period.</p>
                </div>
                @foreach($membershipPlans as $plan)
                  <div class="row mb-3 align-items-center">
                    <label for="plan_fee_{{ $plan->id }}" class="col-md-4 col-lg-3 col-form-label">{{ $plan->name }} fee</label>
                    <div class="col-md-5 col-lg-6">
                      <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" min="0" step="0.01" name="plans[{{ $plan->id }}][fee]" class="form-control" id="plan_fee_{{ $plan->id }}" value="{{ old('plans.'.$plan->id.'.fee', $plan->fee) }}" required>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-check form-switch">
                        <input type="hidden" name="plans[{{ $plan->id }}][is_active]" value="0">
                        <input class="form-check-input plan-availability" type="checkbox" name="plans[{{ $plan->id }}][is_active]" value="1" id="plan_active_{{ $plan->id }}" data-plan-duration="{{ $plan->duration_months }}" @checked(old('plans.'.$plan->id.'.is_active', $plan->is_active))>
                        <label class="form-check-label" for="plan_active_{{ $plan->id }}">Available</label>
                      </div>
                    </div>
                  </div>
                @endforeach

                <hr class="my-4">
                <style>.renewal-builder{max-width:920px}.renewal-choice{position:relative;display:block;margin-bottom:10px;padding:14px 16px 14px 44px;border:1px solid #dfe5ee;border-radius:8px;background:#fff;cursor:pointer}.renewal-choice:has(input:checked){border-color:#397ef6;background:#f5f8ff;box-shadow:0 0 0 2px rgba(57,126,246,.08)}.renewal-choice input{position:absolute;top:17px;left:17px}.renewal-choice strong{display:block;color:#2c3d57;font-size:13px}.renewal-choice span{display:block;margin-top:3px;color:#77859a;font-size:11px;line-height:1.5}.cycle-config{margin:10px 0 14px;padding:14px;border:1px solid #e2e7ef;border-radius:8px;background:#fafbfd}.cycle-config h6{margin:0 0 4px;color:#34445d;font-size:12px}.cycle-config>p{margin:0 0 12px;color:#7b8798;font-size:10.5px}.cycle-preview{display:grid;grid-template-columns:repeat(3,1fr);overflow:hidden;margin-top:12px;border:1px solid #e1e7ef;border-radius:7px;background:#fff}.cycle-preview>div{position:relative;padding:11px;border-right:1px solid #e1e7ef;transition:background-color .2s,color .2s}.cycle-preview>div:last-child{border:0}.cycle-preview>div::after{position:absolute;top:9px;right:10px;padding:2px 6px;border-radius:10px;font-size:7.5px;font-weight:800;text-transform:uppercase}.cycle-preview>div.plan-enabled{background:#ecfdf3}.cycle-preview>div.plan-enabled::after{content:'Active';color:#067647;background:#d1fadf}.cycle-preview>div.plan-enabled small,.cycle-preview>div.plan-enabled strong{color:#067647}.cycle-preview>div.plan-disabled{background:#f2f4f7}.cycle-preview>div.plan-disabled::after{content:'Disabled';color:#667085;background:#e4e7ec}.cycle-preview>div.plan-disabled small,.cycle-preview>div.plan-disabled strong{color:#98a2b3}.cycle-preview small{display:block;color:#8490a2;font-size:8.5px;font-weight:750;text-transform:uppercase}.cycle-preview strong{display:block;margin-top:4px;padding-right:45px;color:#2e3e57;font-size:11px}.renewal-summary{padding:10px 12px;border-left:3px solid #397ef6;background:#f6f9ff;color:#53647c;font-size:11px}@media(max-width:700px){.cycle-preview{grid-template-columns:1fr}.cycle-preview>div{border-right:0;border-bottom:1px solid #e1e7ef}}</style>
                <div class="row mb-4">
                  <label class="col-md-4 col-lg-3 col-form-label">When should members renew?</label>
                  <div class="col-md-8 col-lg-9 renewal-builder">
                    <label class="renewal-choice" for="renewal_joining"><input type="radio" name="renewal_basis" id="renewal_joining" value="joining_date" @checked(old('renewal_basis', $membershipSetting->renewal_basis) === 'joining_date')><strong>On each member’s anniversary</strong><span>Every member follows their own joining date. Example: someone joining on 18 June renews monthly on the 18th, half-yearly on 18 December, or yearly on 18 June.</span></label>
                    <label class="renewal-choice" for="renewal_calendar"><input type="radio" name="renewal_basis" id="renewal_calendar" value="calendar" @checked(old('renewal_basis', $membershipSetting->renewal_basis) === 'calendar')><strong>On fixed organization renewal dates</strong><span>Everyone follows the same billing cycle. Choose your organization’s annual renewal date below.</span></label>
                    <div class="cycle-config" id="cycle_config">
                      <h6>Organization renewal anchor</h6><p>This is the first day of your annual membership cycle.</p>
                      <div class="row g-2"><div class="col-sm-7"><label class="form-label" for="cycle_start_month">Cycle starts in</label><select name="cycle_start_month" id="cycle_start_month" class="form-select">@foreach(range(1,12) as $month)<option value="{{$month}}" @selected((int)old('cycle_start_month',$membershipSetting->cycle_start_month??1)===$month)>{{\Carbon\Carbon::create(2000,$month,1)->format('F')}}</option>@endforeach</select></div><div class="col-sm-5"><label class="form-label" for="cycle_day">Day of month</label><select name="cycle_day" id="cycle_day" class="form-select">@foreach(range(1,31) as $day)<option value="{{$day}}" @selected((int)old('cycle_day',$membershipSetting->cycle_day??1)===$day)>{{$day}}</option>@endforeach</select></div></div>
                      <div class="cycle-preview"><div class="plan-schedule-card" data-plan-duration="1"><small>Monthly plan</small><strong id="monthly_example"></strong></div><div class="plan-schedule-card" data-plan-duration="6"><small>Half-yearly plan</small><strong id="halfyear_example"></strong></div><div class="plan-schedule-card" data-plan-duration="12"><small>Yearly plan</small><strong id="yearly_example"></strong></div></div>
                    </div>
                    <div class="renewal-summary" id="renewal_summary"></div>
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary">Save membership settings</button>
                </div>
              </form>
            </div>
            @include('admin.maincontents.settings.top-brands-tab')
            @include('admin.maincontents.settings.legal-pages-tab')
          </div><!-- End Bordered Tabs -->
        </div>
      </div>
    </div>
    <!-- Sticky Block End Point -->
    <div id="stickyBlockEndPoint"></div>
  </div>
</div>
<!-- End Row -->
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        if (window.location.hash) {
            const tabButton = document.querySelector('[data-bs-target="' + window.location.hash + '"]');
            if (tabButton && window.bootstrap) {
                bootstrap.Tab.getOrCreateInstance(tabButton).show();
            }
        }

        document.querySelectorAll('.settings-nav [data-bs-toggle="tab"]').forEach(function (tabButton) {
            tabButton.addEventListener('shown.bs.tab', function (event) {
                const target = event.target.getAttribute('data-bs-target');
                if (target) {
                    history.replaceState(null, '', target);
                }
            });
        });

        const renewalRadios = document.querySelectorAll('input[name="renewal_basis"]');
        const cycleConfig = document.getElementById('cycle_config');
        const cycleMonth = document.getElementById('cycle_start_month');
        const cycleDay = document.getElementById('cycle_day');
        const renewalSummary = document.getElementById('renewal_summary');
        const planAvailability = document.querySelectorAll('.plan-availability');
        const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const ordinal = number => number + ([11,12,13].includes(number % 100) ? 'th' : ({1:'st',2:'nd',3:'rd'}[number % 10] || 'th'));
        const updateRenewalPreview = function () {
            if (!cycleConfig) return;
            const fixedCycle = document.getElementById('renewal_calendar').checked;
            cycleConfig.style.display = fixedCycle ? 'block' : 'none';
            cycleMonth.disabled = !fixedCycle;
            cycleDay.disabled = !fixedCycle;
            if (!fixedCycle) {
                renewalSummary.innerHTML = '<strong>Member anniversary:</strong> the renewal date is calculated separately from each member’s joining date.';
                return;
            }
            const month = parseInt(cycleMonth.value, 10);
            const day = parseInt(cycleDay.value, 10);
            const secondMonth = ((month - 1 + 6) % 12);
            document.getElementById('monthly_example').textContent = ordinal(day) + ' of every month';
            document.getElementById('halfyear_example').textContent = ordinal(day) + ' ' + monthNames[month - 1] + ' and ' + ordinal(day) + ' ' + monthNames[secondMonth];
            document.getElementById('yearly_example').textContent = ordinal(day) + ' ' + monthNames[month - 1] + ' every year';
            renewalSummary.innerHTML = '<strong>Organization cycle:</strong> all members use these fixed renewal dates. If a month is shorter than the selected day, its final day is used.';
        };
        const updatePlanScheduleCards = function () {
            document.querySelectorAll('.plan-schedule-card').forEach(function (card) {
                const toggle = document.querySelector('.plan-availability[data-plan-duration="' + card.dataset.planDuration + '"]');
                const enabled = Boolean(toggle && toggle.checked);
                card.classList.toggle('plan-enabled', enabled);
                card.classList.toggle('plan-disabled', !enabled);
            });
        };
        renewalRadios.forEach(input => input.addEventListener('change', updateRenewalPreview));
        planAvailability.forEach(input => input.addEventListener('change', updatePlanScheduleCards));
        if (cycleMonth) cycleMonth.addEventListener('change', updateRenewalPreview);
        if (cycleDay) cycleDay.addEventListener('change', updateRenewalPreview);
        updateRenewalPreview();
        updatePlanScheduleCards();

        document.querySelectorAll('.template-selector').forEach(function (button) {
            button.addEventListener('click', function () {
                const template = button.dataset.template;
                document.querySelectorAll('.template-selector').forEach(item => item.classList.remove('active'));
                document.querySelectorAll('.template-editor-panel').forEach(panel => panel.classList.remove('active'));
                button.classList.add('active');
                document.querySelector('[data-template-panel="' + template + '"]').classList.add('active');
            });
        });

        document.querySelectorAll('.variable-chip').forEach(function (chip) {
            chip.addEventListener('click', async function () {
                const variable = chip.textContent.trim();
                try {
                    await navigator.clipboard.writeText(variable);
                    const original = chip.textContent;
                    chip.textContent = 'Copied';
                    setTimeout(() => chip.textContent = original, 1000);
                } catch (error) {
                    const temporary = document.createElement('textarea');
                    temporary.value = variable;
                    document.body.appendChild(temporary);
                    temporary.select();
                    document.execCommand('copy');
                    temporary.remove();
                }
            });
        });

        document.querySelectorAll('.preview-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const panel = button.closest('.template-editor-panel');
                const preview = panel.querySelector('.email-preview');
                const editor = panel.querySelector('.ck-editor');
                const editable = panel.querySelector('.ck-editor__editable');
                const textarea = panel.querySelector('textarea');
                const showingPreview = preview.classList.toggle('active');

                if (showingPreview) {
                    preview.querySelector('.email-preview-paper').innerHTML = editable ? editable.innerHTML : textarea.value;
                    if (editor) editor.style.display = 'none';
                    button.innerHTML = '<i class="fa fa-edit"></i> Edit';
                } else {
                    if (editor) editor.style.display = '';
                    button.innerHTML = '<i class="fa fa-eye"></i> Preview';
                }
            });
        });
    });

    $(document).ready(function(){
        var maxField = 10; //Input fields increment limitation
        var addButton = $('.add_button1'); //Add button selector
        var wrapper = $('.field_wrapper1'); //Input field wrapper
        var fieldHTML = '<div class="row">\
                            <div class="col-md-5">\
                                <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>\
                                <span class="input-with-icon">\
                                    <input type="text" class="form-control requiredCheck" data-check="Link Text" name="footer_link_name[]" autocomplete="off">\
                                </span>\
                            </div>\
                            <div class="col-md-5">\
                                <label for="lefticon" class="control-label">Link<span class="red">*</span></label>\
                                <span class="input-with-icon">\
                                    <input type="text" class="form-control requiredCheck" data-check="Link" name="footer_link[]" autocomplete="off">\
                                </span>\
                            </div>\
                            <div class="col-md-2" style="margin-top: 26px;">\
                                <a href="javascript:void(0);" class="remove_button1" title="Remove field"><i class="fa fa-minus-circle fa-2x text-danger"></i></a>\
                            </div>\
                        </div>'; //New input field html
        var x = 1; //Initial field counter is 1

        //Once add button is clicked
        $(addButton).click(function(){
            //Check maximum number of input fields
            if(x < maxField){
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); //Add field html
            }
        });

        //Once remove button is clicked
        $(wrapper).on('click', '.remove_button1', function(e){
            e.preventDefault();
            $(this).parent('div').parent('div').remove(); //Remove field html
            x--; //Decrement field counter
        });
    });

    $(document).ready(function(){
        var maxField = 10; //Input fields increment limitation
        var addButton = $('.add_button2'); //Add button selector
        var wrapper = $('.field_wrapper2'); //Input field wrapper
        var fieldHTML = '<div class="row">\
                            <div class="col-md-5">\
                                <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>\
                                <span class="input-with-icon">\
                                    <input type="text" class="form-control requiredCheck" data-check="Second Column Link Text" name="second_col_link_text[]" autocomplete="off">\
                                </span>\
                            </div>\
                            <div class="col-md-5">\
                                <label for="lefticon" class="control-label">Link<span class="red">*</span></label>\
                                <span class="input-with-icon">\
                                    <input type="text" class="form-control requiredCheck" data-check="Second Column Link" name="second_col_link[]" autocomplete="off">\
                                </span>\
                            </div>\
                            <div class="col-md-2" style="margin-top: 33px;">\
                                <a href="javascript:void(0);" class="remove_button2" title="Remove field"><i class="fa fa-minus-circle fa-2x text-danger"></i></a>\
                            </div>\
                        </div>'; //New input field html
        var x = 1; //Initial field counter is 1

        //Once add button is clicked
        $(addButton).click(function(){
            //Check maximum number of input fields
            if(x < maxField){
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); //Add field html
            }
        });

        //Once remove button is clicked
        $(wrapper).on('click', '.remove_button2', function(e){
            e.preventDefault();
            $(this).parent('div').parent('div').remove(); //Remove field html
            x--; //Decrement field counter
        });
    });

    $(document).ready(function(){
        var maxField = 10; //Input fields increment limitation
        var addButton = $('.add_button3'); //Add button selector
        var wrapper = $('.field_wrapper3'); //Input field wrapper
        var fieldHTML = '<div class="row">\
                            <div class="col-md-5">\
                                <label for="lefticon" class="control-label">Link Text<span class="red">*</span></label>\
                                <span class="input-with-icon">\
                                    <input type="text" class="form-control requiredCheck" data-check="Third Column Link Text" name="footer_link_name3[]" autocomplete="off">\
                                </span>\
                            </div>\
                            <div class="col-md-5">\
                                <label for="lefticon" class="control-label">Link<span class="red">*</span></label>\
                                <span class="input-with-icon">\
                                    <input type="text" class="form-control requiredCheck" data-check="Third Column Link" name="footer_link3[]" autocomplete="off">\
                                </span>\
                            </div>\
                            <div class="col-md-2" style="margin-top: 33px;">\
                                <a href="javascript:void(0);" class="remove_button3" title="Remove field"><i class="fa fa-minus-circle fa-2x text-danger"></i></a>\
                            </div>\
                        </div>'; //New input field html
        var x = 1; //Initial field counter is 1

        //Once add button is clicked
        $(addButton).click(function(){
            //Check maximum number of input fields
            if(x < maxField){
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); //Add field html
            }
        });

        //Once remove button is clicked
        $(wrapper).on('click', '.remove_button3', function(e){
            e.preventDefault();
            $(this).parent('div').parent('div').remove(); //Remove field html
            x--; //Decrement field counter
        });
    });
</script>

<!-- Script -->
