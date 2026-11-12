@foreach($items as $item)
    @php $at = \Carbon\Carbon::parse($item->created_at); @endphp
    @if($type === 'enquiries')
        <article class="list-card">
            <header class="list-head">
                <div class="list-title"><strong>{{ $item->enm_subject ?: 'Enquiry' }}</strong><span>To {{ $item->to_label }}</span></div>
                <div class="ref-side"><span class="ref-pill {{ (int) $item->etu_cmp_id === 0 ? 'public' : '' }}">{{ (int) $item->etu_cmp_id === 0 ? 'All members' : 'Direct' }}</span><time datetime="{{ $at->toIso8601String() }}">{{ $at->format('d M Y') }}</time></div>
            </header>
            @if($item->enm_description && $item->enm_description !== $item->enm_subject)<p class="ref-note">{{ \Illuminate\Support\Str::limit($item->enm_description, 220) }}</p>@endif
            <div class="list-foot"><span class="status-dot {{ (int) $item->enm_status === 1 ? 'is-open' : '' }}"></span>{{ (int) $item->enm_status === 1 ? 'Open' : 'Closed' }}</div>
        </article>
    @else
        <article class="list-card">
            <header class="list-head">
                <div class="list-title"><strong>{{ $item->business_name ?: 'Business' }}</strong><span class="stars" aria-label="{{ (int) round($item->rev_rating) }} out of 5">@for($i = 1; $i <= 5; $i++)<i class="{{ $i <= round($item->rev_rating) ? 'on' : '' }}">★</i>@endfor</span></div>
                <div class="ref-side"><span class="ref-pill rv">{{ number_format((float) $item->rev_rating, 1) }}</span><time datetime="{{ $at->toIso8601String() }}">{{ $at->format('d M Y') }}</time></div>
            </header>
            @if($item->rev_comment)<p class="ref-note">{{ \Illuminate\Support\Str::limit($item->rev_comment, 220) }}</p>@endif
        </article>
    @endif
@endforeach
