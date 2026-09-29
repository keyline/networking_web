<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $title }}</title>
  <link href="<?= env('UPLOADS_URL').$generalSetting->site_favicon ?>" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @include('front.partials.cms-styles')
  <style>
    .join-page{background:#f5f7fb;min-height:70vh;padding:48px 22px 76px}.join-shell{max-width:1120px;margin:auto}.join-head{max-width:760px;margin-bottom:26px}.join-kicker{color:#397ef6;font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.join-head h1{margin:9px 0 10px;color:#172640;font-size:36px}.join-head p{margin:0;color:#687990;line-height:1.7}.join-card{overflow:hidden;background:#fff;border:1px solid #e1e7f0;border-radius:16px;box-shadow:0 16px 45px rgba(28,49,84,.08)}.join-section{padding:28px 30px;border-bottom:1px solid #e9edf3}.join-section:last-child{border:0}.section-heading{display:flex;align-items:flex-start;gap:12px;margin-bottom:21px}.step{display:grid;place-items:center;flex:0 0 30px;height:30px;border-radius:9px;background:#eaf2ff;color:#397ef6;font-size:13px;font-weight:800}.section-heading h2{margin:0;color:#22314b;font-size:18px}.section-heading p{margin:4px 0 0;color:#7b889b;font-size:13px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.field.full{grid-column:1/-1}.field label{display:block;margin-bottom:7px;color:#34445d;font-size:13px;font-weight:700}.field label span{color:#dc3545}.input{box-sizing:border-box;width:100%;min-height:45px;padding:10px 13px;border:1px solid #d8e0eb;border-radius:9px;background:#fff;color:#253650;font:inherit;outline:none}.input:focus{border-color:#397ef6;box-shadow:0 0 0 3px rgba(57,126,246,.11)}textarea.input{min-height:112px;resize:vertical}.hint{display:block;margin-top:5px;color:#8793a5;font-size:11px}.error{display:block;margin-top:5px;color:#c93642;font-size:12px}.alert{margin-bottom:20px;padding:16px 18px;border-radius:10px}.alert.success{background:#e8f8f0;color:#13784b;border:1px solid #bfead5}.alert.danger{background:#fff0f1;color:#a82d38;border:1px solid #f2c7cb}.consent{display:flex;align-items:flex-start;gap:9px;color:#56667e;font-size:13px;line-height:1.5}.join-actions{display:flex;align-items:center;justify-content:space-between;gap:20px}.join-actions p{margin:0;color:#78869a;font-size:12px}.submit{min-width:190px;padding:13px 21px;border:0;border-radius:9px;background:#397ef6;color:#fff;font-size:14px;font-weight:800;cursor:pointer}.submit:hover{background:#2868dc}.honeypot{position:absolute!important;left:-10000px!important}@media(max-width:720px){.join-page{padding:30px 14px 55px}.join-head h1{font-size:29px}.join-section{padding:23px 19px}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.join-actions{align-items:stretch;flex-direction:column}.submit{width:100%}}
  </style>
</head>
<body>
@include('front.partials.cms-header')
<main class="join-page">
  <div class="join-shell">
    <header class="join-head"><div class="join-kicker">Community registration</div><h1>Join Net-Works</h1><p>Tell us about you and your business. Your profile will be reviewed by the administrator before it becomes visible in the business directory.</p></header>

    @if(session('registration_success'))<div class="alert success"><strong>Registration received.</strong> Your registration number is <strong>{{session('registration_success')}}</strong>. We will notify you after approval.</div>@endif
    @if($errors->any())<div class="alert danger"><strong>Please review the highlighted fields.</strong> {{ $errors->first() }}</div>@endif

    <form class="join-card" method="post" action="{{route('join.store')}}" enctype="multipart/form-data">
      @csrf
      <div class="honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
      <section class="join-section">
        <div class="section-heading"><span class="step">1</span><div><h2>Your details</h2><p>The primary contact and owner of this business profile.</p></div></div>
        <div class="form-grid">
          <div class="field"><label for="first_name">First name <span>*</span></label><input class="input" id="first_name" name="first_name" value="{{old('first_name')}}" required>@error('first_name')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="last_name">Last name</label><input class="input" id="last_name" name="last_name" value="{{old('last_name')}}">@error('last_name')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="email">Email address <span>*</span></label><input class="input" type="email" id="email" name="email" value="{{old('email')}}" required>@error('email')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="mobile">Mobile number <span>*</span></label><input class="input" inputmode="numeric" maxlength="10" id="mobile" name="mobile" value="{{old('mobile')}}" required>@error('mobile')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="whatsapp">WhatsApp number</label><input class="input" inputmode="numeric" maxlength="10" id="whatsapp" name="whatsapp" value="{{old('whatsapp')}}"><small class="hint">Leave blank if it is the same as your mobile.</small>@error('whatsapp')<span class="error">{{$message}}</span>@enderror</div>
        </div>
      </section>

      <section class="join-section">
        <div class="section-heading"><span class="step">2</span><div><h2>Business profile</h2><p>Information customers and other members will use to discover you.</p></div></div>
        <div class="form-grid">
          <div class="field"><label for="business_name">Business name <span>*</span></label><input class="input" id="business_name" name="business_name" value="{{old('business_name')}}" required>@error('business_name')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="business_category">Business category <span>*</span></label><select class="input" id="business_category" name="business_category" required><option value="">Select category</option>@foreach($categories as $category)<option value="{{$category->bcm_id}}" @selected(old('business_category')==$category->bcm_id)>{{$category->name}}</option>@endforeach</select>@error('business_category')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="business_email">Business email</label><input class="input" type="email" id="business_email" name="business_email" value="{{old('business_email')}}"><small class="hint">Leave blank to use your contact email.</small>@error('business_email')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="business_phone">Business phone</label><input class="input" inputmode="numeric" maxlength="10" id="business_phone" name="business_phone" value="{{old('business_phone')}}">@error('business_phone')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="business_whatsapp">Business WhatsApp</label><input class="input" inputmode="numeric" maxlength="10" id="business_whatsapp" name="business_whatsapp" value="{{old('business_whatsapp')}}">@error('business_whatsapp')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="gst_number">GST number</label><input class="input" id="gst_number" name="gst_number" maxlength="15" value="{{old('gst_number')}}" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()">@error('gst_number')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field full"><label for="description">About the business</label><textarea class="input" id="description" name="description" placeholder="Describe your products, services and ideal customers.">{{old('description')}}</textarea>@error('description')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="company_logo">Company logo</label><input class="input" type="file" id="company_logo" name="company_logo" accept=".jpg,.jpeg,.png,.webp"><small class="hint">JPG, PNG or WebP; maximum 2 MB.</small>@error('company_logo')<span class="error">{{$message}}</span>@enderror</div>
        </div>
      </section>

      <section class="join-section">
        <div class="section-heading"><span class="step">3</span><div><h2>Business address</h2><p>The primary location displayed on your business profile.</p></div></div>
        <div class="form-grid">
          <div class="field full"><label for="address_line_1">Address line 1 <span>*</span></label><input class="input" id="address_line_1" name="address_line_1" value="{{old('address_line_1')}}" required>@error('address_line_1')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field full"><label for="address_line_2">Address line 2</label><input class="input" id="address_line_2" name="address_line_2" value="{{old('address_line_2')}}">@error('address_line_2')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="city">City <span>*</span></label><input class="input" id="city" name="city" value="{{old('city')}}" required>@error('city')<span class="error">{{$message}}</span>@enderror</div>
          <div class="field"><label for="pincode">PIN code <span>*</span></label><input class="input" inputmode="numeric" maxlength="6" id="pincode" name="pincode" value="{{old('pincode')}}" required>@error('pincode')<span class="error">{{$message}}</span>@enderror</div>
        </div>
      </section>

      <section class="join-section">
        <div class="join-actions"><div><label class="consent"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required><span>I confirm that the information provided is correct and may be used to create my Net-Works member and business profile.</span></label>@error('consent')<span class="error">{{$message}}</span>@enderror</div><button class="submit" type="submit">Submit registration</button></div>
      </section>
    </form>
  </div>
</main>
@include('front.partials.cms-footer')
</body>
</html>
