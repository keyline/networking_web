@php($siteBrand = $siteBrand ?? \App\Models\GeneralSetting::find(1))
@if($siteBrand?->site_logo)
    <img src="{{ env('UPLOADS_URL').$siteBrand->site_logo }}" alt="{{ $siteBrand->site_name ?: 'Net-Works' }}">
@else
    <span>{{ $siteBrand?->site_name ?: 'Net-Works' }}</span>
@endif
