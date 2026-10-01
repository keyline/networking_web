@php
    use App\Helpers\Helper;
    $selectedType = $filters['type'] ?? '';
    $selectedStatus = $filters['status'] ?? '';
    $search = $filters['search'] ?? '';
@endphp

<style>
.ru-page{--ink:#17233c;--muted:#71809a;--line:#e3e9f2;--blue:#397cf6}.ru-head{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:22px}.ru-head h1{font-size:28px;color:var(--ink);margin:0 0 5px}.ru-sub{color:var(--muted);margin:0}.ru-stats{display:grid;grid-template-columns:repeat(3,minmax(130px,1fr));gap:12px;margin-bottom:18px}.ru-stat{background:#fff;border:1px solid var(--line);border-radius:14px;padding:16px 18px}.ru-stat span{display:block;color:var(--muted);font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}.ru-stat strong{display:block;color:var(--ink);font-size:25px;margin-top:4px}.ru-card{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 8px 24px rgba(33,55,90,.05);overflow:hidden}.ru-filter{display:grid;grid-template-columns:minmax(240px,1fr) 190px 170px auto;gap:10px;padding:18px;border-bottom:1px solid var(--line)}.ru-control{height:44px;border:1px solid #d8e0ec;border-radius:9px;padding:0 13px;color:var(--ink);background:#fff;width:100%}.ru-btn{height:44px;border:0;border-radius:9px;padding:0 19px;background:var(--blue);color:#fff;font-weight:700}.ru-clear{display:inline-flex;align-items:center;justify-content:center;height:44px;padding:0 12px;color:var(--muted)}.ru-table{width:100%;border-collapse:collapse}.ru-table th{padding:13px 16px;background:#f7f9fc;color:#738099;text-transform:uppercase;font-size:11px;letter-spacing:.05em;text-align:left}.ru-table td{padding:15px 16px;border-top:1px solid var(--line);vertical-align:middle;color:#34415a}.ru-person{display:flex;align-items:center;gap:11px}.ru-avatar{width:38px;height:38px;border-radius:11px;background:#eaf1ff;color:#397cf6;display:grid;place-items:center;font-weight:800}.ru-name{font-weight:750;color:var(--ink)}.ru-meta{font-size:12px;color:var(--muted);margin-top:2px}.ru-pill{display:inline-flex;border-radius:999px;padding:5px 9px;font-size:11px;font-weight:800;letter-spacing:.04em;background:#edf3ff;color:#2769df}.ru-pill.visitor{background:#f0f1f5;color:#667085}.ru-pill.active{background:#e9f8f1;color:#13845b}.ru-pill.pending{background:#fff5df;color:#a86700}.ru-pill.inactive{background:#fff0f0;color:#c43b3b}.ru-business{font-size:13px}.ru-business+ .ru-business{margin-top:4px}.ru-actions{white-space:nowrap}.ru-icon{display:inline-grid;place-items:center;width:34px;height:34px;border:1px solid #dce4ef;border-radius:8px;color:#397cf6;margin-right:4px}.ru-empty{text-align:center;padding:55px 20px!important;color:var(--muted)!important}.ru-foot{padding:14px 18px;border-top:1px solid var(--line)}@media(max-width:900px){.ru-filter{grid-template-columns:1fr 1fr}.ru-filter .search{grid-column:1/-1}.ru-table-wrap{overflow:auto}.ru-table{min-width:850px}}@media(max-width:560px){.ru-head{display:block}.ru-stats{grid-template-columns:1fr}.ru-filter{grid-template-columns:1fr}.ru-filter .search{grid-column:auto}}
.ru-icon{background:#fff}.ru-icon.danger{color:#d63745;border-color:#f1cbd0}.ru-delete{display:inline}.ru-alert{padding:13px 16px;border-radius:10px;margin-bottom:16px}.ru-alert.success{background:#eaf8f1;color:#14734f;border:1px solid #c8ead9}.ru-alert.danger{background:#fff0f1;color:#a82d38;border:1px solid #f2c7cb}
</style>

<div class="ru-page">
    @if(session('success_message'))<div class="ru-alert success">{{session('success_message')}}</div>@endif
    @if(session('error_message'))<div class="ru-alert danger">{{session('error_message')}}</div>@endif
    <div class="ru-head">
        <div><h1>Registered Users</h1><p class="ru-sub">View and manage every business owner and visitor account.</p></div>
        <a class="ru-clear" href="{{ route('admin.clients.registered-users') }}"><i class="fa fa-refresh me-2"></i>Refresh</a>
    </div>

    <div class="ru-stats">
        <div class="ru-stat"><span>All registered users</span><strong>{{ number_format($counts['all']) }}</strong></div>
        <div class="ru-stat"><span>Business owners</span><strong>{{ number_format($counts['owners']) }}</strong></div>
        <div class="ru-stat"><span>Visitors</span><strong>{{ number_format($counts['visitors']) }}</strong></div>
    </div>

    <section class="ru-card">
        <form class="ru-filter" method="get" action="{{ route('admin.clients.registered-users') }}">
            <input class="ru-control search" name="search" value="{{ $search }}" placeholder="Search name, email, phone or business">
            <select class="ru-control" name="type">
                <option value="">All user types</option>
                <option value="owner" @selected($selectedType === 'owner')>Business owners</option>
                <option value="visitor" @selected($selectedType === 'visitor')>Visitors</option>
            </select>
            <select class="ru-control" name="status">
                <option value="">All statuses</option>
                <option value="active" @selected($selectedStatus === 'active')>Active</option>
                <option value="pending" @selected($selectedStatus === 'pending')>Pending approval</option>
                <option value="inactive" @selected($selectedStatus === 'inactive')>Inactive</option>
            </select>
            <div><button class="ru-btn" type="submit"><i class="fa fa-search me-1"></i> Filter</button>@if($selectedType || $selectedStatus || $search)<a class="ru-clear" href="{{ route('admin.clients.registered-users') }}">Clear</a>@endif</div>
        </form>

        <div class="ru-table-wrap">
            <table class="ru-table">
                <thead><tr><th>User</th><th>Contact</th><th>Type</th><th>Business</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                    @php
                        $name = trim(($row->userDetail?->ud_first_name ?? '').' '.($row->userDetail?->ud_last_name ?? '')) ?: ($row->um_user_name ?: 'Unnamed user');
                        $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('');
                        $isOwner = (int) $row->um_utm_id === 2;
                        $status = match((int) $row->um_status) { 2 => 'active', 1 => 'pending', default => 'inactive' };
                    @endphp
                    <tr>
                        <td><div class="ru-person"><span class="ru-avatar">{{ $initials ?: 'U' }}</span><div><div class="ru-name">{{ $name }}</div><div class="ru-meta">User #{{ $row->um_id }}</div></div></div></td>
                        <td><div>{{ $row->um_email_id ?: '—' }}</div><div class="ru-meta">{{ $row->um_mobile_no ?: 'No mobile number' }}</div></td>
                        <td><span class="ru-pill {{ $isOwner ? '' : 'visitor' }}">{{ $isOwner ? 'Business owner' : 'Visitor' }}</span></td>
                        <td>
                            @forelse($row->companiesMap as $map)
                                @if($map->companie)<div class="ru-business">{{ $map->companie->cmpd_name }}</div>@endif
                            @empty<span class="ru-meta">No business</span>@endforelse
                        </td>
                        <td><span class="ru-pill {{ $status }}">{{ $status === 'pending' ? 'Pending approval' : ucfirst($status) }}</span></td>
                        <td>{{ $row->um_created_at ? $row->um_created_at->format('d M Y') : '—' }}</td>
                        <td class="ru-actions">
                            <a class="ru-icon" href="{{ url('admin/clients/'.strtolower($row->userType?->utm_name ?: ($isOwner ? 'seller' : 'guest')).'/view_details/'.Helper::encoded($row->um_id)) }}" target="_blank" title="View user"><i class="fa fa-eye"></i></a>
                            <form class="ru-delete" method="post" action="{{route('admin.clients.registered-users.destroy',$row)}}" onsubmit="return confirm('PERMANENTLY DELETE this user?\n\nThis removes the user, linked businesses, enquiries, memberships, portfolio content and uploaded images. This cannot be undone.');">
                                @csrf @method('delete')
                                <button class="ru-icon danger" type="submit" title="Permanently delete user and all data" aria-label="Permanently delete {{$name}}"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="ru-empty" colspan="7"><i class="fa fa-users fa-2x mb-3 d-block"></i>No registered users match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())<div class="ru-foot">{{ $rows->links() }}</div>@endif
    </section>
</div>
