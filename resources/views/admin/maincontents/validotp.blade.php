<?php
use App\Helpers\Helper;
?>
<!-- ========== MAIN CONTENT ========== -->
  <main id="content" role="main" class="main">
    <div class="position-fixed top-0 end-0 start-0 bg-img-start" style="height: 32rem; background-image: url(<?=env('ADMIN_ASSETS_URL')?>assets/svg/components/card-6.svg);">
      <!-- Shape -->
      <div class="shape shape-bottom zi-1">
        <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 1921 273">
          <polygon fill="#fff" points="0,273 1921,273 1921,0 " />
        </svg>
      </div>
      <!-- End Shape -->
    </div>
    <!-- Content -->
    <div class="container py-5 py-sm-7">
      <a class="d-flex justify-content-center mb-5" href="<?=url('/admin')?>">
        <img class="zi-2" src="<?=env('UPLOADS_URL').$generalSetting->site_logo?>" alt="Image Description" style="width: 8rem;">
      </a>
      <div class="mx-auto" style="max-width: 30rem;">
        <!-- Card -->
        <div class="card card-lg mb-5">
          <div class="card-body text-center">
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
            <!-- Form -->
            <form action="" method="POST">
              @csrf
              <div class="mb-4">
                <img class="avatar avatar-xxl avatar-4x3" src="<?=env('ADMIN_ASSETS_URL')?>assets/svg/illustrations/oc-unlock.svg" alt="Image Description" data-hs-theme-appearance="default">
                <img class="avatar avatar-xxl avatar-4x3" src="<?=env('ADMIN_ASSETS_URL')?>assets/svg/illustrations-light/oc-unlock.svg" alt="Image Description" data-hs-theme-appearance="dark">
              </div>

              <div class="mb-5">
                <h1 class="display-5"><?=$page_header?></h1>
                <p class="mb-0">We sent a verification code to your email.</p>
                <p>Enter the code from the email in the field below.</p>
              </div>

              <div class="row gx-2 gx-sm-3">
                <div class="col">
                  <!-- Form -->
                  <div class="mb-4">
                    <input type="text" class="form-control form-control-single-number" name="otp1" id="twoStepVerificationSrCodeInput1" placeholder="" aria-label="" maxlength="1" autocomplete="off" autocapitalize="off" spellcheck="false" autofocus onkeypress="return isNumber(event)" required>
                  </div>
                  <!-- End Form -->
                </div>

                <div class="col">
                  <!-- Form -->
                  <div class="mb-4">
                    <input type="text" class="form-control form-control-single-number" name="otp2" id="twoStepVerificationSrCodeInput2" placeholder="" aria-label="" maxlength="1" autocomplete="off" autocapitalize="off" spellcheck="false" onkeypress="return isNumber(event)" required>
                  </div>
                  <!-- End Form -->
                </div>

                <div class="col">
                  <!-- Form -->
                  <div class="mb-4">
                    <input type="text" class="form-control form-control-single-number" name="otp3" id="twoStepVerificationSrCodeInput3" placeholder="" aria-label="" maxlength="1" autocomplete="off" autocapitalize="off" spellcheck="false" onkeypress="return isNumber(event)" required>
                  </div>
                  <!-- End Form -->
                </div>

                <div class="col">
                  <!-- Form -->
                  <div class="mb-4">
                    <input type="text" class="form-control form-control-single-number" name="otp4" id="twoStepVerificationSrCodeInput4" placeholder="" aria-label="" maxlength="1" autocomplete="off" autocapitalize="off" spellcheck="false" onkeypress="return isNumber(event)" required>
                  </div>
                  <!-- End Form -->
                </div>
              </div>

              <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg">Verify My Account</button>
              </div>

              <div class="text-center">
                <p>Haven't received it? <a href="<?=url('admin/resendOtp/' . Helper::encoded($email))?>">Resend a new code.</a></p>
              </div>
            </form>
            <!-- End Form -->
          </div>
        </div>
        <!-- End Card -->
        <!-- Footer -->
        <div class="position-relative text-center zi-1">
          <small class="text-cap text-body mb-4">
            Developed & maintained by <a target="_blank" href="https://keylines.net/">Keyline</a>
          </small>
        </div>
        <!-- End Footer -->
      </div>
    </div>
    <!-- End Content -->
  </main>
  <!-- ========== END MAIN CONTENT ========== -->