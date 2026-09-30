<main id="content" role="main" class="main">
  <div class="position-fixed top-0 end-0 start-0 bg-img-start" style="height:32rem;background-image:url(<?=env('ADMIN_ASSETS_URL')?>assets/svg/components/card-6.svg)"><div class="shape shape-bottom zi-1"><svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1921 273"><polygon fill="#fff" points="0,273 1921,273 1921,100"/></svg></div></div>
  <div class="container py-5 py-sm-7">
    <a class="d-flex justify-content-center mb-5" href="<?=url('/admin')?>"><img class="zi-2" src="<?=env('UPLOADS_URL').$generalSetting->site_logo?>" alt="Net-Works" style="width:9rem;max-height:70px;object-fit:contain"></a>
    <div class="mx-auto" style="max-width:31rem"><div class="card card-lg mb-5 border-0 shadow-sm"><div class="card-body p-4 p-sm-5">
      @if(session('success_message'))<div class="alert alert-success border-0">{{session('success_message')}}</div>@endif
      @if(session('error_message'))<div class="alert alert-danger border-0">{{session('error_message')}}</div>@endif
      @if($errors->any())<div class="alert alert-danger border-0">{{$errors->first()}}</div>@endif

      <div class="text-center mb-4">
        <span class="badge bg-soft-primary text-primary mb-3">ADMINISTRATION PORTAL</span>
        <h1 class="display-5 mb-2">Admin sign in</h1>
        <p class="text-muted mb-0">Use your authorized admin email and password.</p>
      </div>
      <form action="{{url('/admin')}}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label" for="email">Email address</label>
          <input class="form-control form-control-lg" type="email" id="email" name="email" value="{{old('email')}}" placeholder="admin@example.com" autocomplete="username" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label d-flex justify-content-between" for="password"><span>Password</span><a href="{{url('admin/forgot-password')}}">Forgot password?</a></label>
          <input class="form-control form-control-lg" type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
        </div>
        <div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Sign in to admin dashboard</button></div>
      </form>
      <div class="mt-4 p-3 rounded bg-light text-muted text-center small"><i class="bi-shield-lock me-1"></i> This portal is restricted to authorized administrators.</div>
    </div></div><div class="position-relative text-center zi-1"><small class="text-cap text-body">Developed & maintained by <a target="_blank" href="https://keylines.net/">Keyline</a></small></div></div>
  </div>
</main>
