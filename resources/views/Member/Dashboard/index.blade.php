<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Network | Net-Works</title>
    <style>
        :root{--ink:#17233c;--muted:#6f7c93;--line:#e4eaf2;--blue:#397ef6;--blue-soft:#edf4ff;--green:#159b72;--bg:#f4f7fb;--card:#fff;--shadow:0 10px 28px rgba(35,55,88,.07)}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:14px}.shell{min-height:100vh}.topbar{height:70px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 max(24px,calc((100vw - 1240px)/2));position:sticky;top:0;z-index:20}.brand{font-size:22px;font-weight:800;letter-spacing:-.6px;color:var(--ink);text-decoration:none}.brand i{font-style:normal;color:#f47a20}.top-actions{display:flex;align-items:center;gap:16px}.identity{text-align:right}.identity strong,.identity span{display:block}.identity span{font-size:12px;color:var(--muted);margin-top:2px}.avatar{width:38px;height:38px;border-radius:12px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;font-weight:800}.logout{color:#5e6b82;text-decoration:none;font-weight:700}.container{max-width:1240px;margin:0 auto;padding:28px 24px 50px}.hero{background:linear-gradient(125deg,#172a52,#315caa);border-radius:20px;padding:30px;color:#fff;display:grid;grid-template-columns:1fr auto;gap:24px;align-items:center;box-shadow:var(--shadow)}.eyebrow{font-size:12px;text-transform:uppercase;letter-spacing:.1em;font-weight:800;opacity:.7}.hero h1{font-size:30px;margin:8px 0 8px;letter-spacing:-.8px}.hero p{margin:0;color:#d7e3fa;max-width:640px;line-height:1.6}.hero-actions,.actions{display:flex;gap:10px;flex-wrap:wrap}.btn{border:0;border-radius:10px;height:42px;padding:0 16px;display:inline-flex;align-items:center;justify-content:center;gap:8px;font:inherit;font-weight:750;cursor:pointer;text-decoration:none}.btn-primary{background:var(--blue);color:#fff}.btn-light{background:#fff;color:#234678}.btn-outline{background:#fff;border:1px solid #d9e2ef;color:#40506b}.quick{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:16px 0 22px}.quick button,.quick a{min-height:86px;background:#fff;border:1px solid var(--line);border-radius:14px;padding:16px;text-decoration:none;color:var(--ink);display:flex;gap:13px;align-items:center;text-align:left;cursor:pointer;font:inherit}.quick .icon{width:42px;height:42px;border-radius:11px;background:var(--blue-soft);color:var(--blue);display:grid;place-items:center;font-size:20px}.quick strong,.quick small{display:block}.quick small{color:var(--muted);margin-top:4px}.grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(300px,.75fr);gap:18px}.card{background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);overflow:hidden}.card-head{padding:18px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:16px}.card-head h2{font-size:17px;margin:0}.card-head p{color:var(--muted);margin:4px 0 0;font-size:12px}.card-body{padding:18px 20px}.search{display:flex;gap:9px}.search input{flex:1}.field,input,select,textarea{width:100%;border:1px solid #d9e2ef;border-radius:9px;background:#fff;padding:11px 12px;font:inherit;color:var(--ink);outline:none}input:focus,select:focus,textarea:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(57,126,246,.11)}textarea{min-height:92px;resize:vertical}.business-list,.feed{display:grid;gap:10px}.business{border:1px solid var(--line);border-radius:12px;padding:14px;display:flex;align-items:center;gap:13px}.logo{width:46px;height:46px;flex:0 0 46px;border-radius:12px;background:#eef3fa;display:grid;place-items:center;color:#486078;font-weight:800}.business-info{min-width:0;flex:1}.business h3{font-size:14px;margin:0 0 4px}.business p{margin:0;color:var(--muted);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.business .link{color:var(--blue);font-size:12px;font-weight:750;text-decoration:none}.feed-item{padding:13px 0;border-bottom:1px solid var(--line)}.feed-item:first-child{padding-top:0}.feed-item:last-child{border-bottom:0;padding-bottom:0}.feed-top{display:flex;align-items:center;justify-content:space-between;gap:10px}.feed-item h3{font-size:14px;margin:0}.feed-item p{color:var(--muted);font-size:12px;line-height:1.5;margin:6px 0 0}.pill{display:inline-flex;border-radius:99px;padding:5px 9px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;background:#eef3fa;color:#607088}.pill.public{background:#e9f8f2;color:#168160}.profile{display:grid;gap:12px}.profile-row{display:flex;justify-content:space-between;gap:12px;padding-bottom:11px;border-bottom:1px solid var(--line)}.profile-row span{color:var(--muted);font-size:12px}.profile-row strong{text-align:right;font-size:12px}.empty{text-align:center;padding:24px;color:var(--muted);font-size:13px}.results{margin-top:12px;display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.notice{padding:12px 14px;border-radius:10px;margin-bottom:16px;background:#eaf8f1;color:#17734a}.notice.error{background:#fff0f0;color:#b42318}.notice ul{margin:0;padding-left:18px}dialog{border:0;border-radius:17px;padding:0;width:min(560px,calc(100% - 32px));box-shadow:0 28px 80px rgba(23,35,60,.3)}dialog::backdrop{background:rgba(16,27,48,.58)}.modal-head{padding:19px 22px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}.modal-head h2{font-size:18px;margin:0}.close{border:0;background:#eef2f7;width:34px;height:34px;border-radius:9px;cursor:pointer;font-size:20px}.modal-body{padding:20px 22px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.form-group{display:grid;gap:6px}.form-group.full{grid-column:1/-1}.form-group label{font-size:12px;font-weight:750}.modal-foot{padding:15px 22px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:9px}.section-gap{margin-top:18px}@media(max-width:900px){.quick{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:1fr}.hero{grid-template-columns:1fr}.results{grid-template-columns:1fr}}@media(max-width:560px){.container{padding:18px 14px 40px}.topbar{padding:0 14px}.identity{display:none}.hero{padding:23px}.hero h1{font-size:25px}.quick{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.form-group.full{grid-column:auto}}
        [hidden]{display:none!important}.business-profile{min-width:0;flex:1;display:flex;align-items:center;gap:13px;color:inherit;text-decoration:none;border-radius:9px}.business-profile:hover h3{color:var(--blue)}.request-contact{display:flex;flex-wrap:wrap;gap:6px 14px;margin-top:9px;padding-top:9px;border-top:1px dashed var(--line);font-size:11px}.request-contact strong{color:var(--ink)}.request-contact a,.request-contact span{color:#52627b;text-decoration:none}.request-contact a:hover{color:var(--blue)}
    </style>
</head>
<body>
@php
    $detail = $member->userDetail;
    $displayName = trim(($detail?->ud_first_name ?? '').' '.($detail?->ud_last_name ?? '')) ?: ($member->um_user_name ?: 'Member');
    $initials = collect(explode(' ', $displayName))->filter()->take(2)->map(fn($word) => strtoupper(substr($word,0,1)))->implode('');
    $isBusinessOwner = $member->companies->isNotEmpty();
@endphp
<div class="shell">
    <header class="topbar">
        <a class="brand" href="{{ route('dashboard.index') }}">Net-<i>Works</i></a>
        <div class="top-actions">
            <div class="identity"><strong>{{ $displayName }}</strong><span>{{ $isBusinessOwner ? 'Business owner' : 'Guest member' }}</span></div>
            <div class="avatar">{{ $initials ?: 'NW' }}</div>
            <a class="logout" href="{{ route('member.logout') }}">Log out</a>
        </div>
    </header>
    <main class="container">
        @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
        @if(isset($errors) && $errors->any())<div class="notice error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="hero">
            <div><div class="eyebrow">Your networking workspace</div><h1>Good to see you, {{ explode(' ', $displayName)[0] }}.</h1><p>Discover trusted businesses, ask the community for help, and turn useful introductions into new opportunities.</p></div>
            <div class="hero-actions"><button class="btn btn-light" data-open="enquiryModal">Post an enquiry</button><button class="btn btn-primary" data-open="referralModal">Share a reference</button></div>
        </section>

        <nav class="quick" aria-label="Quick actions">
            <a href="#directory"><span class="icon">⌕</span><span><strong>Find connections</strong><small>People and businesses</small></span></a>
            <button data-open="enquiryModal"><span class="icon">?</span><span><strong>Ask the network</strong><small>Post a common enquiry</small></span></button>
            <button data-open="referralModal"><span class="icon">↗</span><span><strong>Give a reference</strong><small>Connect someone trusted</small></span></button>
            <a href="#my-business"><span class="icon">⌂</span><span><strong>{{ $isBusinessOwner ? 'My business' : 'Join as business' }}</strong><small>{{ $isBusinessOwner ? 'Review your listing' : 'Create a business profile' }}</small></span></a>
        </nav>

        <div class="grid">
            <div>
                <section class="card" id="directory">
                    <div class="card-head"><div><h2>Find people & businesses</h2><p>Search the trusted Net-Works directory by name, service, email or keyword.</p></div></div>
                    <div class="card-body">
                        <form class="search" method="GET" action="{{ route('dashboard.index') }}"><input name="search" value="{{ $search }}" placeholder="Try ‘accountant’, ‘interior designer’ or a member name"><button class="btn btn-primary" type="submit">Search</button></form>
                        @if($search !== '')
                            <div class="results">
                                @forelse($directory as $business)
                                    @php($company = $business->details)
                                    <article class="business"><a class="business-profile" href="{{ route('business.show', $company->public_slug) }}" target="_blank" rel="noopener" aria-label="Open {{ $company?->cmpd_name ?? 'business' }} profile"><div class="logo">{{ strtoupper(substr($company?->cmpd_name ?? 'B',0,2)) }}</div><div class="business-info"><h3>{{ $company?->cmpd_name ?? 'Business' }}</h3><p>{{ $company?->cmpd_description ?: ($company?->cmpd_email ?? 'Net-Works business') }}</p></div></a><button class="link btn btn-outline choose-business" type="button" data-id="{{ $business->cmp_id }}" data-name="{{ $company?->cmpd_name }}">Enquire</button></article>
                                @empty<div class="empty">No matching connections found. Try a broader search.</div>@endforelse
                            </div>
                        @endif
                    </div>
                </section>

                <section class="card section-gap">
                    <div class="card-head"><div><h2>Community requests</h2><p>Recent opportunities and requests shared with everyone.</p></div><button class="btn btn-outline" data-open="enquiryModal">Ask network</button></div>
                    <div class="card-body feed">
                        @forelse($communityEnquiries as $enquiry)
                            <article class="feed-item"><div class="feed-top"><h3>{{ $enquiry->enm_subject }}</h3><span class="pill public">Open request</span></div><p>{{ \Illuminate\Support\Str::limit($enquiry->enm_description,150) }} · {{ optional($enquiry->enm_created_at)->diffForHumans() }}</p><div class="request-contact"><strong>{{ $enquiry->enm_name ?: 'Name not provided' }}</strong>@if($enquiry->enm_phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $enquiry->enm_phone) }}">☎ {{ $enquiry->enm_phone }}</a>@else<span>Phone not provided</span>@endif @if($enquiry->enm_email)<a href="mailto:{{ $enquiry->enm_email }}">✉ {{ $enquiry->enm_email }}</a>@else<span>Email not provided</span>@endif</div></article>
                        @empty<div class="empty">No community enquiries yet. Start the conversation.</div>@endforelse
                    </div>
                </section>
            </div>

            <aside>
                <section class="card" id="my-business">
                    <div class="card-head"><div><h2>{{ $isBusinessOwner ? 'My business' : 'My profile' }}</h2><p>Your identity across the network.</p></div></div>
                    <div class="card-body">
                        @if($isBusinessOwner)
                    <div class="business-list">@foreach($member->companies as $business) @php($company=$business->details)<article class="business"><div class="logo">{{ strtoupper(substr($company?->cmpd_name ?? 'B',0,2)) }}</div><div class="business-info"><h3>{{ $company?->cmpd_name ?? 'Business profile' }}</h3><p>{{ $company?->cmpd_email ?? $member->um_email_id }}</p></div><a class="link" href="{{route('member.portfolio.edit',$business->portfolioRouteToken())}}">Manage page</a>@if($company?->cmpd_status==1 && $company?->public_slug)<a class="link" href="{{route('business.show',$company->public_slug)}}" target="_blank">Open & share</a>@else<span class="pill">Pending approval</span>@endif</article>@endforeach</div>
                        @else
                            <div class="profile"><div class="profile-row"><span>Name</span><strong>{{ $displayName }}</strong></div><div class="profile-row"><span>Email</span><strong>{{ $member->um_email_id }}</strong></div><div class="profile-row"><span>Mobile</span><strong>{{ $member->um_mobile_no ?: 'Not added' }}</strong></div></div>
                        @endif
                    </div>
                </section>

                <section class="card section-gap">
                    <div class="card-head"><div><h2>My recent enquiries</h2><p>Requests and references you shared.</p></div></div>
                    <div class="card-body feed">
                        @forelse($myEnquiries as $enquiry)<article class="feed-item"><div class="feed-top"><h3>{{ $enquiry->enm_subject }}</h3><span class="pill">{{ $enquiry->enm_type == 2 ? 'Public' : 'Direct' }}</span></div><p>{{ \Illuminate\Support\Str::limit($enquiry->enm_description,85) }}</p><div class="request-contact"><strong>To:</strong><span>{{ $enquiry->recipient_label }}</span></div></article>@empty<div class="empty">You have not posted an enquiry yet.</div>@endforelse
                    </div>
                </section>

                <section class="card section-gap">
                    <div class="card-head"><div><h2>Businesses to explore</h2><p>Recently added to the directory.</p></div></div>
                    <div class="card-body business-list">@forelse($recentBusinesses as $business) @php($company=$business->details)<article class="business"><div class="logo">{{ strtoupper(substr($company?->cmpd_name ?? 'B',0,2)) }}</div><div class="business-info"><h3>{{ $company?->cmpd_name ?? 'Business' }}</h3><p>{{ $company?->cmpd_description ?? 'Net-Works member' }}</p></div>@if($company?->public_slug)<a class="link" href="{{route('business.show',$company->public_slug)}}" target="_blank">View profile</a>@endif</article>@empty<div class="empty">No businesses available.</div>@endforelse</div>
                </section>
            </aside>
        </div>
    </main>
