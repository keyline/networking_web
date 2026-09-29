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
              <div class="text-center">
                <div class="mb-5">
                  <h1 class="display-5"><?=$page_header?></h1>
                </div>
              </div>
              <!-- Form -->
              <div class="mb-4">
                <label class="form-label" for="signinSrEmail">Your email</label>
                <input type="email" class="form-control form-control-lg" name="email" id="signinSrEmail" tabindex="1" placeholder="email@address.com" aria-label="email@address.com" required>
                <span class="invalid-feedback">Please enter a valid email address.</span>
              </div>
              <!-- End Form -->
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Submit</button>

                <div class="text-center">
                  <a class="btn btn-link" href="<?=url('admin/')?>">
                    <i class="bi-chevron-left"></i> Back to Sign in
                  </a>
                </div>
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