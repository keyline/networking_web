<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#195d59">
<title>{{ config('app.page_title') }}</title>
<style>
:root{--ink:#17233c;--muted:#6d7b91;--line:#e3eaf3;--navy:#172b4d;--teal:#1f8a7d}
*{box-sizing:border-box}
body{margin:0;background:#f1f5fa;color:var(--ink);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;-webkit-tap-highlight-color:transparent}
.top{position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;min-height:62px;padding:8px max(16px,calc((100% - 760px)/2));padding-top:max(8px,env(safe-area-inset-top));border-bottom:1px solid var(--line);background:rgba(255,255,255,.92);backdrop-filter:saturate(1.6) blur(10px)}
.brand img{display:block;max-width:140px;max-height:42px}
.back{display:inline-flex;align-items:center;min-height:40px;padding:0 14px;border-radius:999px;background:var(--navy);color:#fff;text-decoration:none;font-size:13px;font-weight:800}
.page{width:min(760px,calc(100% - 24px));margin:16px auto 40px}

/* summary card */
.hero{position:relative;overflow:hidden;padding:20px 20px 16px;border-radius:24px;background:linear-gradient(135deg,#134e4a,#1f8a7d 60%,#2bb3a0);color:#fff;box-shadow:0 16px 34px rgba(19,78,74,.25)}
.hero:after{content:"";position:absolute;right:-60px;top:-70px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.08)}
.hero-top{position:relative;z-index:1;display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.hero h1{margin:0;font-size:22px}
.hero p{margin:3px 0 0;color:#d4f3ee;font-size:13px}
.total{text-align:right}
.total strong{display:block;font-size:30px;line-height:1}
.total span{color:#d4f3ee;font-size:12px}
.chart{position:relative;z-index:1;display:flex;align-items:flex-end;gap:3px;height:96px;margin:16px 0 6px;padding:0 2px}
.bar{flex:1;min-width:0;height:100%;display:flex;align-items:flex-end}
.bar i{display:block;width:100%;min-height:3px;border-radius:4px 4px 2px 2px;background:rgba(255,255,255,.35);transform-origin:bottom;animation:grow .6s cubic-bezier(.2,.8,.2,1) both}
.bar.has i{background:linear-gradient(180deg,#fff,rgba(255,255,255,.65))}
.bar.today i{background:#ffd166}
@keyframes grow{from{transform:scaleY(0)}to{transform:scaleY(1)}}
.chart-axis{position:relative;z-index:1;display:flex;justify-content:space-between;color:#c4ebe5;font-size:12px}
.types{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;margin-top:12px}
.type{padding:10px 8px;border-radius:16px;background:#fff;text-align:center;box-shadow:0 6px 16px rgba(32,51,80,.07)}
.type b{display:grid;place-items:center;width:32px;height:32px;margin:0 auto 6px;border-radius:10px;background:var(--c-soft)}
.type b svg{width:17px;height:17px;fill:var(--c)}
.type strong{display:block;font-size:18px;line-height:1.1;color:var(--ink)}
.type span{display:block;margin-top:2px;color:var(--muted);font-size:12px}
.members-line{margin:10px 2px 0;color:var(--muted);font-size:13px}
.members-line strong{color:var(--ink)}

/* action colours */
.ev-view{--c:#2a5fd0;--c-soft:#e8f0ff}
.ev-call{--c:#138a50;--c-soft:#e4f7ec}
.ev-whatsapp{--c:#16a05d;--c-soft:#dff7ea}
.ev-email{--c:#d9601c;--c-soft:#fff1e3}
.ev-share{--c:#6d45d6;--c-soft:#f2ecff}

/* feed */
.feed{margin-top:14px}
.day-head{position:sticky;top:62px;z-index:5;margin:14px 0 6px;padding:6px 4px;background:#f1f5fa;color:var(--muted);font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.item{display:flex;gap:12px;margin-bottom:8px;padding:12px 14px;border-radius:18px;background:#fff;box-shadow:0 4px 14px rgba(32,51,80,.05);animation:rise .35s ease both}
@keyframes rise{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.avatar{position:relative;display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:50%;background:#eef2f7;color:#7a879b;font-size:15px;font-weight:800}
.avatar svg{width:22px;height:22px;fill:currentColor}
.avatar.is-member{background:linear-gradient(135deg,var(--c),color-mix(in srgb,var(--c) 60%,#fff));color:#fff}
.ev-dot{position:absolute;right:-3px;bottom:-3px;display:grid;place-items:center;width:20px;height:20px;border:2px solid #fff;border-radius:50%;background:var(--c)}
.ev-dot svg{width:11px;height:11px;fill:#fff}
.item-body{flex:1;min-width:0}
.row{display:flex;align-items:baseline;justify-content:space-between;gap:10px}
.item h2{margin:0;overflow:hidden;font-size:15px;text-overflow:ellipsis;white-space:nowrap}
time{flex:0 0 auto;color:var(--muted);font-size:12px}
.meta{display:flex;flex-wrap:wrap;align-items:center;gap:4px 6px;margin-top:3px;color:var(--muted);font-size:13px}
.action{color:var(--c);font-weight:700}
.business{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%}
.source{padding:1px 7px;border-radius:999px;background:#f0f3f7;font-size:12px;font-weight:700}
.contacts{display:flex;flex-wrap:wrap;gap:6px;margin-top:9px}
.contacts a{display:inline-flex;align-items:center;min-height:36px;padding:0 13px;border-radius:999px;background:var(--c-soft);color:var(--c);text-decoration:none;font-size:13px;font-weight:700}
.empty{padding:40px 20px;border-radius:18px;background:#fff;color:var(--muted);text-align:center}

/* lazy loading */
.skeleton{display:flex;gap:12px;margin-bottom:8px;padding:12px 14px;border-radius:18px;background:#fff}
.skeleton .s-av{flex:0 0 44px;height:44px;border-radius:50%}
.skeleton .s-lines{flex:1}
.skeleton .s-line{height:12px;margin:6px 0;border-radius:6px}
.skeleton .s-av,.skeleton .s-line{background:linear-gradient(90deg,#eef2f7 25%,#f7f9fc 50%,#eef2f7 75%);background-size:200% 100%;animation:shimmer 1.2s infinite}
@keyframes shimmer{from{background-position:200% 0}to{background-position:-200% 0}}
.end{padding:18px;color:var(--muted);text-align:center;font-size:13px}
.retry{display:block;margin:10px auto;padding:10px 18px;border:0;border-radius:999px;background:var(--navy);color:#fff;font:inherit;font-weight:700}
.pager{display:flex;justify-content:center;gap:8px;margin-top:16px}
.pager a,.pager span{padding:9px 12px;border:1px solid var(--line);border-radius:8px;background:#fff;color:#40516b;text-decoration:none;font-size:13px;font-weight:700}
.js .pager{display:none}
@media(max-width:560px){
 .page{width:calc(100% - 20px);margin-top:12px}
 .hero{padding:18px 16px 14px;border-radius:22px}
 .hero h1{font-size:20px}
 .total strong{font-size:26px}
 .chart{height:84px;gap:2px}
 .types{gap:6px}
 .type{padding:9px 4px;border-radius:14px}
 .type b{width:28px;height:28px}
 .type strong{font-size:16px}
}
@media(prefers-reduced-motion:reduce){.bar i,.item{animation:none}}

/* business chip / picker */
.biz-bar{display:flex;align-items:center;gap:12px;margin:0 0 12px;padding:10px 12px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 6px 16px rgba(32,51,80,.06)}
.biz-logo{position:relative;display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;overflow:hidden;border-radius:12px;background:linear-gradient(135deg,#1f8a7d,#2bb3a0);color:#fff;font-size:14px;font-weight:800}
.biz-logo img{position:absolute;inset:0;width:100%;height:100%;padding:4px;object-fit:contain;background:#fff}
.biz-text{flex:1;min-width:0}
.biz-text small{display:block;color:var(--muted);font-size:12px;font-weight:600}
.biz-text strong{display:block;overflow:hidden;font-size:16px;line-height:1.3;text-overflow:ellipsis;white-space:nowrap}
.biz-text select{display:block;width:100%;max-width:100%;margin:1px 0 0;padding:2px 26px 2px 0;border:0;background:transparent url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%231f8a7d' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right center/22px;color:var(--ink);font:inherit;font-size:16px;font-weight:800;text-overflow:ellipsis;cursor:pointer;-webkit-appearance:none;appearance:none}
.biz-text select:focus{outline:none}
.biz-bar:focus-within{border-color:#1f8a7d;box-shadow:0 0 0 3px rgba(31,138,125,.18)}
.biz-count{flex:0 0 auto;padding:4px 9px;border-radius:999px;background:#e2f7f4;color:#0b6f63;font-size:12px;font-weight:800}
.visually-hidden{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}

.contacts{display:grid;grid-template-columns:repeat(auto-fit,minmax(0,1fr));gap:6px;max-width:330px}
.contacts a{justify-content:center;gap:6px;padding:0 10px}
.contacts a svg{flex:0 0 15px;width:15px;height:15px;fill:currentColor}
.contacts a.call{background:#e8f0ff;color:#2a5fd0}
.contacts a.mail{background:#fff1e3;color:#c2541a}
.contacts a.wa{background:#dff7ea;color:#128048}
</style>
<script>document.documentElement.classList.add('js')</script>
</head>
<body>
<header class="top"><a class="brand" href="{{ url('/') }}">@include('Member.partials.site-logo')</a><a class="back" href="{{ route('dashboard.index') }}">← Dashboard</a></header>
<main class="page">
@php
    $dayTotals = $summary['days'];
    $peak = max(1, max($dayTotals ?: [0]));
    $typeMeta = [
        'view' => ['Views', 'ev-view', '<path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/>'],
        'call' => ['Calls', 'ev-call', '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.6a1 1 0 0 1-.25 1Z"/>'],
        'whatsapp' => ['WhatsApp', 'ev-whatsapp', '<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Z"/>'],
        'email' => ['Email', 'ev-email', '<path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5Z"/>'],
        'share' => ['Shares', 'ev-share', '<path d="M18 16a3 3 0 0 0-2.4 1.2l-6.7-3.4a3 3 0 0 0 0-1.6l6.7-3.4A3 3 0 1 0 15 7a3 3 0 0 0 .1.8L8.4 11.2a3 3 0 1 0 0 3.6l6.7 3.4a3 3 0 1 0 2.9-2.2Z"/>'],
    ];
@endphp
@if($selectedBusiness)
    @php $bizInitials = collect(explode(' ', $selectedBusiness->name))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''); @endphp
    <form class="biz-bar" method="GET" action="{{ route('member.business-activity.index') }}">
        <span class="biz-logo">@if($selectedBusiness->logo)<img src="{{ env('UPLOADS_URL').'company/'.$selectedBusiness->logo }}" alt="" onerror="this.remove()">@endif<b>{{ $bizInitials ?: 'B' }}</b></span>
        <span class="biz-text"><small>{{ $ownedBusinesses->count() > 1 ? 'Showing activity for' : 'Your business' }}</small>
        @if($ownedBusinesses->count() > 1)
            <label class="visually-hidden" for="bizPicker">Choose a business</label>
            <select id="bizPicker" name="business" onchange="this.form.submit()">@foreach($ownedBusinesses as $biz)<option value="{{ $biz->id }}" @selected($biz->id === $selectedBusiness->id)>{{ $biz->name }}</option>@endforeach</select>
            <noscript><button type="submit">Show</button></noscript>
        @else
            <strong>{{ $selectedBusiness->name }}</strong>
        @endif
        </span>
        @if($ownedBusinesses->count() > 1)<span class="biz-count">{{ $ownedBusinesses->count() }}</span>@endif
    </form>
@endif
<section class="hero" aria-label="Activity in the last 30 days">
    <div class="hero-top">
        <div><h1>Business activity</h1><p>Last 30 days</p></div>
        <div class="total"><strong>{{ number_format($summary['total']) }}</strong><span>interactions</span></div>
    </div>
    <div class="chart" role="img" aria-label="Daily interactions over the last 30 days">
        @foreach($dayTotals as $date => $count)
            <div class="bar {{ $count ? 'has' : '' }} {{ $loop->last ? 'today' : '' }}" title="{{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $count }}"><i style="height:{{ max(3, round($count / $peak * 100)) }}%;animation-delay:{{ $loop->index * 12 }}ms"></i></div>
        @endforeach
    </div>
    <div class="chart-axis"><span>{{ \Carbon\Carbon::parse(array_key_first($dayTotals))->format('d M') }}</span><span>Today</span></div>
</section>

<div class="types">
    @foreach($typeMeta as $type => [$label, $class, $icon])
        <div class="type {{ $class }}"><b><svg viewBox="0 0 24 24" aria-hidden="true">{!! $icon !!}</svg></b><strong>{{ number_format($summary['byType'][$type] ?? 0) }}</strong><span>{{ $label }}</span></div>
    @endforeach
</div>
@if($summary['members'])
    <p class="members-line"><strong>{{ $summary['members'] }}</strong> registered {{ \Illuminate\Support\Str::plural('member', $summary['members']) }} engaged with your business.</p>
@endif

<section class="feed" id="activityFeed" aria-live="polite">
    @if($interactions->isEmpty())
        <div class="empty">No activity in the last 30 days.</div>
    @else
        @include('Member.BusinessActivity.partials.items', ['interactions' => $interactions, 'lastDay' => $lastDay])
    @endif
</section>
<div id="feedSentinel" data-next="{{ $interactions->hasMorePages() ? $interactions->nextPageUrl() : '' }}"></div>
@if($interactions->hasPages())
    <nav class="pager">@if($interactions->onFirstPage())<span>Previous</span>@else<a href="{{ $interactions->previousPageUrl() }}">Previous</a>@endif<span>Page {{ $interactions->currentPage() }}</span>@if($interactions->hasMorePages())<a href="{{ $interactions->nextPageUrl() }}">Next</a>@endif</nav>
@endif
</main>
<script>
(() => {
    const feed = document.getElementById('activityFeed'), sentinel = document.getElementById('feedSentinel');
    let next = sentinel.dataset.next, loading = false;
    const lastDay = () => { const items = feed.querySelectorAll('.item'); return items.length ? items[items.length - 1].dataset.day : ''; };
    const skeleton = () => { const box = document.createElement('div'); box.className = 'skeletons'; box.innerHTML = '<div class="skeleton"><div class="s-av"></div><div class="s-lines"><div class="s-line" style="width:55%"></div><div class="s-line" style="width:80%"></div></div></div>'.repeat(3); return box; };
    const finish = () => { observer && observer.disconnect(); if (feed.querySelector('.item')) { sentinel.innerHTML = '<div class="end">You’re all caught up · showing the last 30 days</div>'; } };
    const load = async () => {
        if (loading || !next) return;
        loading = true;
        const placeholder = skeleton(); sentinel.innerHTML = ''; sentinel.appendChild(placeholder);
        try {
            const url = new URL(next, location.href); url.searchParams.set('last_day', lastDay());
            const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!res.ok) throw new Error(res.status);
            const data = await res.json();
            placeholder.remove();
            feed.insertAdjacentHTML('beforeend', data.html);
            next = data.next;
            loading = false;
            if (!next) finish();
        } catch (e) {
            loading = false;
            sentinel.innerHTML = '<button type="button" class="retry">Couldn’t load more · Tap to retry</button>';
            sentinel.querySelector('.retry').addEventListener('click', load);
        }
    };
    const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => { if (entries.some(entry => entry.isIntersecting)) load(); }, { rootMargin: '600px 0px' }) : null;
    if (!next) { finish(); return; }
    if (observer) observer.observe(sentinel); else window.addEventListener('scroll', () => { if (sentinel.getBoundingClientRect().top < innerHeight + 600) load(); }, { passive: true });
})();
</script>
</body>
</html>
