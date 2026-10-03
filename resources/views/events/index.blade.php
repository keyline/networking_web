<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Events | Net-Works</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f5f7fb;font:15px Arial;color:#18243a}
        header{display:flex;align-items:center;min-height:74px;padding:10px max(24px,calc((100% - 1120px)/2));border-bottom:1px solid #e2e8f0;background:#fff}
        .portal-brand{display:inline-flex;align-items:center;color:#18243a;text-decoration:none;font-size:22px;font-weight:800}
        .portal-brand img{display:block;max-width:180px;max-height:52px;object-fit:contain}
        .wrap{max-width:1120px;margin:42px auto;padding:0 20px}.intro{margin-bottom:25px}.intro h1{margin:0 0 8px;font-size:36px}
        .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
        .card{overflow:hidden;border:1px solid #e1e7ef;border-radius:14px;background:#fff;box-shadow:0 8px 25px #1b2b4510}
        .cover{position:relative;display:flex;align-items:flex-end;height:180px;padding:18px;overflow:hidden;background:linear-gradient(135deg,#377dff,#7756d8);color:#fff}
        .cover img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}
        .cover.has-image:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(9,24,51,.05),rgba(9,24,51,.75))}
        .cover b{position:relative;z-index:1;font-size:14px;text-shadow:0 1px 4px rgba(0,0,0,.35)}
        .body{padding:18px}.date{color:#377dff;text-transform:uppercase;font-size:12px;font-weight:800}.card h2{margin:7px 0;font-size:20px}.muted{color:#718096}
        .button{display:inline-block;margin-top:12px;padding:10px 15px;border-radius:8px;background:#377dff;color:#fff;text-decoration:none;font-weight:700}
        .pagination{display:flex;justify-content:flex-end;gap:0;margin:25px 0 0;padding:0;list-style:none}.page-link{display:block;min-width:38px;margin-left:-1px;padding:9px 12px;border:1px solid #dce3ed;background:#fff;color:#377dff;text-align:center;text-decoration:none}.page-item:first-child .page-link{border-radius:8px 0 0 8px}.page-item:last-child .page-link{border-radius:0 8px 8px 0}.page-item.active .page-link{position:relative;border-color:#377dff;background:#377dff;color:#fff}.page-item.disabled .page-link{background:#f3f5f8;color:#9aa5b5}
        @media(max-width:800px){.grid{grid-template-columns:1fr}}
        @media(max-width:520px){header{min-height:62px;padding:8px 16px}.portal-brand img{max-width:140px;max-height:44px}.wrap{margin-top:26px;padding:0 14px}.intro h1{font-size:30px}.cover{height:165px}}
    </style>
</head>
<body>
<header><a class="portal-brand" href="{{url('/')}}">@include('Member.partials.site-logo')</a></header>
<main class="wrap">
    <div class="intro"><h1>Upcoming events</h1><div class="muted">Discover, reserve your seats and complete payment securely online.</div></div>
    <div class="grid">
        @forelse($events as $event)
            <article class="card">
                <div class="cover {{$event->cover_image ? 'has-image' : ''}}">
                    @if($event->cover_image)<img loading="lazy" src="{{url('public/'.$event->cover_image)}}" alt="{{$event->title}} event banner">@endif
                    <b>{{$event->starts_at->format('d M Y')}}</b>
                </div>
                <div class="body">
                    <div class="date">{{$event->starts_at->format('l, h:i A')}}</div>
                    <h2>{{$event->title}}</h2>
                    <div class="muted">{{$event->venue_name ?: 'Venue to be announced'}}</div>
                    <p>{{Str::limit($event->summary,100)}}</p>
                    <a class="button" href="{{route('events.show',$event)}}">View & register</a>
                </div>
            </article>
        @empty
            <div class="card body">No upcoming events are currently published.</div>
        @endforelse
    </div>
    <div style="margin-top:25px">{{$events->links()}}</div>
</main>
</body>
</html>
