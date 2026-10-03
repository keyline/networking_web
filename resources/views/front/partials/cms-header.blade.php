@php
    $headerMember = Auth::guard('member')->user();
    $headerMemberName = $headerMember?->um_name;
    $headerInitials = $headerMemberName
        ? collect(explode(' ', $headerMemberName))->filter()->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('')
        : '';
@endphp
<header class="cms-header">
 <a class="cms-brand" href="{{url('/')}}"><img src="{{env('UPLOADS_URL').$generalSetting->site_logo}}" alt="{{$generalSetting->site_name??'Net-Works'}}"></a>
 <nav class="cms-nav" aria-label="Primary navigation">
  <a href="{{url('/')}}">Home</a>
  <a href="{{route('members.index')}}">Members</a>
  @foreach($headerNavigation as $item)<div class="cms-nav-item"><a href="{{url('page/'.$item->page_slug)}}">{{$item->nav_label?:$item->page_name}}@if($item->children->isNotEmpty()) <span class="caret">⌄</span>@endif</a>@if($item->children->isNotEmpty())<div class="cms-dropdown">@foreach($item->children as $child)<a href="{{url('page/'.$child->page_slug)}}">{{$child->nav_label?:$child->page_name}}</a>@endforeach</div>@endif</div>@endforeach
  <a href="{{url('events')}}">Events</a>
  @if($headerMember)
   <div class="cms-account">
    <span class="cms-account-avatar" aria-hidden="true">{{$headerInitials?:'NW'}}</span>
    <span class="cms-account-name">{{$headerMemberName}}</span>
    <a class="cms-dashboard" href="{{route('dashboard.index')}}">Dashboard</a>
    <a class="cms-logout" href="{{route('member.logout')}}">Log out</a>
   </div>
  @else
   <a href="{{route('join.create')}}">Join as member</a>
   <a class="cms-login" href="{{url('member')}}">Login</a>
  @endif
 </nav>
 <button class="cms-menu-toggle" type="button" aria-label="Toggle menu" onclick="document.querySelector('.cms-nav').classList.toggle('open')">☰</button>
</header>
<style>
.cms-nav .cms-login{margin-left:7px;padding:10px 16px;color:#fff;background:#397ef6}.cms-nav .cms-login:hover{color:#fff;background:#2868dc}.cms-account{display:flex;align-items:center;gap:7px;margin-left:7px;padding-left:10px;border-left:1px solid #e3e9f1}.cms-account-avatar{display:grid;place-items:center;width:31px;height:31px;flex:0 0 31px;border-radius:9px;background:#edf4ff;color:#397ef6;font-size:10px;font-weight:800}.cms-account-name{max-width:130px;overflow:hidden;color:#263752;font-size:12px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.cms-nav .cms-dashboard{padding:7px 10px;border:1px solid #c8d8f3;background:#f4f8ff;color:#2868d6;font-size:11px;font-weight:800}.cms-nav .cms-dashboard:hover{border-color:#397ef6;background:#edf4ff;color:#205ec6}.cms-nav .cms-logout{padding:7px 5px;color:#68768b;font-size:11px}.cms-nav .cms-logout:hover{color:#b42318;background:#fff1f1}@media(max-width:800px){.cms-nav .cms-login{margin-left:0;text-align:center}.cms-account{display:grid;grid-template-columns:34px minmax(0,1fr) auto;margin:4px 0 0;padding:10px 4px 2px;border-top:1px solid #e6ebf2;border-left:0}.cms-account-avatar{grid-row:1/3}.cms-account-name{max-width:none}.cms-nav .cms-dashboard{grid-column:3;grid-row:1/3;align-self:center}.cms-nav .cms-logout{grid-column:2;padding:2px 0}}
</style>
