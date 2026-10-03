@php($siteBrand = $siteBrand ?? \App\Models\GeneralSetting::find(1))
@if($siteBrand?->site_logo)
    <img data-portal-home-logo src="{{ env('UPLOADS_URL').$siteBrand->site_logo }}" alt="{{ $siteBrand->site_name ?: 'Net-Works' }}">
@else
    <span data-portal-home-logo>{{ $siteBrand?->site_name ?: 'Net-Works' }}</span>
@endif
@once
<script>document.querySelectorAll('[data-portal-home-logo]').forEach(logo=>{const link=logo.closest('a');if(link)link.href=@json(url('/'))})</script>
@endonce
