@php
    $eventMeta = [
        'view' => ['Viewed your page', 'ev-view', '<path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>'],
        'call' => ['Tapped call', 'ev-call', '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.6a1 1 0 0 1-.25 1Z"/>'],
        'whatsapp' => ['Opened WhatsApp', 'ev-whatsapp', '<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm4.5 12.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.6-1.3a.5.5 0 0 0 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7a2.8 2.8 0 0 0 1.8-1.3 2.3 2.3 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3Z"/>'],
        'email' => ['Tapped email', 'ev-email', '<path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5Z"/>'],
        'share' => ['Shared your page', 'ev-share', '<path d="M18 16a3 3 0 0 0-2.4 1.2l-6.7-3.4a3 3 0 0 0 0-1.6l6.7-3.4A3 3 0 1 0 15 7a3 3 0 0 0 .1.8L8.4 11.2a3 3 0 1 0 0 3.6l6.7 3.4a3 3 0 1 0 2.9-2.2Z"/>'],
    ];
    $currentDay = $lastDay ?? '';
@endphp
@foreach($interactions as $interaction)
    @php
        $at = \Carbon\Carbon::parse($interaction->bae_created_at);
        $day = $at->toDateString();
        $dayLabel = $at->isToday() ? 'Today' : ($at->isYesterday() ? 'Yesterday' : $at->format('D, d M'));
        $visitorName = trim(($interaction->ud_first_name ?? '').' '.($interaction->ud_last_name ?? '')) ?: ($interaction->um_email_id ? 'Registered user' : 'Unregistered visitor');
        $isMember = (bool) ($interaction->um_email_id || $interaction->um_mobile_no);
        $initials = $isMember ? collect(explode(' ', $visitorName))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') : '';
        [$actionLabel, $actionClass, $actionIcon] = $eventMeta[$interaction->bae_event_type] ?? [ucfirst($interaction->bae_event_type), 'ev-view', $eventMeta['view'][2]];
    @endphp
    @if($day !== $currentDay)
        <h3 class="day-head" data-day="{{ $day }}">{{ $dayLabel }}</h3>
        @php $currentDay = $day; @endphp
    @endif
    <article class="item {{ $actionClass }}" data-day="{{ $day }}">
        <div class="avatar {{ $isMember ? 'is-member' : '' }}" aria-hidden="true">@if($isMember){{ $initials ?: 'M' }}@else<svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5Z"/></svg>@endif<span class="ev-dot"><svg viewBox="0 0 24 24">{!! $actionIcon !!}</svg></span></div>
        <div class="item-body">
            <div class="row"><h2>{{ $visitorName }}</h2><time datetime="{{ $at->toIso8601String() }}">{{ $at->format('h:i A') }}</time></div>
            <div class="meta"><span class="action">{{ $actionLabel }}</span><span class="source">{{ $interaction->bae_source ?: 'WEB' }}</span></div>
            @if($isMember)
                <div class="contacts">@if($interaction->um_mobile_no)<a class="call" href="tel:{{ $interaction->um_mobile_no }}" title="Call {{ $interaction->um_mobile_no }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.6a1 1 0 0 1-.25 1Z"/></svg>Call</a>@endif @if($interaction->um_email_id)<a class="mail" href="mailto:{{ $interaction->um_email_id }}" title="{{ $interaction->um_email_id }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5Z"/></svg>Email</a>@endif @if($interaction->whatsapp_url ?? null)<a class="wa" href="{{ $interaction->whatsapp_url }}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm4.5 12.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.6-1.3a.5.5 0 0 0 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7a2.8 2.8 0 0 0 1.8-1.3 2.3 2.3 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3Z"/></svg>WhatsApp</a>@endif</div>
            @endif
        </div>
    </article>
@endforeach
