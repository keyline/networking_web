@php
    $headerMember = Auth::guard('member')->user();
    $headerMemberName = $headerMember?->um_name;
    $headerInitials = $headerMemberName
        ? collect(explode(' ', $headerMemberName))->filter()->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('')
        : '';
@endphp
<header class="cms-header">
 <a class="cms-brand" href="{{url('/')}}"><img src="{{env('UPLOADS_URL').$generalSetting->site_logo}}" alt="{{$generalSetting->site_name??'Networx'}}"></a>
 <nav class="cms-nav" id="cmsNav" aria-label="Primary navigation">
  <a href="{{url('/')}}">Home</a>
  <a href="{{route('members.index')}}">Members</a>
  @foreach($headerNavigation as $item)<div class="cms-nav-item"><a href="{{url('page/'.$item->page_slug)}}">{{$item->nav_label?:$item->page_name}}@if($item->children->isNotEmpty()) <span class="caret">⌄</span>@endif</a>@if($item->children->isNotEmpty())<div class="cms-dropdown">@foreach($item->children as $child)<a href="{{url('page/'.$child->page_slug)}}">{{$child->nav_label?:$child->page_name}}</a>@endforeach</div>@endif</div>@endforeach
  <a href="{{url('events')}}">Events</a>
  @if($headerMember)
   <div class="cms-account">
    <div class="cms-account-person">
     <span class="cms-account-avatar" aria-hidden="true">{{$headerInitials?:'NW'}}</span>
     <div class="cms-account-details"><span class="cms-account-name">{{$headerMemberName}}</span><a class="cms-logout" href="{{route('member.logout')}}">Log out</a></div>
    </div>
    <a class="cms-dashboard" href="{{route('dashboard.index')}}">Dashboard</a>
   </div>
  @else
   <a href="{{route('join.create')}}">Join as member</a>
   <a class="cms-login" href="{{url('member')}}">Login</a>
   <a class="cms-visitor" href="{{route('guest.register')}}">Join as a visitor</a>
  @endif
 </nav>
 <button class="cms-menu-toggle" type="button" aria-label="Toggle menu" aria-controls="cmsNav" aria-expanded="false" onclick="this.setAttribute('aria-expanded',document.querySelector('.cms-nav').classList.toggle('open'))">☰</button>
</header>
<style>
.cms-nav .cms-login{margin-left:7px;padding:10px 16px;color:#fff;background:#397ef6}.cms-nav .cms-login:hover{color:#fff;background:#2868dc}.cms-nav .cms-visitor{padding:9px 15px;border:1px solid #397ef6;color:#2868dc;background:#fff}.cms-nav .cms-visitor:hover{color:#fff;background:#397ef6}.cms-account{display:flex;align-items:center;gap:9px;margin-left:7px;padding-left:10px;border-left:1px solid #e3e9f1}.cms-account-person{display:flex;align-items:center;gap:8px;min-width:0}.cms-account-avatar{display:grid;place-items:center;width:34px;height:34px;flex:0 0 34px;border-radius:10px;background:#edf4ff;color:#397ef6;font-size:10px;font-weight:800}.cms-account-details{min-width:0}.cms-account-name{display:block;max-width:130px;overflow:hidden;color:#263752;font-size:12px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.cms-nav .cms-dashboard{padding:8px 12px;border:1px solid #172b4d;background:#172b4d;color:#fff;font-size:11px;font-weight:800}.cms-nav .cms-dashboard:hover{border-color:#223e6d;background:#223e6d;color:#fff}.cms-nav .cms-logout{min-height:0;padding:3px 0 0;color:#68768b;font-size:10px}.cms-nav .cms-logout:hover{color:#b42318;background:transparent}@media(max-width:800px){.cms-nav .cms-login{margin-left:0;text-align:center}.cms-nav .cms-visitor{text-align:center}.cms-account{justify-content:space-between;gap:14px;margin:4px 0 0;padding:14px;border-top:1px solid #e6ebf2;border-left:0;border-radius:0 0 8px 8px;background:#f8faff}.cms-account-person{min-width:0}.cms-account-avatar{width:42px;height:42px;flex-basis:42px;border-radius:12px;font-size:12px}.cms-account-name{max-width:220px;font-size:13px}.cms-nav .cms-logout{display:inline-flex;min-height:24px;padding:3px 0 0;font-size:11px}.cms-nav .cms-dashboard{flex:0 0 auto;min-height:42px;padding:10px 14px;border-radius:9px;font-size:12px}}
</style>