</div>

<dialog id="enquiryModal"><form method="POST" action="{{ route('member.enquiries.store') }}">@csrf<div class="modal-head"><div><h2>Post an enquiry</h2></div><button class="close" type="button" data-close>×</button></div><div class="modal-body form-grid"><div class="form-group full"><label>Who should see this?</label><select name="visibility" id="visibility"><option value="public" @selected(old('visibility', 'public') === 'public')>All members — common enquiry</option><option value="private" @selected(old('visibility') === 'private')>One selected business — direct enquiry</option></select></div><div class="form-group full" id="companyField" hidden><label>Business</label><select name="company_id" id="enquiryCompany"><option value="">Choose a business</option>@foreach($businessOptions as $business)<option value="{{ $business->cmp_id }}" @selected((string) old('company_id') === (string) $business->cmp_id)>{{ $business->details?->cmpd_name ?? 'Business #'.$business->cmp_id }}</option>@endforeach</select></div><div class="form-group full"><label>What do you need?</label><input name="subject" value="{{ old('subject') }}" placeholder="Example: Looking for a GST consultant" required></div><div class="form-group full"><label>Details</label><textarea name="description" placeholder="Add useful context, location, timeline or budget." required>{{ old('description') }}</textarea></div></div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit">Share enquiry</button></div></form></dialog>

