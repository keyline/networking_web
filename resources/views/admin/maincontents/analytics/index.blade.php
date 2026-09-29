<?php
$maxTrend = max(1, (int) $trend->max(fn ($item) => max((int) $item->views, (int) $item->clicks)));
$sourceTotal = max(1, (int) $sources->sum('total'));
?>
<style>
  .analytics-page { --an-primary:#397ef6; --an-text:#263650; --an-muted:#728097; --an-border:#e5eaf2; }
  .analytics-page .analytics-head { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin-bottom:18px; }
  .analytics-page .breadcrumb { margin:0 0 7px; font-size:12px; }
  .analytics-page h1 { margin:0 0 4px; color:#17233c; font-size:24px; font-weight:750; }
  .analytics-page .subtitle { margin:0; color:var(--an-muted); font-size:13px; }
  .analytics-page .range-form { display:flex; align-items:center; gap:8px; padding:5px; border:1px solid var(--an-border); border-radius:9px; background:#fff; }
  .analytics-page .range-form label { margin-left:7px; color:#7e8a9d; font-size:10px; font-weight:750; text-transform:uppercase; }
  .analytics-page .range-form select { height:34px; padding:0 30px 0 9px; color:#40506a; border:0; border-radius:6px; background-color:#f5f7fa; font-size:12px; font-weight:650; }
  .analytics-page .stats { display:grid; grid-template-columns:repeat(5, 1fr); gap:10px; margin-bottom:14px; }
  .analytics-page .stat { min-width:0; padding:15px; border:1px solid var(--an-border); border-radius:10px; background:#fff; box-shadow:0 5px 16px rgba(23,35,60,.04); }
  .analytics-page .stat-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
  .analytics-page .stat-icon { display:grid; place-items:center; width:32px; height:32px; border-radius:8px; background:#edf4ff; color:var(--an-primary); }
  .analytics-page .stat:nth-child(2) .stat-icon { background:#e9f8f1; color:#198754; }
  .analytics-page .stat:nth-child(3) .stat-icon { background:#eaf8f8; color:#0b98a4; }
  .analytics-page .stat:nth-child(4) .stat-icon { background:#fff2e8; color:#dc7415; }
  .analytics-page .stat:nth-child(5) .stat-icon { background:#f0edff; color:#7256d8; }
  .analytics-page .stat-value { margin-top:12px; color:#17233c; font-size:24px; font-weight:780; line-height:1; }
  .analytics-page .stat-label { margin-top:5px; color:#7c899c; font-size:10px; font-weight:750; letter-spacing:.045em; text-transform:uppercase; }
  .analytics-page .overview-grid { display:grid; grid-template-columns:minmax(0, 2fr) minmax(260px, 1fr); gap:12px; margin-bottom:14px; }
  .analytics-page .panel { overflow:hidden; border:1px solid var(--an-border); border-radius:11px; background:#fff; box-shadow:0 6px 18px rgba(23,35,60,.04); }
  .analytics-page .panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid #edf1f6; }
  .analytics-page .panel-head h2 { margin:0; color:var(--an-text); font-size:14px; font-weight:750; }
  .analytics-page .panel-head span { color:#8a96a8; font-size:10px; }
  .analytics-page .trend-chart { display:flex; align-items:flex-end; gap:5px; height:195px; padding:24px 16px 14px; }
  .analytics-page .trend-day { display:flex; flex:1; align-items:center; flex-direction:column; justify-content:flex-end; min-width:5px; height:100%; }
  .analytics-page .trend-bars { display:flex; align-items:flex-end; justify-content:center; gap:2px; width:100%; height:155px; }
  .analytics-page .bar { width:min(9px, 42%); min-height:2px; border-radius:3px 3px 1px 1px; background:#7faaf8; }
  .analytics-page .bar.clicks { background:#27b692; }
  .analytics-page .trend-label { margin-top:6px; color:#9aa5b5; font-size:8px; white-space:nowrap; transform:rotate(-35deg); }
  .analytics-page .chart-legend { display:flex; gap:14px; padding:0 16px 14px; color:#77859a; font-size:10px; }
  .analytics-page .legend-dot { display:inline-block; width:7px; height:7px; margin-right:5px; border-radius:2px; background:#7faaf8; }
  .analytics-page .legend-dot.clicks { background:#27b692; }
  .analytics-page .source-list { padding:14px 16px; }
  .analytics-page .source-row { margin-bottom:15px; }
  .analytics-page .source-meta { display:flex; justify-content:space-between; margin-bottom:6px; color:#53647d; font-size:11px; font-weight:650; }
  .analytics-page .source-track { height:6px; overflow:hidden; border-radius:5px; background:#edf1f6; }
  .analytics-page .source-fill { height:100%; border-radius:5px; background:linear-gradient(90deg, #397ef6, #71a2fb); }
  .analytics-page .channel-strip { display:grid; grid-template-columns:repeat(5, 1fr); gap:8px; margin-bottom:14px; }
  .analytics-page .channel { padding:11px 13px; border:1px solid var(--an-border); border-radius:9px; background:#fff; }
  .analytics-page .channel span { color:#7d899b; font-size:9px; font-weight:750; text-transform:uppercase; }
  .analytics-page .channel strong { display:block; margin-top:4px; color:#34455e; font-size:17px; }
  .analytics-page .ranking-panel { padding-bottom:4px; }
  .analytics-page .ranking-tools { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 16px; border-bottom:1px solid #edf1f6; }
  .analytics-page .search-form { display:flex; gap:7px; width:min(390px, 100%); }
  .analytics-page .search-form input { width:100%; height:36px; padding:7px 11px; border:1px solid #dce3ed; border-radius:7px; font-size:12px; }
  .analytics-page .search-form button { padding:7px 13px; color:#fff; border:0; border-radius:7px; background:var(--an-primary); font-size:11px; font-weight:700; }
  .analytics-page table { margin:0; }
  .analytics-page table thead th { padding:10px 12px; color:#68778d; border-bottom:1px solid var(--an-border); background:#f7f9fc; font-size:10px; font-weight:780; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
  .analytics-page table tbody td { padding:10px 12px; color:#53647d; border-bottom:1px solid #edf1f6; font-size:12px; vertical-align:middle; }
  .analytics-page .business { display:flex; align-items:center; gap:9px; min-width:210px; }
  .analytics-page .business-logo { display:grid; place-items:center; width:34px; height:34px; flex:0 0 34px; overflow:hidden; border:1px solid #e3e8ef; border-radius:8px; background:#f6f8fb; }
  .analytics-page .business-logo img { width:100%; height:100%; object-fit:contain; }
  .analytics-page .business strong { display:block; color:#2c3c56; font-size:12px; }
  .analytics-page .business small { color:#98a3b2; font-size:9px; }
  .analytics-page .score { display:inline-flex; min-width:40px; justify-content:center; padding:5px 8px; color:#2d68cf; border-radius:12px; background:#eaf2ff; font-size:11px; font-weight:800; }
  .analytics-page .empty { padding:35px !important; color:#8995a6; text-align:center; }
  .analytics-page .pagination-wrap { padding:12px 16px; }
  @media(max-width:1199px){.analytics-page .stats{grid-template-columns:repeat(3,1fr)}.analytics-page .overview-grid{grid-template-columns:1fr}}
  @media(max-width:767px){.analytics-page .analytics-head{align-items:stretch;flex-direction:column}.analytics-page .stats{grid-template-columns:repeat(2,1fr)}.analytics-page .channel-strip{grid-template-columns:repeat(2,1fr)}.analytics-page .ranking-tools{align-items:stretch;flex-direction:column}.analytics-page .search-form{width:100%}}
</style>

<div class="analytics-page">
  <div class="analytics-head">
    <div>
      <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Analytics</li></ol></nav>
      <h1>Business Traction Analytics</h1>
      <p class="subtitle">Understand which businesses attract attention and which contact channels convert visitors.</p>
    </div>
    <form method="GET" class="range-form">
      <label for="range">Reporting period</label>
      <select id="range" name="range" onchange="this.form.submit()">
        @foreach([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days', 365 => 'Last 12 months'] as $days => $label)
          <option value="{{ $days }}" @selected($range === $days)>{{ $label }}</option>
        @endforeach
      </select>
    </form>
  </div>

  <div class="stats">
    <div class="stat"><div class="stat-top"><div class="stat-icon"><i class="fa fa-eye"></i></div></div><div class="stat-value">{{ number_format($stats['views']) }}</div><div class="stat-label">Profile views</div></div>
    <div class="stat"><div class="stat-top"><div class="stat-icon"><i class="fa fa-users"></i></div></div><div class="stat-value">{{ number_format($stats['unique_visitors']) }}</div><div class="stat-label">Unique visitors</div></div>
    <div class="stat"><div class="stat-top"><div class="stat-icon"><i class="fa fa-mouse-pointer"></i></div></div><div class="stat-value">{{ number_format($stats['clicks']) }}</div><div class="stat-label">Contact clicks</div></div>
    <div class="stat"><div class="stat-top"><div class="stat-icon"><i class="fa fa-paper-plane"></i></div></div><div class="stat-value">{{ number_format($stats['enquiries']) }}</div><div class="stat-label">Enquiries</div></div>
    <div class="stat"><div class="stat-top"><div class="stat-icon"><i class="fa fa-percentage"></i></div></div><div class="stat-value">{{ $stats['conversion_rate'] }}%</div><div class="stat-label">Engagement rate</div></div>
  </div>

  <div class="channel-strip">
    <div class="channel"><span>Phone clicks</span><strong>{{ number_format($stats['calls']) }}</strong></div>
    <div class="channel"><span>WhatsApp clicks</span><strong>{{ number_format($stats['whatsapp']) }}</strong></div>
    <div class="channel"><span>Email clicks</span><strong>{{ number_format($stats['emails']) }}</strong></div>
    <div class="channel"><span>Shares</span><strong>{{ number_format($stats['shares']) }}</strong></div>
    <div class="channel"><span>Total leads</span><strong>{{ number_format($stats['enquiries']) }}</strong></div>
  </div>

  <div class="overview-grid">
    <section class="panel">
      <div class="panel-head"><h2>Views and contact activity</h2><span>Daily trend</span></div>
      @if($trend->isNotEmpty())
        <div class="trend-chart">
          @foreach($trend as $point)
            <div class="trend-day" title="{{ $point->day }}: {{ $point->views }} views, {{ $point->clicks }} clicks">
              <div class="trend-bars"><span class="bar" style="height:{{ max(2, ((int)$point->views / $maxTrend) * 100) }}%"></span><span class="bar clicks" style="height:{{ max(2, ((int)$point->clicks / $maxTrend) * 100) }}%"></span></div>
              <span class="trend-label">{{ \Carbon\Carbon::parse($point->day)->format('d M') }}</span>
            </div>
          @endforeach
        </div>
        <div class="chart-legend"><span><i class="legend-dot"></i>Views</span><span><i class="legend-dot clicks"></i>Contact clicks</span></div>
      @else
        <div class="empty">No tracked activity in this reporting period.</div>
      @endif
    </section>
    <section class="panel">
      <div class="panel-head"><h2>Traffic sources</h2><span>All interactions</span></div>
      <div class="source-list">
        @forelse($sources as $source)
          <div class="source-row"><div class="source-meta"><span>{{ ucfirst(strtolower($source->source)) }}</span><strong>{{ number_format($source->total) }}</strong></div><div class="source-track"><div class="source-fill" style="width:{{ ($source->total / $sourceTotal) * 100 }}%"></div></div></div>
        @empty
          <div class="empty">Source data will appear after activity is tracked.</div>
        @endforelse
      </div>
    </section>
  </div>

  <section class="panel ranking-panel">
    <div class="panel-head"><div><h2>Business traction ranking</h2><span>Views + 3× contact clicks + 5× enquiries</span></div></div>
    <div class="ranking-tools">
      <span class="subtitle">Compare engagement across every registered business.</span>
      <form method="GET" class="search-form"><input type="hidden" name="range" value="{{ $range }}"><input name="search" value="{{ $search }}" placeholder="Search business name"><button type="submit"><i class="fa fa-search"></i> Search</button></form>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead><tr><th>#</th><th>Business</th><th>Views</th><th>Phone</th><th>WhatsApp</th><th>Email</th><th>Shares</th><th>Enquiries</th><th>Engagement</th><th>Score</th></tr></thead>
        <tbody>
          @forelse($rankings as $index => $business)
            @php($contacts = (int)$business->calls + (int)$business->whatsapp + (int)$business->emails + (int)$business->shares)
            <tr>
              <td>{{ $rankings->firstItem() + $index }}</td>
              <td><div class="business"><div class="business-logo">@if($business->cmpd_logo)<img src="{{ env('UPLOADS_URL').'company/'.$business->cmpd_logo }}" alt="">@else<i class="fa fa-building"></i>@endif</div><div><strong>{{ $business->cmpd_name ?: 'Unnamed business' }}</strong><small>Business #{{ $business->cmpd_cmp_id }}</small></div></div></td>
              <td>{{ number_format($business->views) }}</td><td>{{ number_format($business->calls) }}</td><td>{{ number_format($business->whatsapp) }}</td><td>{{ number_format($business->emails) }}</td><td>{{ number_format($business->shares) }}</td><td>{{ number_format($business->enquiries) }}</td>
              <td>{{ $business->views > 0 ? round(($contacts + $business->enquiries) / $business->views * 100, 1) : 0 }}%</td><td><span class="score">{{ number_format($business->traction_score) }}</span></td>
            </tr>
          @empty
            <tr><td colspan="10" class="empty">No businesses match the selected filters.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($rankings->hasPages())<div class="pagination-wrap">{{ $rankings->links() }}</div>@endif
  </section>
</div>
