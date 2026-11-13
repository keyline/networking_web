@php
    $at = \Carbon\Carbon::parse($item->created_at);
    $stars = (int) round((float) $item->rev_rating);
    $edited = !empty($item->updated_at) && \Carbon\Carbon::parse($item->updated_at)->gt($at->copy()->addMinute());
@endphp
<article class="list-card review-card" data-review-id="{{ $item->rev_id }}">
    <header class="list-head">
        <div class="list-title"><strong>{{ $item->business_name ?: 'Business' }}</strong><span class="stars" aria-label="{{ $stars }} out of 5">@for($i = 1; $i <= 5; $i++)<i class="{{ $i <= $stars ? 'on' : '' }}">★</i>@endfor</span></div>
        <div class="ref-side"><span class="ref-pill rv">{{ number_format((float) $item->rev_rating, 1) }}</span><time datetime="{{ $at->toIso8601String() }}">{{ $at->format('d M Y') }}@if($edited) · edited @endif</time></div>
    </header>
    <div class="rv-view">
        @if($item->rev_comment)<p class="ref-note">{{ \Illuminate\Support\Str::limit($item->rev_comment, 220) }}</p>@endif
        <button type="button" class="rv-edit" aria-label="Edit review for {{ $item->business_name }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.3V21h3.8l11-11.1-3.7-3.7L3 17.3Zm17.7-10.2a1 1 0 0 0 0-1.4l-2.4-2.4a1 1 0 0 0-1.4 0l-1.8 1.8 3.7 3.7 1.9-1.7Z"/></svg>Edit</button>
    </div>
    <form class="rv-form" method="post" action="{{ route('member.reviews.update', $item->rev_id) }}" hidden>
        @csrf @method('PUT')
        <div class="rv-stars" role="radiogroup" aria-label="Rating">
            @for($i = 5; $i >= 1; $i--)<input type="radio" id="rv{{ $item->rev_id }}-{{ $i }}" name="rating" value="{{ $i }}" @checked($i === $stars)><label for="rv{{ $item->rev_id }}-{{ $i }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">★</label>@endfor
        </div>
        <textarea name="comment" rows="3" minlength="3" maxlength="1000" required>{{ $item->rev_comment }}</textarea>
        <p class="rv-error" hidden></p>
        <div class="rv-actions"><button type="button" class="rv-cancel">Cancel</button><button type="submit" class="rv-save">Save review</button></div>
    </form>
</article>