<dialog id="referralModal"><form method="POST" action="{{ route('member.referrals.store') }}">@csrf<div class="modal-head"><div><h2>Share a reference</h2></div><button class="close" type="button" data-close>×</button></div><div class="modal-body form-grid"><div class="form-group full"><label>Choose a member</label><select name="company_id" required><option value="">Choose a member</option>@foreach($memberOptions as $recipient)<option value="{{ $recipient->company_id }}" @selected((string) old('company_id') === (string) $recipient->company_id)>{{ $recipient->label }}</option>@endforeach</select><small>Member name - Business name (Category name)</small></div><div class="form-group"><label>Contact name</label><input name="name" required placeholder="Full name"></div><div class="form-group"><label>Phone</label><input name="phone" required placeholder="Mobile number"></div><div class="form-group full"><label>Email <span style="color:var(--muted);font-weight:400">(optional)</span></label><input name="email" type="email" placeholder="name@example.com"></div><div class="form-group full"><label>Why is this a useful connection?</label><textarea name="note" required placeholder="Give the member enough context to follow up professionally."></textarea></div></div><div class="modal-foot"><button class="btn btn-outline" type="button" data-close>Cancel</button><button class="btn btn-primary" type="submit">Share reference</button></div></form></dialog>

<script>
document.querySelectorAll('[data-open]').forEach(button=>button.addEventListener('click',()=>document.getElementById(button.dataset.open).showModal()));
document.querySelectorAll('[data-close]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog').close()));
const visibility=document.getElementById('visibility'),companyField=document.getElementById('companyField'),enquiryCompany=document.getElementById('enquiryCompany');
function syncVisibility(){const direct=visibility.value==='private';companyField.hidden=!direct;enquiryCompany.required=direct;if(!direct)enquiryCompany.value=''} visibility.addEventListener('change',syncVisibility);syncVisibility();
document.querySelectorAll('.choose-business').forEach(button=>button.addEventListener('click',()=>{visibility.value='private';syncVisibility();document.getElementById('enquiryCompany').value=button.dataset.id;document.getElementById('enquiryModal').showModal()}));
document.querySelectorAll('dialog').forEach(dialog=>dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close()}));
</script>
</body>
</html>
