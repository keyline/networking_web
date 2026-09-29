<style>
.login-methods{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;padding:5px;background:#f3f6fa;border-radius:11px;margin-bottom:25px}.login-method{border:0;background:transparent;border-radius:8px;padding:9px 5px;color:#6b7890;font-size:12px;font-weight:700}.login-method.active{background:#fff;color:#377dff;box-shadow:0 2px 8px rgba(28,50,84,.1)}.method-panel{display:none}.method-panel.active{display:block}.secure-note{padding:10px 12px;border-radius:8px;background:#f6f8fb;color:#6b7890;font-size:12px;text-align:center}.otp-code{font-size:1.45rem!important;letter-spacing:.5em;text-align:center;font-weight:800}@media(max-width:440px){.login-method{font-size:10px;padding:8px 2px}}
</style>
<main id="content" role="main" class="main">
  <div class="position-fixed top-0 end-0 start-0 bg-img-start" style="height:32rem;background-image:url(<?=env('ADMIN_ASSETS_URL')?>assets/svg/components/card-6.svg)"><div class="shape shape-bottom zi-1"><svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1921 273"><polygon fill="#fff" points="0,273 1921,273 1921,100"/></svg></div></div>
  <div class="container py-5 py-sm-7">
    <a class="d-flex justify-content-center mb-5" href="<?=url('/admin')?>"><img class="zi-2" src="<?=env('UPLOADS_URL').$generalSetting->site_logo?>" alt="Net-Works" style="width:9rem;max-height:70px;object-fit:contain"></a>
    <div class="mx-auto" style="max-width:31rem"><div class="card card-lg mb-5 border-0 shadow-sm"><div class="card-body p-4 p-sm-5">
      @if(session('success_message'))<div class="alert alert-success border-0">{{session('success_message')}}</div>@endif
      @if(session('error_message'))<div class="alert alert-danger border-0">{{session('error_message')}}</div>@endif
      @if($errors->any())<div class="alert alert-danger border-0">{{$errors->first()}}</div>@endif

      @if(session('admin_otp_admin_id'))
        <form action="{{route('admin.login.verify-otp')}}" method="POST">@csrf
          <div class="text-center mb-5"><span class="badge bg-soft-primary text-primary mb-3">{{ucfirst(session('admin_otp_channel'))}} verification</span><h1 class="display-5 mb-2">Enter sign-in code</h1><p class="text-muted mb-0">We sent a six-digit code to<br><strong>{{session('admin_otp_display')}}</strong></p></div>
          <input type="hidden" name="admin_id" value="{{session('admin_otp_admin_id')}}"><label class="form-label" for="adminOtp">One-time code</label><input type="text" class="form-control form-control-lg otp-code" name="otp" id="adminOtp" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="••••••" required autofocus>
          <div class="d-grid mt-4"><button class="btn btn-primary btn-lg" type="submit">Verify and open dashboard</button></div>
        </form>
        <form action="{{route('admin.login.resend-otp')}}" method="POST" class="mt-3">@csrf<button class="btn btn-white w-100" type="submit">Send a new code</button></form><a href="{{url('/admin?reset=1')}}" class="d-block text-center mt-3 small">Choose another login method</a>
      @else
        <div class="text-center mb-4"><h1 class="display-5 mb-2">Admin sign in</h1><p class="text-muted mb-0">Choose your preferred secure login method.</p></div>
        <div class="login-methods" role="tablist"><button class="login-method active" type="button" data-method="password"><i class="bi-person-badge d-block mb-1"></i>User ID</button><button class="login-method" type="button" data-method="email_otp"><i class="bi-envelope d-block mb-1"></i>Email OTP</button><button class="login-method" type="button" data-method="mobile_otp"><i class="bi-phone d-block mb-1"></i>Mobile OTP</button></div>
        <form action="{{url('/admin')}}" method="POST">@csrf<input type="hidden" name="login_method" id="loginMethod" value="{{old('login_method','password')}}">
          <div class="method-panel active" data-panel="password"><div class="mb-3"><label class="form-label" for="identifier">User ID</label><input class="form-control form-control-lg" id="identifier" name="identifier" value="{{old('identifier')}}" placeholder="Member ID or email" autocomplete="username"></div><div class="mb-3"><label class="form-label d-flex justify-content-between" for="password"><span>Password</span><a href="{{url('admin/forgot-password')}}">Forgot password?</a></label><input class="form-control form-control-lg" type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password"></div><div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Sign in securely</button></div></div>
          <div class="method-panel" data-panel="email_otp"><div class="mb-3"><label class="form-label" for="email">Approved admin email</label><input class="form-control form-control-lg" type="email" id="email" name="email" value="{{old('email')}}" placeholder="name@company.com" autocomplete="email"></div><div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Email me a code</button></div></div>
          <div class="method-panel" data-panel="mobile_otp"><div class="mb-3"><label class="form-label" for="mobile">Approved mobile number</label><input class="form-control form-control-lg" type="tel" id="mobile" name="mobile" value="{{old('mobile')}}" placeholder="Enter registered mobile" autocomplete="tel"></div><div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Text me a code</button></div></div>
        </form>
        <div class="secure-note mt-4"><i class="bi-shield-check me-1"></i> OTP codes expire in 10 minutes and can only be used once.</div>
      @endif
    </div></div><div class="position-relative text-center zi-1"><small class="text-cap text-body">Developed & maintained by <a target="_blank" href="https://keylines.net/">Keyline</a></small></div></div>
  </div>
</main>
@if(!session('admin_otp_admin_id'))
<script>
document.addEventListener('DOMContentLoaded',()=>{const tabs=[...document.querySelectorAll('.login-method')],panels=[...document.querySelectorAll('.method-panel')],method=document.getElementById('loginMethod');function select(name){method.value=name;tabs.forEach(t=>t.classList.toggle('active',t.dataset.method===name));panels.forEach(p=>{const active=p.dataset.panel===name;p.classList.toggle('active',active);p.querySelectorAll('input').forEach(i=>{if(i.type!=='hidden')i.required=active})})}tabs.forEach(t=>t.addEventListener('click',()=>select(t.dataset.method)));select(method.value)})
</script>
@endif
