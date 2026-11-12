<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ config('app.page_title') }}</title><link href="<?= env('UPLOADS_URL').$generalSetting->site_favicon ?>" rel="icon"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">@include('front.partials.cms-styles')
<style>.join-page{background:#f5f7fb;min-height:70vh;padding:48px 22px 76px}.join-shell{max-width:850px;margin:auto}.join-head{margin-bottom:26px}.join-head h1{margin:9px 0;color:#172640;font-size:36px}.join-head p{color:#687990;line-height:1.7}.join-card{background:#fff;border:1px solid #e1e7f0;border-radius:16px;box-shadow:0 16px 45px rgba(28,49,84,.08);overflow:hidden}.join-section{padding:28px 30px;border-bottom:1px solid #e9edf3}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.field.full{grid-column:1/-1}.name-fields{display:grid;grid-template-columns:105px 1fr 1fr;gap:18px}.field label{display:block;margin-bottom:7px;color:#34445d;font-size:13px;font-weight:700}.input{box-sizing:border-box;width:100%;min-height:45px;padding:10px 13px;border:1px solid #d8e0eb;border-radius:9px;font:inherit}.error{display:block;color:#c93642;font-size:12px;margin-top:5px}.alert{margin-bottom:20px;padding:16px 18px;border-radius:10px}.success{background:#e8f8f0;color:#13784b}.danger,.closed{background:#fff0f1;color:#a82d38}.submit{padding:13px 21px;border:0;border-radius:9px;background:#397ef6;color:#fff;font-weight:800}.consent{display:flex;gap:9px;color:#56667e;font-size:13px}.actions{display:flex;justify-content:space-between;align-items:center;gap:20px}.honeypot{position:absolute;left:-10000px}.registration-complete{padding:34px;border:1px solid #cdebdc;border-radius:16px;background:#fff;box-shadow:0 16px 45px rgba(28,49,84,.08);text-align:center}.registration-complete-icon{display:grid;place-items:center;width:58px;height:58px;margin:0 auto 16px;border-radius:50%;background:#e4f8ee;color:#13865b;font-size:28px;font-weight:800}.registration-complete h2{margin:0 0 10px;color:#172640}.registration-complete p{max-width:610px;margin:0 auto;color:#687990;line-height:1.65}.registration-number{display:inline-block;margin:17px 0 20px;padding:8px 13px;border-radius:8px;background:#edf4ff;color:#2768d5;font-weight:800}.explore-button{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 20px;border-radius:9px;background:#397ef6;color:#fff;text-decoration:none;font-weight:800}@media(max-width:700px){.join-page{padding:20px 9px 48px}.join-head{margin-bottom:16px;padding:0 3px}.join-head h1{margin-top:4px;font-size:27px;line-height:1.18}.join-head p{font-size:13px;line-height:1.55}.join-card{border-radius:12px;box-shadow:0 8px 24px rgba(28,49,84,.06)}.join-section{padding:19px 14px}.join-section h2{font-size:22px}.form-grid{grid-template-columns:1fr;gap:13px}.field.full{grid-column:auto}.name-fields{grid-template-columns:1fr;gap:13px}.name-fields>.field:first-child{max-width:120px}.name-fields .last-name{grid-column:auto}.field label{margin-bottom:5px;font-size:12px}.input{min-height:43px;padding:9px 11px}.business-fields{margin-top:16px}.section-note{margin-bottom:14px}.actions{align-items:stretch;flex-direction:column;gap:14px}.submit{width:100%}.registration-complete{padding:24px 15px}.explore-button{width:100%}}
/* ===== compact, colourful join form ===== */
.s-blue{--th:#397ef6;--th2:#2a5fd0;--th-soft:#e8f0ff}.s-purple{--th:#8a5ce6;--th2:#6d45d6;--th-soft:#f2ecff}.s-teal{--th:#14b3a3;--th2:#0b8577;--th-soft:#e2f7f4}.s-green{--th:#22b26b;--th2:#138a50;--th-soft:#e4f7ec}
.join-shell{max-width:880px}
.join-head{position:relative;overflow:hidden;margin-bottom:-28px;padding:30px 32px 58px;border-radius:22px 22px 0 0;background:linear-gradient(125deg,#13295a,#397ef6 60%,#7257df);color:#fff}
.join-head:after{content:"";position:absolute;right:-60px;top:-80px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.1)}
.join-kicker{position:relative;display:inline-block;padding:5px 11px;border-radius:999px;background:rgba(255,255,255,.18);font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.join-head h1{position:relative;margin:12px 0 6px;color:#fff;font-size:32px;line-height:1.15}
.join-head p{position:relative;max-width:620px;margin:0;color:#dbe7ff;font-size:15px;line-height:1.55}
.join-steps{position:relative;display:flex;flex-wrap:wrap;gap:8px;margin:16px 0 0;padding:0;list-style:none}
.join-steps li{display:flex;align-items:center;gap:7px;padding:5px 12px 5px 5px;border-radius:999px;background:rgba(255,255,255,.14);font-size:13px;font-weight:600}
.join-steps b{display:grid;place-items:center;width:22px;height:22px;border-radius:50%;background:#fff;color:#2a5fd0;font-size:12px}
.join-card{position:relative;z-index:1;border:0;border-radius:22px;box-shadow:0 20px 50px rgba(28,49,84,.12)}
.join-section{padding:22px 28px}
.sec-head{display:flex;align-items:center;gap:12px;margin:0 0 16px}
.sec-num{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,var(--th),var(--th2));color:#fff;font-size:17px;font-weight:800;box-shadow:0 8px 16px color-mix(in srgb,var(--th) 30%,transparent)}
.sec-head h2{margin:0;color:#172640;font-size:20px}
.sec-head p{margin:2px 0 0;color:#6b7890;font-size:13px}
.join-section.s-blue{border-top:4px solid var(--th)}
.join-section.s-purple{background:linear-gradient(180deg,#fbf9ff,#fff 120px)}
.form-grid{gap:12px 14px}
.field label{margin-bottom:5px;font-size:13px;color:#33435c}
.join-card .input{min-height:44px;padding:9px 12px;border-radius:10px;font-size:15px}
.join-card .input:focus{border-color:var(--th,#397ef6);box-shadow:0 0 0 3px color-mix(in srgb,var(--th,#397ef6) 20%,transparent);outline:none}
.join-card textarea.input{min-height:84px}
.business-fields{grid-template-columns:repeat(4,minmax(0,1fr))}
.business-fields>.field:not(.quarter){grid-column:span 2}
.business-fields>.field.full{grid-column:1/-1}
.sub-head{display:flex;align-items:center;gap:10px;margin-top:6px;padding:10px 12px;border-radius:12px;background:var(--th-soft)}
.sub-icon{display:grid;place-items:center;flex:0 0 32px;width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,var(--th),var(--th2))}
.sub-icon svg{width:18px;height:18px;fill:#fff}
.sub-head h3{margin:0;color:#0b5f56;font-size:15px}
.sub-head p{margin:1px 0 0;color:#4d6b67;font-size:12px}
.category-help{display:block;margin:-2px 0 8px;color:#6b7890;font-size:12px}
.category-list{display:flex;flex-wrap:wrap;gap:8px;max-height:170px;overflow:auto;padding:10px;border:1px solid #e3e8f1;border-radius:12px;background:#fff}
.category-option{display:inline-flex!important;align-items:center;gap:6px;margin:0!important;padding:7px 12px;border:1px solid #e1e6f0;border-radius:999px;background:#f8fafc;color:#33435c;font-size:13px!important;font-weight:600;cursor:pointer;transition:.15s}
.category-option input{width:15px;height:15px;margin:0;accent-color:var(--th2)}
.category-option:has(input:checked){border-color:var(--th);background:var(--th-soft);color:var(--th2)}
#profile-photo{padding:6px}
#profile-photo::file-selector-button{margin-right:10px;padding:7px 14px;border:0;border-radius:8px;background:var(--th-soft);color:var(--th2);font:inherit;font-size:13px;font-weight:700;cursor:pointer}
.field .photo-help,.photo-help{display:block;margin-top:5px;color:#6b7890;font-size:12px!important;line-height:1.45}
.optional{color:#8a96a8;font-weight:500}
.join-section.actions{display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:0;background:var(--th-soft)}
.consent{display:flex;align-items:flex-start;gap:9px;color:#33435c;font-size:13px;line-height:1.5}
.consent input{width:18px;height:18px;margin-top:1px;flex:0 0 18px;accent-color:var(--th2)}
.submit{flex:0 0 auto;min-height:48px;padding:0 24px;border-radius:999px;background:linear-gradient(135deg,#397ef6,#6b52dc);font-size:15px;box-shadow:0 10px 22px rgba(57,126,246,.3);cursor:pointer}
.submit:hover{filter:brightness(1.05)}
@media(max-width:700px){
.join-head{padding:22px 18px 50px;border-radius:18px 18px 0 0}
.join-head h1{font-size:25px}.join-head p{font-size:14px}
.join-card{border-radius:18px}
.join-section{padding:18px 16px}
.sec-head h2{font-size:18px}
.business-fields{grid-template-columns:1fr 1fr}
.business-fields>.field:not(.quarter),.business-fields>.field.full{grid-column:1/-1}
.join-card .input{font-size:16px}
.join-section.actions{flex-direction:column;align-items:stretch}
.submit{width:100%}
}
@media(max-width:380px){.business-fields{grid-template-columns:1fr}.business-fields>.field.quarter{grid-column:1/-1}}
</style></head><body>
<style>.join-section h2{margin-top:0}.section-note{margin:-8px 0 20px;color:#687990;font-size:13px;line-height:1.55}.business-fields{margin-top:22px}.category-help{display:block;margin:0 0 8px;color:#687990;font-size:12px}.category-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;max-height:228px;overflow-y:auto;padding:8px;border:1px solid #d8e0eb;border-radius:10px;background:#f8fafc;scrollbar-gutter:stable}.category-list::-webkit-scrollbar{width:8px}.category-list::-webkit-scrollbar-thumb{border:2px solid #f8fafc;border-radius:10px;background:#b8c5d8}.category-option{display:flex!important;align-items:center;gap:7px;min-height:38px;margin:0!important;padding:7px 9px;border:1px solid #dde4ee;border-radius:7px;background:#fff;font-size:12px;font-weight:650!important;cursor:pointer}.category-option:hover{border-color:#a9c5f7;background:#f5f9ff}.category-option input{width:15px;height:15px;flex:0 0 auto}.category-option:has(input:checked){border-color:#397ef6;background:#edf4ff;color:#245fc4}textarea.input{resize:vertical}@media(max-width:800px){.category-list{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:520px){.category-list{grid-template-columns:1fr;max-height:210px}}</style>
<style>.optional{color:#7a879a;font-weight:500}.photo-help{display:block;margin-top:6px;color:#687990;font-size:11px;line-height:1.45}.photo-help.is-working{color:#2768d5}.photo-help.is-ready{color:#13784b}.photo-help.is-error{color:#c93642}.upload-popup[hidden]{display:none}.upload-popup{position:fixed;z-index:10000;inset:0;display:grid;place-items:center;padding:18px;background:rgba(16,31,57,.56)}.upload-popup-card{width:min(390px,100%);padding:24px;border-radius:15px;background:#fff;box-shadow:0 22px 70px rgba(9,24,48,.28);text-align:center}.upload-popup-icon{display:grid;place-items:center;width:48px;height:48px;margin:0 auto 13px;border-radius:50%;background:#fff0f1;color:#c93642;font-size:23px;font-weight:800}.upload-popup h3{margin:0 0 8px;color:#172640;font-size:20px}.upload-popup p{margin:0;color:#687990;font-size:14px;line-height:1.55}.upload-popup button{margin-top:18px;padding:10px 24px;border:0;border-radius:8px;background:#397ef6;color:#fff;font:inherit;font-weight:800;cursor:pointer}@media(max-width:800px){.photo-help{font-size:12px}}@media(max-width:767.98px){.input,select.input,textarea.input{font-size:16px}}@media(max-width:700px){.name-fields>.field:first-child{max-width:none}select.input{width:100%;font:inherit;font-size:16px}}
/* final overrides (after all earlier join styles) */
.join-card .category-list{display:flex;flex-wrap:wrap;gap:8px;max-height:176px;padding:10px;border:1px solid #e3e8f1;border-radius:12px;background:#fff}
.join-card .category-option{display:inline-flex!important;flex:0 0 auto;width:auto;min-height:38px;padding:7px 13px;border:1px solid #e1e6f0;border-radius:999px;background:#f8fafc;color:#33435c;font-size:13px!important;font-weight:600!important}
.join-card .category-option:hover{border-color:var(--th);background:#fff}
.join-card .category-option:has(input:checked){border-color:var(--th);background:var(--th-soft);color:var(--th2)}
.join-card .category-option:has(input:disabled){opacity:.5;cursor:not-allowed}
.join-card .name-fields{grid-template-columns:110px 1fr 1fr;gap:12px 14px}
@media(max-width:700px){
 .join-card .name-fields{grid-template-columns:104px minmax(0,1fr);gap:12px}
 .join-card .name-fields>.field:first-child{max-width:none}
 .join-card .name-fields .last-name{grid-column:1/-1}
 .join-card .s-blue .form-grid{grid-template-columns:1fr 1fr}
 .join-card .s-blue .form-grid>.field{grid-column:1/-1}
 .join-card .s-blue .form-grid>.field:has(input[name="mobile"]),.join-card .s-blue .form-grid>.field:has(input[name="whatsapp"]){grid-column:auto}
}
@media(max-width:340px){.join-card .s-blue .form-grid>.field{grid-column:1/-1!important}}

/* registration closed: guided visitor -> membership path */
.closed-path{position:relative;z-index:1;padding:24px 28px;border-radius:22px;background:#fff;box-shadow:0 20px 50px rgba(28,49,84,.12)}
.cp-notice{padding:12px 14px;border-radius:12px;background:#fff5e6;color:#8a4b00;font-size:14px;line-height:1.5}
.cp-notice strong{color:#6b3a00}
.closed-path h2{margin:20px 0 14px;color:#172640;font-size:22px}
.cp-steps{display:grid;gap:10px;margin:0;padding:0;list-style:none}
.cp-steps li{display:flex;align-items:flex-start;gap:12px;padding:12px 14px;border:1px solid #e6ebf3;border-radius:14px;background:#fafbfd}
.cp-steps b{display:grid;place-items:center;flex:0 0 32px;width:32px;height:32px;border-radius:50%;background:#e8eef8;color:#5b6b83;font-size:14px}
.cp-steps strong{display:block;color:#172640;font-size:15px}
.cp-steps span{display:block;margin-top:2px;color:#6b7890;font-size:13px;line-height:1.45}
.cp-steps li.now{border-color:#bcd3fb;background:#f3f7ff}
.cp-steps li.now b{background:linear-gradient(135deg,#397ef6,#6b52dc);color:#fff}
.cp-steps li.done b{background:#22b26b;color:#fff}
.cp-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
.cp-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:0 22px;border:1px solid #d6e0ee;border-radius:999px;background:#fff;color:#2a5fd0;font-size:15px;font-weight:700;text-decoration:none}
.cp-btn.primary{border:0;background:linear-gradient(135deg,#397ef6,#6b52dc);color:#fff;box-shadow:0 10px 22px rgba(57,126,246,.3)}
@media(max-width:700px){.closed-path{padding:18px 16px;border-radius:18px}.closed-path h2{font-size:19px}.cp-actions{flex-direction:column}.cp-btn{width:100%}}
</style>
@include('front.partials.cms-header')
<main class="join-page"><div class="join-shell"><header class="join-head"><span class="join-kicker">Business membership</span><h1>Join as a business member</h1><p>Tell us about you and your business. Once approved, you can manage your business page on web and mobile.</p><ol class="join-steps" aria-hidden="true"><li><b>1</b>You</li><li><b>2</b>Business</li><li><b>3</b>Approval</li></ol></header>
@if(session('registration_success'))
<section class="registration-complete"><div class="registration-complete-icon">✓</div><h2>Registration received</h2><p>{{session('registration_requires_approval') ? 'Your business-member registration is awaiting administrator approval. You can explore the member businesses while it is being reviewed.' : 'Your registration is complete. You can now explore the network.'}}</p><div class="registration-number">Registration number: {{session('registration_success')}}</div><br><a class="explore-button" href="{{route('members.index')}}">Explore members and businesses →</a></section>
@else
@if($errors->any())<div class="alert danger"><strong>Please review the form.</strong> {{$errors->first()}}</div>@endif
@unless($registrationOpen)
@php $signedIn = Auth::guard('member')->check(); $applyUrl = route('member.membership.create'); @endphp
<section class="closed-path">
  <div class="cp-notice"><strong>Direct business registration is paused.</strong> {{ $registrationSettings->closed_message ?: 'You can still join the network as a visitor and apply for business membership.' }}</div>
  <h2>You can still become a business member</h2>
  <ol class="cp-steps">
    <li class="{{ $signedIn ? 'done' : 'now' }}"><b>1</b><div><strong>Join as a visitor</strong><span>Verify your mobile number. It takes a minute.</span></div></li>
    <li class="{{ $signedIn ? 'now' : '' }}"><b>2</b><div><strong>Apply for business membership</strong><span>Choose a plan and share your payment details.</span></div></li>
    <li><b>3</b><div><strong>Admin approves, then add your business</strong><span>Once approved, you can add and publish your business page.</span></div></li>
  </ol>
  <div class="cp-actions">
    @if($signedIn)
      <a class="cp-btn primary" href="{{ $applyUrl }}">Apply for business membership →</a>
      <a class="cp-btn" href="{{ route('dashboard.index') }}">Go to my dashboard</a>
    @else
      <a class="cp-btn primary" href="{{ route('guest.register', ['redirect' => $applyUrl]) }}">Join as a visitor →</a>
      <a class="cp-btn" href="{{ url('member') }}">Already registered? Log in</a>
    @endif
  </div>
</section>
@else
<form class="join-card" id="join-registration-form" method="post" enctype="multipart/form-data" action="{{route('join.store')}}">@csrf<div class="honeypot"><input name="website" tabindex="-1" autocomplete="off"></div>
<section class="join-section s-blue"><div class="sec-head"><span class="sec-num">1</span><div><h2>Your details</h2><p>How members can reach you.</p></div></div><div class="form-grid">
<div class="field full name-fields"><div class="field"><label>Title</label><select class="input" name="salutation"><option value="">Select</option>@foreach(['Mr.','Mrs.','Ms.','Miss','Dr.','Prof.','Mx.'] as $title)<option value="{{$title}}" @selected(old('salutation')===$title)>{{$title}}</option>@endforeach</select></div><div class="field"><label>First name *</label><input class="input" name="first_name" value="{{old('first_name')}}" required>@error('first_name')<span class="error">{{$message}}</span>@enderror</div><div class="field last-name"><label>Last name</label><input class="input" name="last_name" value="{{old('last_name')}}"></div></div>
<div class="field"><label>Email address *</label><input class="input" type="email" name="email" value="{{old('email')}}" required>@error('email')<span class="error">{{$message}}</span>@enderror</div>
<div class="field"><label>Mobile number *</label><input class="input" name="mobile" inputmode="numeric" maxlength="10" value="{{old('mobile')}}" required>@error('mobile')<span class="error">{{$message}}</span>@enderror</div>
<div class="field"><label>WhatsApp number</label><input class="input" name="whatsapp" inputmode="numeric" maxlength="10" value="{{old('whatsapp')}}"></div>
<div class="field"><label>Upload your photo <span class="optional">(optional)</span></label><input class="input" id="profile-photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp"><span class="photo-help" id="profile-photo-status" aria-live="polite">JPG, PNG or WebP · square works best · large photos are compressed automatically.</span>@error('profile_photo')<span class="error">{{$message}}</span>@enderror</div>
</div></section>
<section class="join-section s-purple"><div class="sec-head"><span class="sec-num">2</span><div><h2>Your business</h2><p>It stays private until an administrator approves it.</p></div></div><div class="business-fields form-grid"><div class="field full"><label>Business name *</label><input class="input" name="business_name" value="{{old('business_name')}}" required>@error('business_name')<span class="error">{{$message}}</span>@enderror</div><div class="field full"><label>Business categories *</label><span class="category-help">Pick up to three.</span><div class="category-list">@foreach($businessCategories as $category)<label class="category-option"><input type="checkbox" name="category_ids[]" value="{{$category->bcm_id}}" @checked(in_array($category->bcm_id,old('category_ids',[])))><span>{{$category->name}}</span></label>@endforeach</div>@error('category_ids')<span class="error">{{$message}}</span>@enderror</div><div class="field"><label>Business email</label><input class="input" type="email" name="business_email" value="{{old('business_email')}}"></div><div class="field"><label>Business phone</label><input class="input" name="business_phone" maxlength="15" value="{{old('business_phone')}}"></div><div class="field full"><label>Short business description</label><textarea class="input" name="business_description" rows="3" placeholder="What do you offer? Who are your customers?">{{old('business_description')}}</textarea></div><div class="field full sub-head s-teal"><span class="sub-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg></span><div><h3>Business address</h3><p>Public location. Online businesses can leave the street blank.</p></div></div><div class="field full"><label>Address line 1</label><input class="input" name="business_address_line_1" value="{{old('business_address_line_1')}}"></div><div class="field full"><label>Address line 2</label><input class="input" name="business_address_line_2" value="{{old('business_address_line_2')}}"></div><div class="field quarter"><label>City</label><input class="input" name="business_city" value="{{old('business_city')}}"></div><div class="field quarter"><label>Country</label><select class="input" id="business_country" name="business_country"><option value="">Select country</option>@foreach($countries as $country)<option value="{{$country->id}}" @selected((string)old('business_country',$defaultCountryId)===(string)$country->id)>{{$country->name}}</option>@endforeach</select></div><div class="field quarter"><label>State</label><select class="input" id="business_state" name="business_state" data-selected="{{old('business_state')}}"><option value="">Select a country first</option></select></div><div class="field quarter"><label>PIN code</label><input class="input" name="business_pincode" inputmode="numeric" maxlength="6" value="{{old('business_pincode')}}"></div></div></section>
<section class="join-section actions s-green"><label class="consent"><input type="checkbox" name="consent" value="1" required><span>I confirm that the information is correct and may be used to create my member profile.</span></label><button class="submit">Submit registration <span aria-hidden="true">→</span></button></section></form>@endunless
@endif
</div></main>@include('front.partials.cms-footer')
@if($registrationOpen)<div class="upload-popup" id="upload-error-popup" role="alertdialog" aria-modal="true" aria-labelledby="upload-error-title" hidden><div class="upload-popup-card"><div class="upload-popup-icon">!</div><h3 id="upload-error-title">Photo cannot be uploaded</h3><p id="upload-error-message">Please choose a smaller JPG, PNG or WebP photo.</p><button type="button" id="upload-error-close">Choose another photo</button></div></div>@endif
@if($registrationOpen)<script>(()=>{const checks=[...document.querySelectorAll('.category-list input[name="category_ids[]"]')];const refresh=()=>{const selected=checks.filter(check=>check.checked).length;checks.forEach(check=>check.disabled=!check.checked&&selected>=3)};checks.forEach(check=>check.addEventListener('change',refresh));refresh()})()</script><script>(()=>{const c=document.getElementById('business_country'),s=document.getElementById('business_state');if(!c||!s)return;const selected=s.dataset.selected;async function load(){if(!c.value){s.innerHTML='<option value="">Select a country first</option>';return}const r=await fetch(`{{url('/api/v1/utilities/countries')}}/${c.value}/states`,{headers:{Accept:'application/json'}});if(!r.ok)return;const rows=await r.json();s.innerHTML='<option value="">Select state</option>'+rows.map(x=>`<option value="${x.id}">${x.name}</option>`).join('');if(selected)s.value=selected}c.addEventListener('change',load);load()})()</script><script>(()=>{const input=document.getElementById('profile-photo'),form=document.getElementById('join-registration-form'),status=document.getElementById('profile-photo-status'),submit=form?.querySelector('.submit');if(!input||!form||!status)return;const allowed=['image/jpeg','image/png','image/webp'],targetBytes=900*1024,maxSourceBytes=20*1024*1024,maxDimension=1200;let processing=null,resubmitting=false;const size=bytes=>bytes<1024*1024?`${Math.max(1,Math.round(bytes/1024))} KB`:`${(bytes/1024/1024).toFixed(1)} MB`;const message=(text,state='')=>{status.textContent=text;status.className='photo-help'+(state?` is-${state}`:'')};const encode=(canvas,quality)=>new Promise((resolve,reject)=>canvas.toBlob(blob=>blob?resolve(blob):reject(new Error('Image conversion failed')),'image/jpeg',quality));const optimize=file=>new Promise((resolve,reject)=>{const url=URL.createObjectURL(file),image=new Image;image.onload=async()=>{try{const scale=Math.min(1,maxDimension/Math.max(image.naturalWidth,image.naturalHeight)),canvas=document.createElement('canvas');canvas.width=Math.max(1,Math.round(image.naturalWidth*scale));canvas.height=Math.max(1,Math.round(image.naturalHeight*scale));const context=canvas.getContext('2d');context.fillStyle='#fff';context.fillRect(0,0,canvas.width,canvas.height);context.drawImage(image,0,0,canvas.width,canvas.height);let blob=await encode(canvas,.82);if(blob.size>targetBytes)blob=await encode(canvas,.68);if(blob.size>targetBytes)blob=await encode(canvas,.55);resolve(blob)}catch(error){reject(error)}finally{URL.revokeObjectURL(url)}};image.onerror=()=>{URL.revokeObjectURL(url);reject(new Error('This image could not be opened'))};image.src=url});const prepare=async()=>{input.setCustomValidity('');const file=input.files?.[0];if(!file){message('Recommended: 600 × 600 px square. Large JPG, PNG or WebP photos are automatically optimized.');return}if(!allowed.includes(file.type)){input.value='';input.setCustomValidity('Choose a JPG, PNG or WebP photo.');message('Choose a JPG, PNG or WebP photo.','error');return}if(file.size>maxSourceBytes){input.value='';input.setCustomValidity('Choose a photo smaller than 20 MB.');message('This photo is too large to process. Choose one smaller than 20 MB.','error');return}if(file.size<=targetBytes){message(`Photo ready (${size(file.size)}).`,'ready');return}if(typeof DataTransfer==='undefined'){input.value='';input.setCustomValidity('Choose a photo smaller than 900 KB.');message('Your browser cannot optimize this photo. Choose one smaller than 900 KB.','error');return}message('Optimizing your photo for upload…','working');if(submit)submit.disabled=true;try{const blob=await optimize(file),name=(file.name.replace(/\.[^.]+$/,'')||'member-photo')+'.jpg',optimized=new File([blob],name,{type:'image/jpeg',lastModified:Date.now()}),transfer=new DataTransfer;transfer.items.add(optimized);input.files=transfer.files;message(`Photo optimized from ${size(file.size)} to ${size(optimized.size)}.`,'ready')}catch(error){input.value='';input.setCustomValidity('Choose a smaller JPG, PNG or WebP photo.');message('This photo could not be optimized. Please choose a smaller JPG, PNG or WebP image.','error')}finally{if(submit)submit.disabled=false}};input.addEventListener('change',()=>{processing=prepare().finally(()=>processing=null)});form.addEventListener('submit',async event=>{if(resubmitting||!processing)return;event.preventDefault();await processing;if(!form.reportValidity())return;resubmitting=true;form.requestSubmit()})})()</script>@endif
@if($registrationOpen)<script>(()=>{const status=document.getElementById('profile-photo-status'),popup=document.getElementById('upload-error-popup'),text=document.getElementById('upload-error-message'),close=document.getElementById('upload-error-close');if(!status||!popup||!text||!close)return;let last='';const hide=()=>{popup.hidden=true};const show=()=>{if(!status.classList.contains('is-error')||status.textContent===last)return;last=status.textContent;text.textContent=status.textContent;popup.hidden=false;close.focus()};new MutationObserver(show).observe(status,{attributes:true,childList:true,characterData:true,subtree:true});close.addEventListener('click',hide);popup.addEventListener('click',event=>{if(event.target===popup)hide()});document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!popup.hidden)hide()})})()</script>@endif
</body></html>
