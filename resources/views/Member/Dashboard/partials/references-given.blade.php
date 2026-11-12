@foreach($references as $reference)
    @php
        $at = \Carbon\Carbon::parse($reference->enm_created_at);
        $isPublic = (int) $reference->etu_cmp_id === 0;
        $isReferral = $reference->enm_subject === 'Member referral';
        $toName = $isPublic ? 'All members' : ($reference->recipient_business ?? $reference->recipient_label);
        $toInitials = $isPublic ? '' : collect(explode(' ', (string) $toName))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
        $toMeta = $isPublic ? 'Shared with the whole network' : collect([$reference->recipient_owner ?? null, $reference->recipient_category ?? null])->filter()->implode(' · ');
        $phone = preg_replace('/\D+/', '', (string) $reference->enm_phone);
        $waNumber = strlen($phone) >= 10 ? (strlen($phone) === 10 ? '91'.$phone : (strlen($phone) === 11 && str_starts_with($phone, '0') ? '91'.substr($phone, 1) : $phone)) : null;
    @endphp
    <article class="ref-card">
        <header class="ref-head">
            <span class="ref-avatar {{ $isPublic ? 'is-public' : '' }}" aria-hidden="true">@if($isPublic)<svg viewBox="0 0 24 24"><path d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-8 1a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm8 1c-2.7 0-8 1.3-8 4v2h16v-2c0-2.7-5.3-4-8-4Zm-8 0c-.3 0-.7 0-1.1.1A4.7 4.7 0 0 1 9 17v2H1v-2c0-2.2 4.4-4 7-4Z"/></svg>@else{{ $toInitials ?: 'B' }}@endif</span>
            <div class="ref-to">
                <small>Given to</small>
                <strong>{{ $toName }}</strong>
                @if($toMeta)<span>{{ $toMeta }}</span>@endif
            </div>
            <div class="ref-side">
                <span class="ref-pill {{ $isPublic ? 'public' : '' }}">{{ $isPublic ? 'All members' : 'Direct' }}</span>
                <time datetime="{{ $at->toIso8601String() }}">{{ $at->format('d M Y') }}</time>
            </div>
        </header>

        @if($reference->enm_name || $reference->enm_phone || $reference->enm_email)
            <div class="ref-contact">
                <small>{{ $isReferral ? 'Referred contact' : 'Contact' }}</small>
                <div class="ref-person">{{ $reference->enm_name ?: 'Name not provided' }}</div>
                <div class="ref-lines">
                    @if($reference->enm_phone)<span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.6a1 1 0 0 1-.25 1Z"/></svg>{{ $reference->enm_phone }}</span>@endif
                    @if($reference->enm_email)<span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5Z"/></svg>{{ $reference->enm_email }}</span>@endif
                </div>
                @if($phone || $reference->enm_email)
                    <div class="ref-actions">
                        @if($phone)<a class="call" href="tel:{{ $reference->enm_phone }}">Call</a>@endif
                        @if($waNumber)<a class="wa" href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener">WhatsApp</a>@endif
                        @if($reference->enm_email)<a class="mail" href="mailto:{{ $reference->enm_email }}">Email</a>@endif
                    </div>
                @endif
            </div>
        @endif

        @if(!$isReferral && $reference->enm_subject)<div class="ref-subject">{{ $reference->enm_subject }}</div>@endif
        @if($reference->enm_description)<div class="ref-notebox"><small>Note</small><p>{{ \Illuminate\Support\Str::limit($reference->enm_description, 300) }}</p></div>@endif
    </article>
@endforeach
